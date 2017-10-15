<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

/**
 * Preferans\Oauth\Entities\ScopeEntity
 *
 * @package Preferans\Oauth\Entities
 */
class ScopeEntity implements ScopeEntityInterface
{
    use IdentifiedEntityTrait;

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
