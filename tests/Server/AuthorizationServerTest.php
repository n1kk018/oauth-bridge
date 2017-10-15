<?php

namespace Preferans\Oauth\Tests\Server;

use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\AuthorizationServer;
use Preferans\Oauth\Tests\Helpers\KeysAwareTrait;
use Preferans\Oauth\Tests\Stubs\CustomResponseTypeStub;
use Preferans\Oauth\Repositories\ScopeRepositoryInterface;
use Preferans\Oauth\Repositories\ClientRepositoryInterface;
use Preferans\Oauth\Repositories\AccessTokenRepositoryInterface;


class AuthorizationServerTest extends TestCase
{
    use KeysAwareTrait;

    /** @test */
    public function shouldGetCustomResponseType()
    {
        $server = new AuthorizationServer(
            $this->getMockBuilder(ClientRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock(),
            $this->privateKey,
            $this->publicKey,
            new CustomResponseTypeStub()
        );

        $method = new ReflectionMethod($server, 'getResponseType');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($server) instanceof CustomResponseTypeStub);
    }
}
