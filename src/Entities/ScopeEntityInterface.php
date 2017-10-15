<?php

namespace Preferans\Oauth\Entities;

/**
 * Preferans\Oauth\Entities\ScopeEntityInterface
 *
 * @package Preferans\Oauth\Entities
 */
interface ScopeEntityInterface extends \JsonSerializable
{
    /**
     * Get the scope's identifier.
     *
     * @return string
     */
    public function getIdentifier();
}
