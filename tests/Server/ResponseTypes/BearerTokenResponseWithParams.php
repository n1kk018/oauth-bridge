<?php

namespace Preferans\Oauth\Tests\Server\ResponseTypes;

use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Server\ResponseType\BearerTokenResponse;

class BearerTokenResponseWithParams extends BearerTokenResponse
{
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        return ['foo' => 'bar', 'token_type' => 'Should not overwrite'];
    }
}
