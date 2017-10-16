<?php

namespace Preferans\Oauth\Tests\Server\Grant;

use DateInterval;
use Phalcon\Crypt;
use Phalcon\Security\Random;
use PHPUnit\Framework\TestCase;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Server\CodeChallengeVerifiers\CodeChallengeVerifierInterface;
use Preferans\Oauth\Server\CodeChallengeVerifiers\S256Verifier;
use Preferans\Oauth\Tests\Stubs\ScopeEntity;
use Preferans\Oauth\Tests\Stubs\ClientEntity;
use Preferans\Oauth\Server\Grant\AuthCodeGrant;
use Preferans\Oauth\Tests\Stubs\ResponseTypeStub;
use Preferans\Oauth\Tests\Stubs\AccessTokenEntity;
use Preferans\Oauth\Tests\Stubs\RefreshTokenEntity;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use Preferans\Oauth\Repositories\ScopeRepositoryInterface;
use Preferans\Oauth\Repositories\ClientRepositoryInterface;
use Preferans\Oauth\Server\RequestType\AuthorizationRequest;
use Preferans\Oauth\Repositories\AuthCodeRepositoryInterface;
use Preferans\Oauth\Repositories\AccessTokenRepositoryInterface;
use Preferans\Oauth\Server\CodeChallengeVerifiers\PlainVerifier;
use Preferans\Oauth\Repositories\RefreshTokenRepositoryInterface;

class AuthCodeGrantTest extends TestCase
{
    /** @test */
    public function shouldValidateAuthorizationRequestCodeChallenge()
    {
        $clientRepositoryMock = $this->getClientMock();

        $requestMock = $this->getRequestMock();

        $requestMock->method('getQuery')
            ->withConsecutive(
                ['client_id', null, null],
                ['redirect_uri', null, null],
                ['scope', null, null],
                ['state', null, null],
                ['code_challenge'],
                ['code_challenge_method']
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                'http://foo/bar',
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

    /**
     * @test
     * @expectedException \Preferans\Oauth\Exceptions\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function shouldThrowExceptionOnValidateAuthorizationRequestMissingCodeChallenge()
    {
        $clientRepositoryMock = $this->getClientMock();

        $requestMock = $this->getRequestMock();

        $requestMock->method('getQuery')
            ->withConsecutive(
                ['client_id', null, null],
                ['redirect_uri', null, null],
                ['scope', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                'http://foo/bar',
                null
            );

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier(new PlainVerifier());
        $grant->setClientRepository($clientRepositoryMock);

        $grant->validateAuthorizationRequest($requestMock);
    }

    /**
     * @test
     * @expectedException \Preferans\Oauth\Exceptions\OAuthServerException
     * @expectedExceptionCode 3
     */
    public function shouldThrowExceptionOnValidateAuthorizationRequestInvalidCodeChallenge()
    {
        $clientRepositoryMock = $this->getClientMock();

        $requestMock = $this->getRequestMock();

        $requestMock->method('getQuery')
            ->withConsecutive(
                ['client_id', null, null],
                ['redirect_uri', null, null],
                ['scope', null, null],
                ['state', null, null],
                ['code_challenge'],
                ['code_challenge_method']
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                'http://foo/bar',
                null,
                null,
                'foobar',
                'foo'
            );

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier(new PlainVerifier());
        $grant->setClientRepository($clientRepositoryMock);

        $grant->validateAuthorizationRequest($requestMock);
    }

    /**
     * @test
     * @dataProvider codeChallengeProvider
     * @param string $method
     * @param string $codeChallenge
     * @param CodeChallengeVerifierInterface $provider
     */
    public function shouldRespondToAccessTokenRequestCodeChallenge($method, $codeChallenge, $provider)
    {
        $clientRepositoryMock = $this->getClientMock('foo');

        $scopeRepositoryMock = $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock();
        $scopeEntity = new ScopeEntity();
        $scopeRepositoryMock->method('getScopeEntityByIdentifier')->willReturn($scopeEntity);
        $scopeRepositoryMock->method('finalizeScopes')->willReturnArgument(0);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();
        $accessTokenRepositoryMock->method('getNewToken')->willReturn(new AccessTokenEntity());
        $accessTokenRepositoryMock->method('persistNewAccessToken')->willReturnSelf();

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();
        $refreshTokenRepositoryMock->method('persistNewRefreshToken')->willReturnSelf();
        $refreshTokenRepositoryMock->method('getNewRefreshToken')->willReturn(new RefreshTokenEntity());

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier($provider);

        $crypt = new Crypt();

        $encryptionKey = (new Random)->base64(36);
        $crypt->setKey($encryptionKey);

        $grant->setClientRepository($clientRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setEncryptionKey($encryptionKey);

        $requestMock = $this->getRequestMock();

        $requestMock
            ->method('get')
            ->withConsecutive(
                ['client_id' , null, null],
                ['client_secret' , null, null],
                ['redirect_uri' , null, null],
                ['code' , null, null],
                ['redirect_uri' , null, null],
                ['code_verifier' , null, null],
                ['grant_type', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                null,
                'http://foo/bar',
                $crypt->encrypt(json_encode(
                    [
                        'auth_code_id'          => uniqid(),
                        'expire_time'           => time() + 3600,
                        'client_id'             => 'foo',
                        'user_id'               => 123,
                        'scopes'                => ['foo'],
                        'redirect_uri'          => 'http://foo/bar',
                        'code_challenge'        => $codeChallenge,
                        'code_challenge_method' => $method,
                    ]
                )),
                'http://foo/bar',
                'foobar',
                'authorization_code'
            );

        /** @var ResponseTypeStub $response */
        $response = $grant->respondToAccessTokenRequest(
            $requestMock,
            new ResponseTypeStub(),
            new DateInterval('PT10M')
        );

        $this->assertTrue($response->getAccessToken() instanceof AccessTokenEntityInterface);
        $this->assertTrue($response->getRefreshToken() instanceof RefreshTokenEntityInterface);
    }

    /**
     * @test
     * @dataProvider codeChallengeProvider
     * @param string $method
     * @param string $codeChallenge
     * @param CodeChallengeVerifierInterface $provider
     */
    public function shouldRespondToAccessTokenRequestBadCodeVerifier($method, $codeChallenge, $provider)
    {
        $clientRepositoryMock = $this->getClientMock('foo');

        $scopeRepositoryMock = $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock();
        $scopeEntity = new ScopeEntity();
        $scopeRepositoryMock->method('getScopeEntityByIdentifier')->willReturn($scopeEntity);
        $scopeRepositoryMock->method('finalizeScopes')->willReturnArgument(0);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();
        $accessTokenRepositoryMock->method('getNewToken')->willReturn(new AccessTokenEntity());
        $accessTokenRepositoryMock->method('persistNewAccessToken')->willReturnSelf();

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();
        $refreshTokenRepositoryMock->method('persistNewRefreshToken')->willReturnSelf();
        $refreshTokenRepositoryMock->method('getNewRefreshToken')->willReturn(new RefreshTokenEntity());

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier($provider);

        $crypt = new Crypt();

        $encryptionKey = (new Random)->base64(36);
        $crypt->setKey($encryptionKey);

        $grant->setClientRepository($clientRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setEncryptionKey($encryptionKey);

        $requestMock = $this->getRequestMock();

        $requestMock
            ->method('get')
            ->withConsecutive(
                ['client_id' , null, null],
                ['client_secret' , null, null],
                ['redirect_uri' , null, null],
                ['code' , null, null],
                ['redirect_uri' , null, null],
                ['code_verifier' , null, null],
                ['grant_type', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                null,
                'http://foo/bar',
                $crypt->encrypt(json_encode(
                    [
                        'auth_code_id'          => uniqid(),
                        'expire_time'           => time() + 3600,
                        'client_id'             => 'foo',
                        'user_id'               => 123,
                        'scopes'                => ['foo'],
                        'redirect_uri'          => 'http://foo/bar',
                        'code_challenge'        => 'foobar',
                        'code_challenge_method' => $method,
                    ]
                )),
                'http://foo/bar',
                'none',
                'authorization_code'
            );

        try {
            /** @var ResponseTypeStub $response */
            $response = $grant->respondToAccessTokenRequest(
                $requestMock,
                new ResponseTypeStub(),
                new DateInterval('PT10M')
            );
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Failed to verify `code_verifier`.');
        }
    }

    /** @test */
    public function shouldRespondToAccessTokenRequestMissingCodeVerifier()
    {
        $clientRepositoryMock = $this->getClientMock('foo');

        $scopeRepositoryMock = $this->getMockBuilder(ScopeRepositoryInterface::class)->getMock();
        $scopeEntity = new ScopeEntity();
        $scopeRepositoryMock->method('getScopeEntityByIdentifier')->willReturn($scopeEntity);
        $scopeRepositoryMock->method('finalizeScopes')->willReturnArgument(0);

        $accessTokenRepositoryMock = $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock();
        $accessTokenRepositoryMock->method('getNewToken')->willReturn(new AccessTokenEntity());
        $accessTokenRepositoryMock->method('persistNewAccessToken')->willReturnSelf();

        $refreshTokenRepositoryMock = $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock();
        $refreshTokenRepositoryMock->method('persistNewRefreshToken')->willReturnSelf();
        $refreshTokenRepositoryMock->method('getNewRefreshToken')->willReturn(new RefreshTokenEntity());

        $grant = new AuthCodeGrant(
            $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock(),
            $this->getMockBuilder(RefreshTokenRepositoryInterface::class)->getMock(),
            new DateInterval('PT10M')
        );

        $grant->enableCodeChallengeVerifier(new PlainVerifier());

        $crypt = new Crypt();

        $encryptionKey = (new Random)->base64(36);
        $crypt->setKey($encryptionKey);

        $grant->setClientRepository($clientRepositoryMock);
        $grant->setScopeRepository($scopeRepositoryMock);
        $grant->setAccessTokenRepository($accessTokenRepositoryMock);
        $grant->setRefreshTokenRepository($refreshTokenRepositoryMock);
        $grant->setEncryptionKey($encryptionKey);

        $requestMock = $this->getRequestMock();

        $requestMock
            ->method('get')
            ->withConsecutive(
                ['client_id' , null, null],
                ['client_secret' , null, null],
                ['redirect_uri' , null, null],
                ['code' , null, null],
                ['redirect_uri' , null, null],
                ['code_verifier' , null, null],
                ['grant_type', null, null]
            )
            ->willReturnOnConsecutiveCalls(
                'foo',
                null,
                'http://foo/bar',
                $crypt->encrypt(json_encode(
                    [
                        'auth_code_id'          => uniqid(),
                        'expire_time'           => time() + 3600,
                        'client_id'             => 'foo',
                        'user_id'               => 123,
                        'scopes'                => ['foo'],
                        'redirect_uri'          => 'http://foo/bar',
                        'code_challenge'        => 'foobar',
                        'code_challenge_method' => 'plain',
                    ]
                )),
                'http://foo/bar',
                null,
                'authorization_code'
            );

        try {
            /** @var ResponseTypeStub $response */
            $response = $grant->respondToAccessTokenRequest(
                $requestMock,
                new ResponseTypeStub(),
                new DateInterval('PT10M')
            );
        } catch (OAuthServerException $e) {
            $this->assertEquals($e->getHint(), 'Check the `code_verifier` parameter');
        }

    }

    public function codeChallengeProvider()
    {
        return [
            ['plain', 'foobar', new PlainVerifier()],
            ['S256', urlencode(base64_encode(hash('sha256', 'foobar'))), new S256Verifier()],
        ];
    }

    protected function getRequestMock(array $methods = [])
    {
        return $this->getMockBuilder(RequestInterface::class)
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }

    protected function getClientMock($identifier = null)
    {
        $client = new ClientEntity();
        $client->setRedirectUri('http://foo/bar');
        $client->setIdentifier($identifier);

        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        return $clientRepositoryMock;
    }
}
