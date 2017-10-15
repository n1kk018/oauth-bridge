<?php

namespace Preferans\Oauth\Exceptions;

/**
 * Preferans\Oauth\Exceptions\UniqueTokenIdentifierConstraintViolationException
 *
 * @package Preferans\Oauth\Exceptions
 */
class UniqueTokenIdentifierConstraintViolationException extends OAuthServerException
{
    public static function create()
    {
        $errorMessage = 'Could not create unique access token identifier';

        return new static($errorMessage, 100, 'access_token_duplicate', 500);
    }
}
