<?php

namespace Preferans\Oauth\Entities;

/**
 * Preferans\Oauth\Entities\AuthCodeEntityInterface
 *
 * @package Preferans\Oauth\Entities
 */
interface AuthCodeEntityInterface extends TokenInterface
{
    /**
     * @return string
     */
    public function getRedirectUri();

    /**
     * @param string $uri
     */
    public function setRedirectUri($uri);
}
