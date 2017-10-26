<?php

namespace Preferans\Oauth\Tests\Stubs;

use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Tests\Helpers\IdentifyAwareTrait;
use Preferans\Oauth\Entities\Traits\NamedEntityTrait;
use Preferans\Oauth\Entities\Traits\IdentifiedEntityTrait;
use Preferans\Oauth\Entities\Traits\RedirectableEntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use IdentifiedEntityTrait, NamedEntityTrait, RedirectableEntityTrait, IdentifyAwareTrait;
}
