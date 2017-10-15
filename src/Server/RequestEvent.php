<?php

namespace Preferans\Oauth\Server;

/**
 * Preferans\Oauth\Server\RequestEvent
 *
 * @package Preferans\Oauth\Server
 */
final class RequestEvent
{
    const CLIENT_AUTHENTICATION_FAILED = 'auth:client.authentication.failed';
    const USER_AUTHENTICATION_FAILED = 'auth:user.authentication.failed';
    const REFRESH_TOKEN_CLIENT_FAILED = 'auth:refresh_token.client.failed';
}
