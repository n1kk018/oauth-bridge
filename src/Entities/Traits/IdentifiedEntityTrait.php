<?php

namespace Preferans\Oauth\Entities\Traits;

/**
 * Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait
 *
 * @package Preferans\Oauth\Entities\Traits
 */
trait IdentifiedEntityTrait
{
    protected $id;

    /**
     * Gets the entity's identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * Sets the entity's identifier.
     *
     * @param mixed $identifier
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->id = $identifier;
    }
}
