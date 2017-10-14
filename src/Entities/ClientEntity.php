<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\NamedEntityTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use Preferans\Oauth\Entities\Traits\RedirectableEntityTrait;

/**
 * Preferans\Oauth\Entities\ClientEntity
 *
 * @package Preferans\Oauth\Entities
 */
class ClientEntity implements ClientEntityInterface
{
    use IdentifiedEntityTrait, NamedEntityTrait, RedirectableEntityTrait;

    protected $secret;

    /**
     * Set the hashed client's secret.
     *
     * @param string $secret
     * @return void
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Get the hashed client's secret.
     *
     * @return string
     **/
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Returns true if the client is capable of keeping it's secrets secret.
     *
     * @return bool
     */
    public function canKeepASecret()
    {
        return $this->secret !== null;
    }
}
