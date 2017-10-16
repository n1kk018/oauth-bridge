<?php

namespace Preferans\Oauth\Tests\Stubs;

use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use Preferans\Oauth\Server\ResponseType\AbstractResponseType;

class ResponseTypeStub extends AbstractResponseType
{
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function generateHttpResponse(ResponseInterface $response)
    {
        return new Response();
    }
}
