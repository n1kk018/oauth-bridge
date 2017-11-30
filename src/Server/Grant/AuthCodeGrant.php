<?php

namespace Preferans\Oauth\Server\Grant;

use DateTime;
use DateInterval;
use LogicException;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Server\RequestEvent;
use Preferans\Oauth\Entities\UserEntityInterface;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Traits\RequestScopesAwareTrait;
use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Server\ResponseType\RedirectResponse;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Repositories\AuthCodeRepositoryInterface;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;
use Preferans\Oauth\Server\CodeChallengeVerifiers\CodeChallengeVerifierInterface;

/**
 * Preferans\Oauth\Server\Grant\AuthCodeGrant
 *
 * @package Preferans\Oauth\Server\Grant
 */
class AuthCodeGrant extends AbstractAuthorizeGrant
{
    use RequestScopesAwareTrait;

    /**
     * @var DateInterval
     */
    private $authCodeTTL;

    /**
     * @var CodeChallengeVerifierInterface[]
     */
    private $codeChallengeVerifiers = [];

    /**
     * @param AuthCodeRepositoryInterface     $authCodeRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param DateInterval                   $authCodeTTL
     */
    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        DateInterval $authCodeTTL
    ) {
        $this->setAuthCodeRepository($authCodeRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->authCodeTTL = $authCodeTTL;
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    /**
     * Enable a code challenge verifier on the grant.
     *
     * @param CodeChallengeVerifierInterface $codeChallengeVerifier
     */
    public function enableCodeChallengeVerifier(CodeChallengeVerifierInterface $codeChallengeVerifier)
    {
        $this->codeChallengeVerifiers[$codeChallengeVerifier->getMethod()] = $codeChallengeVerifier;
    }

    /**
     * Respond to an access token request.
     *
     * @param RequestInterface $request
     * @param ResponseTypeInterface  $responseType
     * @param DateInterval          $accessTokenTTL
     *
     * @throws OAuthServerException
     *
     * @return ResponseTypeInterface
     */
    public function respondToAccessTokenRequest(
        RequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $encryptedAuthCode = $this->getRequestParameter('code', $request, null);

        if ($encryptedAuthCode === null) {
            throw OAuthServerException::invalidRequest('code');
        }

        // Validate the authorization code
        try {
            $rawCodePayload = $this->getCrypt()->decryptBase64($encryptedAuthCode, $this->encryptionKey, true);

            $authCodePayload = json_decode($rawCodePayload);
            $this->validateAuthCodePayload($authCodePayload);

            if (time() > $authCodePayload->expire_time) {
                throw OAuthServerException::invalidRequest('code', 'Authorization code has expired');
            }

            if ($this->authCodeRepository->isAuthCodeRevoked($authCodePayload->auth_code_id) === true) {
                throw OAuthServerException::invalidRequest('code', 'Authorization code has been revoked');
            }

            if ($authCodePayload->client_id !== $client->getIdentifier()) {
                throw OAuthServerException::invalidRequest(
                    'code',
                    'Authorization code was not issued to this client'
                );
            }

            // The redirect URI is required in this request
            $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);
            if (!empty($authCodePayload->redirect_uri) && $redirectUri === null) {
                throw OAuthServerException::invalidRequest('redirect_uri');
            }

            if ($authCodePayload->redirect_uri !== $redirectUri) {
                throw OAuthServerException::invalidRequest('redirect_uri', 'Invalid redirect URI');
            }

            $scopes = [];
            foreach ($authCodePayload->scopes as $scopeId) {
                $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeId);

                if (!$scope instanceof ScopeEntityInterface) {
                    // @codeCoverageIgnoreStart
                    throw OAuthServerException::invalidScope($scopeId);
                    // @codeCoverageIgnoreEnd
                }

                $scopes[] = $scope;
            }

            // Finalize the requested scopes
            $scopes = $this->scopeRepository->finalizeScopes(
                $scopes,
                $this->getIdentifier(),
                $client,
                $authCodePayload->user_id
            );
        } catch (LogicException $e) {
            throw OAuthServerException::invalidRequest('code', 'Cannot decrypt the authorization code');
        }

        // Verify code challenge
        if (!empty($this->codeChallengeVerifiers)) {
            $codeVerifier = $this->getRequestParameter('code_verifier', $request, null);
            if ($codeVerifier === null) {
                throw OAuthServerException::invalidRequest('code_verifier');
            }

            if (isset($this->codeChallengeVerifiers[$authCodePayload->code_challenge_method])) {
                $verifier = $this->codeChallengeVerifiers[$authCodePayload->code_challenge_method];

                if (!$verifier->verifyCodeChallenge($codeVerifier, $authCodePayload->code_challenge)) {
                    throw OAuthServerException::invalidGrant('Failed to verify `code_verifier`.');
                }
            } else {
                // @codeCoverageIgnoreStart
                throw OAuthServerException::serverError(
                    sprintf('Unsupported code challenge method `%s`', $authCodePayload->code_challenge_method)
                );
                // @codeCoverageIgnoreEnd
            }
        }

        // Issue and persist access + refresh tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $authCodePayload->user_id, $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Inject tokens into response type
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        // Revoke used auth code
        $this->authCodeRepository->revokeAuthCode($authCodePayload->auth_code_id);

        return $responseType;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'authorization_code';
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function canRespondToAuthorizationRequest(RequestInterface $request)
    {
        return $request->hasQuery('client_id') &&
            $this->getQueryStringParameter('response_type', $request) === 'code';
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @return AuthorizationRequest
     * @throws OAuthServerException
     */
    public function validateAuthorizationRequest(RequestInterface $request)
    {
        $clientId = $this->getQueryStringParameter('client_id', $request);

        if ($clientId === null) {
            $clientId = $this->getServerParameter('PHP_AUTH_USER', $request);
        }

        if ($clientId === null) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        $client = $this->clientRepository->getClientEntity($clientId, $this->getIdentifier());

        if (!$client instanceof ClientEntityInterface) {
            $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
            throw OAuthServerException::invalidClient();
        }

        $redirectUri = $this->getQueryStringParameter('redirect_uri', $request);
        $clientRedirect = $client->getRedirectUri();

        if ($redirectUri !== null) {
            if (is_string($clientRedirect) && (strcmp($clientRedirect, $redirectUri) !== 0)) {
                $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
                throw OAuthServerException::invalidClient();
            } elseif (is_array($clientRedirect) && in_array($redirectUri, $clientRedirect) === false) {
                $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
                throw OAuthServerException::invalidClient();
            }
        } elseif (is_array($clientRedirect) && count($clientRedirect) !== 1 || empty($clientRedirect)) {
            $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
            throw OAuthServerException::invalidClient();
        }

        $scopes = $this->getScopesFromRequest($request, true, $clientRedirect, $this->defaultScope);

        $stateParameter = $this->getQueryStringParameter('state', $request);

        $authorizationRequest = new AuthorizationRequest();
        $authorizationRequest->setGrantTypeId($this->getIdentifier());
        $authorizationRequest->setClient($client);

        $authorizationRequest->setScopes($scopes);

        if ($redirectUri !== null) {
            $authorizationRequest->setRedirectUri($redirectUri);
        }

        if ($stateParameter !== null) {
            $authorizationRequest->setState($stateParameter);
        }

        if (!empty($this->codeChallengeVerifiers)) {
            $codeChallenge = $this->getQueryStringParameter('code_challenge', $request);
            if ($codeChallenge === null) {
                throw OAuthServerException::invalidRequest('code_challenge');
            }

            if (preg_match('/^[A-Za-z0-9-._~]{43,128}$/', $codeChallenge) !== 1) {
                throw OAuthServerException::invalidRequest(
                    'code_challenge',
                    'The code_challenge must be between 43 and 128 characters'
                );
            }

            $codeChallengeMethod = $this->getQueryStringParameter('code_challenge_method', $request, 'plain');
            if (!array_key_exists($codeChallengeMethod, $this->codeChallengeVerifiers)) {
                $validVerifies = array_map(function ($method) {
                    return '`' . $method . '`';
                }, array_keys($this->codeChallengeVerifiers));


                throw OAuthServerException::invalidRequest(
                    'code_challenge_method',
                    'Code challenge method must be one of ' . implode(', ', $validVerifies)
                );
            }

            $authorizationRequest->setCodeChallenge($codeChallenge);

            if (!empty($codeChallengeMethod)) {
                $authorizationRequest->setCodeChallengeMethod($codeChallengeMethod);
            }
        }

        return $authorizationRequest;
    }

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationRequest $authorizationRequest
     *
     * @return RedirectResponse
     * @throws OAuthServerException
     */
    public function completeAuthorizationRequest(AuthorizationRequest $authorizationRequest)
    {
        if (!$authorizationRequest->getUser() instanceof UserEntityInterface) {
            throw new LogicException('An instance of UserEntityInterface should be set on the AuthorizationRequest');
        }

        $finalRedirectUri = $authorizationRequest->getFinalRedirectUri();

        // The user approved the client, redirect them back with an auth code
        if ($authorizationRequest->isAuthorizationApproved() === true) {
            $authCode = $this->issueAuthCode(
                $this->authCodeTTL,
                $authorizationRequest->getClient(),
                $authorizationRequest->getUser()->getIdentifier(),
                $authorizationRequest->getRedirectUri(),
                $authorizationRequest->getScopes()
            );

            $payload = [
                'client_id'             => $authCode->getClient()->getIdentifier(),
                'redirect_uri'          => $authCode->getRedirectUri(),
                'auth_code_id'          => $authCode->getIdentifier(),
                'scopes'                => $authCode->getScopes(),
                'user_id'               => $authCode->getUserIdentifier(),
                'expire_time'           => (new DateTime())->add($this->authCodeTTL)->format('U'),
                'code_challenge'        => $authorizationRequest->getCodeChallenge(),
                'code_challenge_method' => $authorizationRequest->getCodeChallengeMethod(),
            ];

            $response = new RedirectResponse();
            $response->setRedirectUri(
                $this->makeRedirectUri(
                    $finalRedirectUri,
                    [
                        'code'  => $this->getCrypt()->encryptBase64(json_encode($payload), $this->encryptionKey, true),
                        'state' => $authorizationRequest->getState(),
                    ]
                )
            );

            return $response;
        }

        // The user denied the client, redirect them back with an error
        throw OAuthServerException::accessDenied(
            'The user denied the request',
            $this->makeRedirectUri(
                $finalRedirectUri,
                [
                    'state' => $authorizationRequest->getState(),
                ]
            )
        );
    }

    /**
     * Validate auth code payload.
     *
     * @param $authCodePayload
     * @throws OAuthServerException
     */
    private function validateAuthCodePayload($authCodePayload)
    {
        $validCodePayload =
            isset($authCodePayload->expire_time) &
            isset($authCodePayload->auth_code_id) &
            isset($authCodePayload->client_id) &
            isset($authCodePayload->redirect_uri) &
            isset($authCodePayload->scopes) && is_array($authCodePayload->scopes) &
            isset($authCodePayload->user_id);

        if ($validCodePayload === false) {
            throw OAuthServerException::invalidRequest('code');
        }
    }
}
