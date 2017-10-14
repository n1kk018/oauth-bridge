<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Preferans\Oauth\Entities\Traits\RedirectableEntityTrait;

/**
 * Preferans\Oauth\Entities\AuthCodeEntity
 *
 * @package Preferans\Oauth\Entities
 */
class AuthCodeEntity implements AuthCodeEntityInterface
{
    use IdentifiedEntityTrait, RedirectableEntityTrait, TokenEntityTrait;
}
