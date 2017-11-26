<?php

namespace Preferans\Oauth\Tests\Server;

use DateInterval;
use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Tests\Stubs\ClientEntity;
use Preferans\Oauth\Server\AuthorizationServer;
use Preferans\Oauth\Server\Grant\AuthCodeGrant;
use Preferans\Oauth\Tests\Helpers\KeysAwareTrait;
use Preferans\Oauth\Tests\Stubs\CustomResponseTypeStub;
use Preferans\Oauth\Repositories\ScopeRepositoryInterface;
use Preferans\Oauth\Repositories\ClientRepositoryInterface;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Repositories\AuthCodeRepositoryInterface;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;
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

    /** @test */
    public function shouldValidateAuthorizationRequest()
    {
        $client = new ClientEntity();
        $client->setRedirectUri('http://foo/bar');

        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->setClientRepository($clientRepositoryMock);

        $server = new AuthorizationServer(
            $clientRepositoryMock,
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock(),
            $this->privateKey,
            $this->publicKey
        );

        $server->enableGrantType($grant);

        $requestMock = $this->getRequestMock();

        $requestMock
            ->method('hasQuery')
            ->withConsecutive(
                ['client_id']
            )
            ->willReturnOnConsecutiveCalls(
                true
            );

        $requestMock
            ->method('getQuery')
            ->withConsecutive(
                ['response_type', null, null],
                ['client_id', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'code',
                'foo'
            );

        $this->assertTrue($server->validateAuthorizationRequest($requestMock) instanceof AuthorizationRequest);
    }

    /**
     * @test
     * @expectedException \Preferans\Oauth\Exceptions\OAuthServerException
     * @expectedExceptionMessage Client authentication failed
     */
    public function shouldValidateAuthorizationRequestWithMissingRedirectUri()
    {
        $client = new ClientEntity();

        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->setClientRepository($clientRepositoryMock);

        $server = new AuthorizationServer(
            $clientRepositoryMock,
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock(),
            $this->privateKey,
            $this->publicKey
        );

        $server->enableGrantType($grant);

        $requestMock = $this->getRequestMock();

        $requestMock
            ->method('hasQuery')
            ->withConsecutive(
                ['client_id']
            )
            ->willReturnOnConsecutiveCalls(
                true
            );

        $requestMock
            ->method('getQuery')
            ->withConsecutive(
                ['response_type', null, null],
                ['client_id', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'code',
                'foo'
            );

        $server->validateAuthorizationRequest($requestMock);
    }

    protected function getRequestMock(array $methods = [])
    {
        return $this->getMockBuilder(RequestInterface::class)
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }
}
