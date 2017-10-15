<?php

namespace Preferans\Oauth\Server\ResponseType;

use Phalcon\Http\ResponseInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * Preferans\Oauth\Server\ResponseType\ResponseTypeInterface
 *
 * @package Preferans\Oauth\Server
 */
interface ResponseTypeInterface
{
    /**
     * @param AccessTokenEntityInterface $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken);

    /**
     * @param RefreshTokenEntityInterface $refreshToken
     */
    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken);

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function generateHttpResponse(ResponseInterface $response);

    /**
     * Set the encryption key
     *
     * @param string|null $key
     */
    public function setEncryptionKey($key = null);
}
