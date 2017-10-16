<?php

namespace Preferans\Oauth\Entities;

use Lcobucci\JWT\Token;
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
     * @return Token
     */
    public function convertToJWT(CryptKey $privateKey);
}
