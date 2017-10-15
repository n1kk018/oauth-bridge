<?php

namespace Preferans\Oauth\Entities;

/**
 * Preferans\Oauth\Entities\UserEntityInterface
 *
 * @package Preferans\Oauth\Entities
 */
interface UserEntityInterface
{
    /**
     * Return the user's identifier.
     *
     * @return mixed
     */
    public function getIdentifier();
}
