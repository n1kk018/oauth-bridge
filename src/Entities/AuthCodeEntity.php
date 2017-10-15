<?php

namespace Preferans\Oauth\Entities;

use Preferans\Oauth\Entities\Traits\TokenEntityTrait;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
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
