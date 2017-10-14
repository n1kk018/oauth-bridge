<?php

namespace Preferans\Oauth\Entities\Traits;

/**
 * Preferans\Oauth\Entities\Traits\NamedEntityTrait
 *
 * @package Preferans\Oauth\Entities\Traits
 */
trait NamedEntityTrait
{
    protected $name;

    /**
     * Gets the entity's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * Sets the entity's name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
}
