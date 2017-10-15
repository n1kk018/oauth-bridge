<?php

namespace Preferans\Oauth\Tests\Server;

use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Tests\Helpers\KeysAwareTrait;

class CryptKeyTest extends TestCase
{
    use KeysAwareTrait;

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Key path "file://non-existent-file" does not exist or is not readable
     */
    public function shouldThrowExceptionInCaseOfAbsenseFile()
    {
        new CryptKey('non-existent-file');
    }

    /** @test */
    public function shouldCreateKeyInstance()
    {
        $key = new CryptKey($this->publicKey, 'secret');

        $this->assertEquals('file://' . $this->publicKey, $key->getKeyPath());
        $this->assertEquals('secret', $key->getPassPhrase());
    }
}
