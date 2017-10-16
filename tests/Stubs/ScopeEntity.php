<?php

namespace Preferans\Oauth\Tests\Stubs;

use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

class ScopeEntity implements ScopeEntityInterface
{
    use IdentifiedEntityTrait;

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
