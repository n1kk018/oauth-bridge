<?php

namespace Preferans\Oauth\Server\Grant;

use DateTime;
use DateInterval;
use LogicException;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Server\RequestEvent;
use Preferans\Oauth\Entities\UserEntityInterface;
use Preferans\Oauth\Traits\RequestScopesAwareTrait;
use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Server\ResponseType\RedirectResponse;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;

/**
 * Preferans\Oauth\Server\Grant\ImplicitGrant
 *
 * @package Preferans\Oauth\Server\Grant
 */
class ImplicitGrant extends AbstractAuthorizeGrant
{
    use RequestScopesAwareTrait;

    /**
     * @var DateInterval
     */
    private $accessTokenTTL;

    /**
     * @var string
     */
    private $queryDelimiter;

    /**
     * {@inheritdoc}
     *
     * @param DateInterval $accessTokenTTL
     */
    public function __construct(DateInterval $accessTokenTTL, string $queryDelimiter = '#')
    {
        $this->accessTokenTTL = $accessTokenTTL;
        $this->queryDelimiter = $queryDelimiter;
    }

    /**
     * {@inheritdoc}
     *
     * @param DateInterval $refreshTokenTTL
     *
     * @throw LogicException
     */
    public function setRefreshTokenTTL(DateInterval $refreshTokenTTL)
    {
        throw new LogicException('The Implicit Grant does not return refresh tokens');
    }

    /**
     * {@inheritdoc}
     *
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     *
     * @throw LogicException
     */
    public function setRefreshTokenRepository(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        throw new LogicException('The Implicit Grant does not return refresh tokens');
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function canRespondToAccessTokenRequest(RequestInterface $request)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'implicit';
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface      $request
     * @param ResponseTypeInterface $responseType
     * @param DateInterval          $accessTokenTTL
     *
     * @return ResponseTypeInterface
     * @throws LogicException
     */
    public function respondToAccessTokenRequest(
        RequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        throw new LogicException('This grant does not used this method');
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
            $this->getQueryStringParameter('response_type', $request) === 'token';
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
        $clientId = $this->getQueryStringParameter(
            'client_id',
            $request,
            $this->getServerParameter('PHP_AUTH_USER', $request)
        );

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

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes(
            $scopes,
            $this->getIdentifier(),
            $client
        );

        $stateParameter = $this->getQueryStringParameter('state', $request);

        $authorizationRequest = new AuthorizationRequest();
        $authorizationRequest->setGrantTypeId($this->getIdentifier());
        $authorizationRequest->setClient($client);
        $authorizationRequest->setScopes($finalizedScopes);

        if ($redirectUri !== null) {
            $authorizationRequest->setRedirectUri($redirectUri);
        }

        if ($stateParameter !== null) {
            $authorizationRequest->setState($stateParameter);
        }

        return $authorizationRequest;
    }

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationRequest $authorizationRequest
     *
     * @return RedirectResponse
     * @throws LogicException
     * @throws OAuthServerException
     */
    public function completeAuthorizationRequest(AuthorizationRequest $authorizationRequest)
    {
        if (!$authorizationRequest->getUser() instanceof UserEntityInterface) {
            throw new LogicException('An instance of UserEntityInterface should be set on the AuthorizationRequest');
        }

        $finalRedirectUri = $authorizationRequest->getFinalRedirectUri();

        // The user approved the client, redirect them back with an access token
        if ($authorizationRequest->isAuthorizationApproved() === true) {
            $accessToken = $this->issueAccessToken(
                $this->accessTokenTTL,
                $authorizationRequest->getClient(),
                $authorizationRequest->getUser()->getIdentifier(),
                $authorizationRequest->getScopes()
            );

            $response = new RedirectResponse();
            $expiresIn = $accessToken->getExpiryDateTime()->getTimestamp() - (new DateTime())->getTimestamp();

            $response->setRedirectUri(
                $this->makeRedirectUri(
                    $finalRedirectUri,
                    [
                        'access_token' => (string)$accessToken->convertToJWT($this->privateKey),
                        'token_type'   => 'bearer',
                        'expires_in'   => $expiresIn,
                        'state'        => $authorizationRequest->getState(),
                    ],
                    $this->queryDelimiter
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
}
