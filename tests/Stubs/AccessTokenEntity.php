<?php

namespace Preferans\Oauth\Tests\Stubs;

use Preferans\Oauth\Tests\Helpers\IdentifyAwareTrait;
use Preferans\Oauth\Entities\Traits\AccessTokenTrait;
use Preferans\Oauth\Entities\Traits\TokenEntityTrait;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use AccessTokenTrait, IdentifiedEntityTrait, TokenEntityTrait, IdentifyAwareTrait;
}
