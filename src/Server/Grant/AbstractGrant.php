<?php

namespace Preferans\Oauth\Server\Grant;

use DateTime;
use DateInterval;
use LogicException;
use Phalcon\Di\Injectable;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Server\RequestEvent;
use Phalcon\Http\Response\CookiesInterface;
use Preferans\Oauth\Traits\CryptAwareTrait;
use Preferans\Oauth\Traits\EventsAwareTrait;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Entities\AuthCodeEntityInterface;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use Preferans\Oauth\Repositories\UserRepositoryInterface;
use Preferans\Oauth\Repositories\ScopeRepositoryInterface;
use Preferans\Oauth\Repositories\ClientRepositoryInterface;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Repositories\AuthCodeRepositoryInterface;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;
use Preferans\Oauth\Repositories\AccessTokenRepositoryInterface;
use Preferans\Oauth\Exceptions\UniqueTokenIdentifierConstraintViolationException;

/**
 * Preferans\Oauth\Server\Grant\AbstractGrant
 *
 * @property CookiesInterface $cookies
 * @package Preferans\Oauth\Server\Grant
 */
abstract class AbstractGrant extends Injectable implements GrantTypeInterface
{
    use EventsAwareTrait, CryptAwareTrait;

    const SCOPE_DELIMITER_STRING = ' ';

    const MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS = 10;

    /**
     * @var ClientRepositoryInterface
     */
    protected $clientRepository;

    /**
     * @var AccessTokenRepositoryInterface
     */
    protected $accessTokenRepository;

    /**
     * @var ScopeRepositoryInterface
     */
    protected $scopeRepository;

    /**
     * The default scope for the current Grant Type.
     *
     * @var string|null
     */
    protected $defaultScope;

    /**
     * @var AuthCodeRepositoryInterface
     */
    protected $authCodeRepository;

    /**
     * @var RefreshTokenRepositoryInterface
     */
    protected $refreshTokenRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var DateInterval
     */
    protected $refreshTokenTTL;

    /**
     * @var CryptKey
     */
    protected $privateKey;

    /**
     * {@inheritdoc}
     *
     * @param ClientRepositoryInterface $clientRepository
     */
    public function setClientRepository(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     */
    public function setAccessTokenRepository(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param ScopeRepositoryInterface $scopeRepository
     */
    public function setScopeRepository(ScopeRepositoryInterface $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    /**
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function setRefreshTokenRepository(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * @param AuthCodeRepositoryInterface $authCodeRepository
     */
    public function setAuthCodeRepository(AuthCodeRepositoryInterface $authCodeRepository)
    {
        $this->authCodeRepository = $authCodeRepository;
    }

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function setUserRepository(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param DateInterval $refreshTokenTTL
     */
    public function setRefreshTokenTTL(DateInterval $refreshTokenTTL)
    {
        $this->refreshTokenTTL = $refreshTokenTTL;
    }

    /**
     * {@inheritdoc}
     *
     * @param CryptKey $key
     */
    public function setPrivateKey(CryptKey $key)
    {
        $this->privateKey = $key;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $defaultScope
     * @return void
     */
    public function setDefaultScope(string $defaultScope)
    {
        $this->defaultScope = $defaultScope;
    }

    /**
     * Validate scopes in the request.
     *
     * @param string      $scopes
     * @param string|null $redirectUri
     *
     * @throws OAuthServerException
     *
     * @return ScopeEntityInterface[]
     */
    public function validateScopes($scopes, $redirectUri = null)
    {
        $scopesList = array_filter(
            explode(self::SCOPE_DELIMITER_STRING, trim($scopes)),
            function ($scope) {
                return !empty($scope);
            }
        );

        $scopes = [];
        foreach ($scopesList as $scopeItem) {
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);

            if ($scope instanceof ScopeEntityInterface === false) {
                throw OAuthServerException::invalidScope($scopeItem, $redirectUri);
            }

            $scopes[] = $scope;
        }

        if (empty($scopes)) {
            throw OAuthServerException::invalidScope('', $redirectUri);
        }

        return $scopes;
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
        return $request->get('grant_type') === $this->getIdentifier();
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
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @throws LogicException
     */
    public function validateAuthorizationRequest(RequestInterface $request)
    {
        throw new LogicException('This grant cannot validate an authorization request');
    }

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationRequest $authorizationRequest
     */
    public function completeAuthorizationRequest(AuthorizationRequest $authorizationRequest)
    {
        throw new LogicException('This grant cannot complete an authorization request');
    }

    /**
     * Validate the client.
     *
     * @param RequestInterface $request
     *
     * @return ClientEntityInterface
     * @throws OAuthServerException
     */
    protected function validateClient(RequestInterface $request)
    {
        list($basicAuthUser, $basicAuthPassword) = $this->getBasicAuthCredentials($request);

        $clientId = $this->getRequestParameter('client_id', $request, $basicAuthUser);
        if ($clientId === null) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        // If the client is confidential require the client secret
        $clientSecret = $this->getRequestParameter('client_secret', $request, $basicAuthPassword);
        $client = $this->clientRepository->getClientEntity($clientId, $this->getIdentifier(), $clientSecret);

        if (!$client instanceof ClientEntityInterface) {
            $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);

            throw OAuthServerException::invalidClient();
        }

        // If a redirect URI is provided ensure it matches what is pre-registered
        $redirectUri = $this->getRequestParameter('redirect_uri', $request);

        if ($redirectUri !== null) {
            if (is_string($client->getRedirectUri()) && (strcmp($client->getRedirectUri(), $redirectUri) !== 0)) {
                $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
                throw OAuthServerException::invalidClient();
            } elseif (is_array($client->getRedirectUri())
                && in_array($redirectUri, $client->getRedirectUri()) === false
            ) {
                $this->getEventsManager()->fire(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request);
                throw OAuthServerException::invalidClient();
            }
        }

        return $client;
    }

    /**
     * Retrieve request parameter.
     *
     * @param string           $parameter
     * @param RequestInterface $request
     * @param mixed            $default
     *
     * @return null|string
     */
    protected function getRequestParameter(string $parameter, RequestInterface $request, $default = null)
    {
        return $request->get($parameter, null, $default);
    }

    /**
     * Retrieve HTTP Basic Auth credentials with the Authorization header
     * of a request. First index of the returned array is the username,
     * second is the password (so list() will work). If the header does
     * not exist, or is otherwise an invalid HTTP Basic header, return
     * [null, null].
     *
     * @param RequestInterface $request
     *
     * @return string[]|null[]
     */
    protected function getBasicAuthCredentials(RequestInterface $request)
    {
        if (!$header = $request->getHeader('Authorization')) {
            return [null, null];
        }

        if (strpos($header, 'Basic ') !== 0) {
            return [null, null];
        }

        if (!($decoded = base64_decode(substr($header, 6)))) {
            return [null, null];
        }

        if (strpos($decoded, ':') === false) {
            return [null, null]; // HTTP Basic header without colon isn't valid
        }

        return explode(':', $decoded, 2);
    }

    /**
     * Retrieve query string parameter.
     *
     * @param string           $parameter
     * @param RequestInterface $request
     * @param mixed            $default
     *
     * @return null|string
     */
    protected function getQueryStringParameter($parameter, RequestInterface $request, $default = null)
    {
        return $request->getQuery($parameter, null, $default);
    }

    /**
     * Retrieve cookie parameter.
     *
     * @param string $parameter
     * @param mixed  $default
     *
     * @return null|string
     */
    protected function getCookieParameter($parameter, $default = null)
    {
        if (!$this->getDI()->has('cookies')) {
            return $default;
        }

        return $this->cookies->has($parameter) ? $this->cookies->get($parameter) : $default;
    }

    /**
     * Retrieve server parameter.
     *
     * @param string           $parameter
     * @param RequestInterface $request
     * @param mixed            $default
     *
     * @return null|string
     */
    protected function getServerParameter($parameter, RequestInterface $request, $default = null)
    {
        $result = $request->getServer($parameter);

        return $result !== null ? $result : $default;
    }

    /**
     * Issue an access token.
     *
     * @param DateInterval           $accessTokenTTL
     * @param ClientEntityInterface  $client
     * @param string|null            $userIdentifier
     * @param ScopeEntityInterface[] $scopes
     *
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     *
     * @return AccessTokenEntityInterface
     */
    protected function issueAccessToken(
        DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ) {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

        $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $userIdentifier);
        $accessToken->setClient($client);
        $accessToken->setExpiryDateTime((new DateTime())->add($accessTokenTTL));

        while ($maxGenerationAttempts-- > 0) {
            $accessToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $this->accessTokenRepository->persistNewAccessToken($accessToken);

                break;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }

        return $accessToken;
    }

    /**
     * Issue an auth code.
     *
     * @param DateInterval           $authCodeTTL
     * @param ClientEntityInterface  $client
     * @param string                 $userIdentifier
     * @param string|null            $redirectUri
     * @param ScopeEntityInterface[] $scopes
     *
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     *
     * @return AuthCodeEntityInterface
     */
    protected function issueAuthCode(
        DateInterval $authCodeTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        $redirectUri,
        array $scopes = []
    ) {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

        $authCode = $this->authCodeRepository->getNewAuthCode();
        $authCode->setExpiryDateTime((new DateTime())->add($authCodeTTL));
        $authCode->setClient($client);
        $authCode->setUserIdentifier($userIdentifier);

        if (!empty($redirectUri)) {
            $authCode->setRedirectUri($redirectUri);
        }


        foreach ($scopes as $scope) {
            $authCode->addScope($scope);
        }

        while ($maxGenerationAttempts-- > 0) {
            $authCode->setIdentifier($this->generateUniqueIdentifier());
            try {
                $this->authCodeRepository->persistNewAuthCode($authCode);

                break;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }

        return $authCode;
    }

    /**
     * @param AccessTokenEntityInterface $accessToken
     *
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     *
     * @return RefreshTokenEntityInterface
     */
    protected function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

        $refreshToken = $this->refreshTokenRepository->getNewRefreshToken();
        $refreshToken->setExpiryDateTime((new DateTime())->add($this->refreshTokenTTL));
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $this->refreshTokenRepository->persistNewRefreshToken($refreshToken);

                break;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }

        return $refreshToken;
    }

    /**
     * Generate a new unique identifier.
     *
     * @param int $length
     *
     * @throws OAuthServerException
     *
     * @return string
     */
    protected function generateUniqueIdentifier($length = 40)
    {
        try {
            return bin2hex(random_bytes($length));
            // @codeCoverageIgnoreStart
        } catch (\TypeError $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Error $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
        // @codeCoverageIgnoreEnd
    }
}
