<?php

namespace Preferans\Oauth\Server;

use DateInterval;
use League\OAuth2\Server\CryptKey;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Events\EventsAwareInterface;
use Preferans\Oauth\Traits\EventsAwareTrait;
use Preferans\Oauth\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Preferans\Oauth\Server\ResponseType\BearerTokenResponse;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

/**
 * Preferans\Oauth\Server\AuthorizationServer
 *
 * @package Preferans\Oauth\Server
 */
class AuthorizationServer implements EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * @var GrantTypeInterface[]
     */
    protected $enabledGrantTypes = [];

    /**
     * @var DateInterval[]
     */
    protected $grantTypeAccessTokenTTL = [];

    /**
     * @var CryptKey
     */
    protected $privateKey;


    /**
     * @var null|ResponseTypeInterface
     */
    protected $responseType;

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
     * @var string
     */
    protected $encryptionKey;

    /**
     * New server instance.
     *
     * @param ClientRepositoryInterface      $clientRepository
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param ScopeRepositoryInterface       $scopeRepository
     * @param CryptKey|string                $privateKey
     * @param string                         $encryptionKey
     * @param ResponseTypeInterface|null     $responseType
     */
    public function __construct(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $privateKey,
        string $encryptionKey,
        ResponseTypeInterface $responseType = null
    ) {
        $this->clientRepository = $clientRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->scopeRepository = $scopeRepository;

        if (!$privateKey instanceof CryptKey) {
            $privateKey = new CryptKey($privateKey);
        }

        $this->privateKey = $privateKey;
        $this->encryptionKey = $encryptionKey;
        $this->responseType = $responseType;
    }

    /**
     * Enable a grant type on the server.
     *
     * @param GrantTypeInterface $grantType
     * @param null|DateInterval $accessTokenTTL
     */
    public function enableGrantType(GrantTypeInterface $grantType, DateInterval $accessTokenTTL = null)
    {
        $accessTokenTTL = $accessTokenTTL ?: new DateInterval('PT1H');

        $grantType->setAccessTokenRepository($this->accessTokenRepository);
        $grantType->setClientRepository($this->clientRepository);
        $grantType->setScopeRepository($this->scopeRepository);
        $grantType->setPrivateKey($this->privateKey);
        $grantType->setEventsManager($this->getEventsManager());
        $grantType->setEncryptionKey($this->encryptionKey);

        $this->enabledGrantTypes[$grantType->getIdentifier()] = $grantType;
        $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()] = $accessTokenTTL;
    }

    /**
     * Validate an authorization request
     *
     * @param RequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return AuthorizationRequest
     */
    public function validateAuthorizationRequest(RequestInterface $request)
    {
        foreach ($this->enabledGrantTypes as $grantType) {
            if ($grantType->canRespondToAuthorizationRequest($request)) {
                return $grantType->validateAuthorizationRequest($request);
            }
        }

        throw OAuthServerException::unsupportedGrantType();
    }

    /**
     * Complete an authorization request
     *
     * @param AuthorizationRequest $authRequest
     * @param ResponseInterface    $response
     *
     * @return ResponseInterface
     */
    public function completeAuthorizationRequest(AuthorizationRequest $authRequest, ResponseInterface $response)
    {
        return $this->enabledGrantTypes[$authRequest->getGrantTypeId()]
            ->completeAuthorizationRequest($authRequest)
            ->generateHttpResponse($response);
    }

    /**
     * Return an access token response.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @throws OAuthServerException
     *
     * @return ResponseInterface
     */
    public function respondToAccessTokenRequest(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->enabledGrantTypes as $grantType) {
            if ($grantType->canRespondToAccessTokenRequest($request)) {
                $tokenResponse = $grantType->respondToAccessTokenRequest(
                    $request,
                    $this->getResponseType(),
                    $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()]
                );

                if ($tokenResponse instanceof ResponseTypeInterface) {
                    return $tokenResponse->generateHttpResponse($response);
                }
            }
        }

        throw OAuthServerException::unsupportedGrantType();
    }

    /**
     * Get the token type that grants will return in the HTTP response.
     *
     * @return ResponseTypeInterface
     */
    protected function getResponseType()
    {
        if (!$this->responseType instanceof ResponseTypeInterface) {
            $this->responseType = new BearerTokenResponse();
        }

        $this->responseType->setPrivateKey($this->privateKey);
        $this->responseType->setEncryptionKey($this->encryptionKey);

        return $this->responseType;
    }
}
