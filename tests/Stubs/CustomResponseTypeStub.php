<?php

namespace Preferans\Oauth\Tests\Stubs;

use Phalcon\Http\ResponseInterface;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;

class CustomResponseTypeStub implements ResponseTypeInterface
{
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
    }

    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
    }

    public function generateHttpResponse(ResponseInterface $response)
    {
    }

    public function setEncryptionKey($key = null)
    {
    }
}
