<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\TokenEntityTrait;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use Preferans\Oauth\Entities\Traits\AccessTokenTrait;

/**
 * Preferans\Oauth\Entities\AccessTokenEntity
 *
 * @package Preferans\Oauth\Entities
 */
class AccessTokenEntity implements AccessTokenEntityInterface
{
    use AccessTokenTrait, TokenEntityTrait, IdentifiedEntityTrait;
}
