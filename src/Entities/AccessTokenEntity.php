<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

/**
 * Preferans\Oauth\Entities\AccessTokenEntity
 *
 * @package Preferans\Oauth\Entities
 */
class AccessTokenEntity implements AccessTokenEntityInterface
{
    use AccessTokenTrait, TokenEntityTrait, IdentifiedEntityTrait;
}
