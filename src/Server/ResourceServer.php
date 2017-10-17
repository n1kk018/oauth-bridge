<?php

namespace Preferans\Oauth\Server;

use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Repositories\AccessTokenRepositoryInterface;
use Preferans\Oauth\AuthorizationValidators\BearerTokenValidator;
use Preferans\Oauth\AuthorizationValidators\AuthorizationValidatorInterface;

/**
 * Preferans\Oauth\Server\ResourceServer
 *
 * @package Preferans\Oauth\Server
 */
class ResourceServer
{
    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var CryptKey
     */
    private $publicKey;

    /**
     * @var null|AuthorizationValidatorInterface
     */
    private $authorizationValidator;

    /**
     * New server instance.
     *
     * @param AccessTokenRepositoryInterface       $accessTokenRepository
     * @param CryptKey|string                      $publicKey
     * @param null|AuthorizationValidatorInterface $authorizationValidator
     */
    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $publicKey,
        AuthorizationValidatorInterface $authorizationValidator = null
    ) {
        $this->accessTokenRepository = $accessTokenRepository;

        if ($publicKey instanceof CryptKey === false) {
            $publicKey = new CryptKey($publicKey);
        }
        $this->publicKey = $publicKey;

        $this->authorizationValidator = $authorizationValidator;
    }

    /**
     * @return AuthorizationValidatorInterface
     */
    protected function getAuthorizationValidator()
    {
        if ($this->authorizationValidator instanceof AuthorizationValidatorInterface === false) {
            $this->authorizationValidator = new BearerTokenValidator($this->accessTokenRepository);
        }

        $this->authorizationValidator->setPublicKey($this->publicKey);

        return $this->authorizationValidator;
    }

    /**
     * Determine the access token validity.
     *
     * @param RequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return RequestInterface
     */
    public function validateAuthenticatedRequest(RequestInterface $request)
    {
        return $this->getAuthorizationValidator()->validateAuthorization($request);
    }
}
