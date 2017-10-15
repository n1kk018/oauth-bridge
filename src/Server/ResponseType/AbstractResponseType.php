<?php

namespace Preferans\Oauth\Server\ResponseType;

use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Traits\CryptAwareTrait;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;

/**
 * Preferans\Oauth\Server\ResponseType\AbstractResponseType
 *
 * @package Preferans\Oauth\Server\ResponseType
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{
    use CryptAwareTrait;

    /**
     * @var AccessTokenEntityInterface|null
     */
    protected $accessToken;

    /**
     * @var RefreshTokenEntityInterface|null
     */
    protected $refreshToken;

    /**
     * @var CryptKey|null
     */
    protected $privateKey;

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

    /**
     * Set the private key
     *
     * @param CryptKey $key
     */
    public function setPrivateKey(CryptKey $key)
    {
        $this->privateKey = $key;
    }
}
