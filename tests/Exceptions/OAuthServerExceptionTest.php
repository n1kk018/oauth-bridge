<?php

namespace Preferans\Oauth\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Exceptions\OAuthServerException;

class OAuthServerExceptionTest extends TestCase
{
    /** @test */
    public function shouldCorrectDetectRedirect()
    {
        $this->assertFalse(OAuthServerException::accessDenied('Some hint')->hasRedirect());
        $this->assertTrue(OAuthServerException::accessDenied('some hint', 'https://example.com/error')->hasRedirect());
    }
}
