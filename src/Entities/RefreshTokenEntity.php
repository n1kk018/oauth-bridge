<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * Preferans\Entities\Oauth\RefreshTokenEntity
 *
 * @package Preferans\Oauth\Entities
 */
class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use IdentifiedEntityTrait, RefreshTokenTrait;
}
