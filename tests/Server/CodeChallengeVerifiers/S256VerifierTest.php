<?php

namespace Preferans\Oauth\Tests\Server;

use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\CodeChallengeVerifiers\S256Verifier;

class S256VerifierTest extends TestCase
{
    /** @test */
    public function shouldReturnCorrectMethos()
    {
        $verifier = new S256Verifier();
        $this->assertEquals('S256', $verifier->getMethod());
    }

    /** @test */
    public function shouldVerifyCodeChallenge()
    {
        $verifier = new S256Verifier();

        $this->assertTrue(
            $verifier->verifyCodeChallenge(
                'foo',
                strtr(rtrim(base64_encode(hash('sha256', 'foo')), '='), '+/', '-_')
            )
        );

        $this->assertFalse(
            $verifier->verifyCodeChallenge(
                'foo',
                strtr(rtrim(base64_encode(hash('sha256', 'bar')), '='), '+/', '-_')
            )
        );
    }
}
