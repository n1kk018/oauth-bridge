<?php

namespace Preferans\Oauth\Tests\Server;

use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\CryptKey;

class CryptKeyTest extends TestCase
{
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
        $keyFile = __DIR__ . '/../_data/public.key';
        $key = new CryptKey($keyFile, 'secret');

        $this->assertEquals('file://' . $keyFile, $key->getKeyPath());
        $this->assertEquals('secret', $key->getPassPhrase());
    }
}
