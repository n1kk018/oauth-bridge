<?php

namespace Preferans\Oauth\Tests\Stubs;

use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Tests\Helpers\IdentifyAwareTrait;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

class ScopeEntity implements ScopeEntityInterface
{
    use IdentifiedEntityTrait, IdentifyAwareTrait;

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
