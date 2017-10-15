<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\RefreshTokenTrait;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

/**
 * Preferans\Entities\Oauth\RefreshTokenEntity
 *
 * @package Preferans\Oauth\Entities
 */
class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use IdentifiedEntityTrait, RefreshTokenTrait;
}
