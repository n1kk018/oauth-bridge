<?php

namespace Preferans\Oauth\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;

use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Preferans\Oauth\Entities\UserEntity
 *
 * @package Preferans\Oauth\Entities
 */
class UserEntity implements UserEntityInterface
{
    use IdentifiedEntityTrait;
}
