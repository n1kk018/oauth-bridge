<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Server\CryptKey;

/**
 * Preferans\Oauth\Entities\AccessTokenEntityInterface
 *
 * @package Preferans\Oauth\Entities
 */
interface AccessTokenEntityInterface extends TokenInterface
{
    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return string
     */
    public function convertToJWT(CryptKey $privateKey);
}
