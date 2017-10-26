<?php

namespace Preferans\Oauth\Tests\Server\ResponseTypes;

use DateTime;
use DateInterval;
use Phalcon\Crypt;
use Phalcon\Http\Response;
use Phalcon\Security\Random;
use PHPUnit\Framework\TestCase;
use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Tests\Stubs\ScopeEntity;
use Preferans\Oauth\Tests\Stubs\ClientEntity;
use Preferans\Oauth\Tests\Helpers\KeysAwareTrait;
use Preferans\Oauth\Tests\Stubs\AccessTokenEntity;
use Preferans\Oauth\Tests\Stubs\RefreshTokenEntity;
use Preferans\Oauth\Server\ResponseType\BearerTokenResponse;

class BearerResponseTypeTest extends TestCase
{
    use KeysAwareTrait;

    /**
     * @test
     * @param string $className
     * @dataProvider providerBearerTokenResponse
     */
    public function shouldGenerateHttpResponse($className)
    {
        $responseType = $this->createBearerTokenResponse($className);

        $response = $responseType->generateHttpResponse(new Response());

        $this->assertEquals('200 OK', $response->getHeaders()->get('Status'));
        $this->assertEquals('no-cache', $response->getHeaders()->get('pragma'));
        $this->assertEquals('no-store', $response->getHeaders()->get('cache-control'));
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaders()->get('Content-Type'));

        $json = json_decode($response->getContent());

        $this->assertEquals('Bearer', $json->token_type);
        $this->assertTrue(isset($json->expires_in));
        $this->assertTrue(isset($json->access_token));
        $this->assertTrue(isset($json->refresh_token));
        $this->assertTrue(isset($json->scope));
        $this->assertEquals('basic1 basic2', $json->scope);

        if ($className === BearerTokenResponseWithParams::class) {
            $this->assertTrue(isset($json->foo));
            $this->assertEquals('bar', $json->foo);
        }
    }

    /**
     * @test
     * @expectedException \Preferans\Oauth\Exceptions\IllegalStateException
     * @expectedExceptionMessage AccessToken Entity were not set.
     */
    public function shouldThrowIllegalStateExceptionInCaseOfAbsenceAccessTokenEntity()
    {
        $responseType = new BearerTokenResponse();
        $responseType->setPrivateKey(new CryptKey($this->privateKey));
        $responseType->generateHttpResponse(new Response());
    }

    /**
     * @test
     * @expectedException \Preferans\Oauth\Exceptions\IllegalStateException
     * @expectedExceptionMessage CryptKey were not set.
     */
    public function shouldThrowIllegalStateExceptionInCaseOfAbsencePrivateKey()
    {
        $responseType = new BearerTokenResponse();
        $responseType->setAccessToken(new AccessTokenEntity('abcdef'));
        $responseType->generateHttpResponse(new Response());
    }

    public function providerBearerTokenResponse()
    {
        return [
            [BearerTokenResponse::class],
            [BearerTokenResponseWithParams::class],
        ];
    }

    /**
     * @param string $class
     * @return BearerTokenResponse
     */
    protected function createBearerTokenResponse($class)
    {
        /** @var BearerTokenResponse $responseType */
        $responseType = new $class();
        $responseType->setPrivateKey(new CryptKey($this->privateKey));

        $crypt = new Crypt();
        $crypt->setKey((new Random)->base64(36));

        $responseType->setCrypt($crypt);
        $responseType->setEncryptionKey($crypt->getKey());

        $accessToken = new AccessTokenEntity('abcdef');
        $accessToken->setExpiryDateTime((new DateTime())->add(new DateInterval('PT1H')));
        $accessToken->setClient(new ClientEntity('tester'));
        $accessToken->addScope(new ScopeEntity('basic1'));
        $accessToken->addScope(new ScopeEntity('basic2'));

        $refreshToken = new RefreshTokenEntity('abcdef');
        $refreshToken->setAccessToken($accessToken);
        $refreshToken->setExpiryDateTime((new DateTime())->add(new DateInterval('PT1H')));

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }
}
