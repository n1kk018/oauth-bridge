<?php

namespace Preferans\Oauth\Tests\Stubs;

use Preferans\Oauth\Entities\Traits\RefreshTokenTrait;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, IdentifiedEntityTrait;
}
