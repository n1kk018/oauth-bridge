<?php

namespace Preferans\Oauth\Tests\Server\Grant;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Tests\Stubs\ClientEntity;
use Preferans\Oauth\Server\Grant\AuthCodeGrant;
use Preferans\Oauth\Repositories\ClientRepositoryInterface;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Repositories\AuthCodeRepositoryInterface;
use Preferans\Oauth\Server\CodeChallengeVerifiers\S256Verifier;
use Preferans\Oauth\Server\CodeChallengeVerifiers\PlainVerifier;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;

class AuthCodeGrantTest extends TestCase
{
    /** @test */
    public function shouldValidateAuthorizationRequestCodeChallenge()
    {
        $client = new ClientEntity();
        $client->setRedirectUri('http://foo/bar');

        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['hasQuery', 'getQuery'])
            ->getMockForAbstractClass();

        $requestMock->method('getQuery')
            ->withConsecutive(
                ['client_id', null, null],
                ['redirect_uri', null, null],
                ['scope', null, null],
                //['response_type'], 'code',
                ['state', null, null],
                ['code_challenge'],
                ['code_challenge_method']
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                $client->getRedirectUri(),
                null,
                null,
                str_repeat('A', 48),
                'plain'
            );

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier(new PlainVerifier());
        $grant->setClientRepository($clientRepositoryMock);

        $authorizationRequest = $grant->validateAuthorizationRequest($requestMock);

        $this->assertInstanceOf(AuthorizationRequest::class, $authorizationRequest);
        $this->assertEquals('plain', $authorizationRequest->getCodeChallengeMethod());
        $this->assertEquals(str_repeat('A', 48), $authorizationRequest->getCodeChallenge());
    }
}
