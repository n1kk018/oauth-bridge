<?php

namespace Preferans\Oauth\Entities\Traits;

use Preferans\Oauth\Entities\AccessTokenEntityInterface;

/**
 * Preferans\Oauth\Entities\Traits\RefreshTokenTrait
 *
 * @package Preferans\Oauth\Entities\Traits
 */
trait RefreshTokenTrait
{
    /**
     * @var AccessTokenEntityInterface
     */
    protected $accessToken;

    /**
     * @var \DateTime
     */
    protected $expiryDateTime;

    /**
     * {@inheritdoc}
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the token's expiry date time.
     *
     * @return \DateTime
     */
    public function getExpiryDateTime()
    {
        return $this->expiryDateTime;
    }

    /**
     * Set the date time when the token expires.
     *
     * @param \DateTime $dateTime
     */
    public function setExpiryDateTime(\DateTime $dateTime)
    {
        $this->expiryDateTime = $dateTime;
    }
}
