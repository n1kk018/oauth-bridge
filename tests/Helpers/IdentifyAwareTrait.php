<?php

namespace Preferans\Oauth\Tests\Helpers;

trait IdentifyAwareTrait
{
    public function __construct($identifier = null)
    {
        if ($identifier !== null) {
            $this->setIdentifier($identifier);
        }
    }
}
