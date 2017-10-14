<?php

namespace Preferans\Oauth\Server\ResponseType;

use League\OAuth2\Server\CryptTrait;
use Preferans\Oauth\Server\ResponseTypeInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * Preferans\Oauth\Server\ResponseType\AbstractResponseType
 *
 * @package Preferans\Oauth\Server\ResponseType
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{
    use CryptTrait;

    /**
     * @var AccessTokenEntityInterface
     */
    protected $accessToken;

    /**
     * @var RefreshTokenEntityInterface
     */
    protected $refreshToken;

    /**
     * {@inheritdoc}
     *
     * @param AccessTokenEntityInterface $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritdoc}
     *
     * @param RefreshTokenEntityInterface $refreshToken
     */
    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }
}
