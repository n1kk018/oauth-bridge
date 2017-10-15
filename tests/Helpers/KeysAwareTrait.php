<?php

namespace Preferans\Oauth\Tests\Helpers;

trait KeysAwareTrait
{
    protected $privateKey;
    protected $publicKey;

    protected function setUp()
    {
        $this->privateKey = __DIR__ . '/../_data/public.key';
        $this->publicKey = __DIR__ . '/../_data/public.key';

        chmod($this->privateKey, 0600);
        chmod($this->publicKey, 0600);
    }
}
