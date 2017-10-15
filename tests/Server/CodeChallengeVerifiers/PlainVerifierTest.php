<?php

namespace Preferans\Oauth\Tests\Server;

use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\CodeChallengeVerifiers\PlainVerifier;

class PlainVerifierTest extends TestCase
{
    /** @test */
    public function shouldReturnCorrectMethos()
    {
        $verifier = new PlainVerifier();
        $this->assertEquals('plain', $verifier->getMethod());
    }

    /** @test */
    public function shouldVerifyCodeChallenge()
    {
        $verifier = new PlainVerifier();

        $this->assertTrue($verifier->verifyCodeChallenge('foo', 'foo'));
        $this->assertFalse($verifier->verifyCodeChallenge('foo', 'bar'));
    }
}
