<?php

namespace Preferans\Oauth\AuthorizationValidators;

use Phalcon\Http\RequestInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;

/**
 * Preferans\Oauth\AuthorizationValidators\AuthorizationValidatorInterface
 *
 * @package Preferans\Oauth\AuthorizationValidators
 */
interface AuthorizationValidatorInterface extends InjectionAwareInterface, EventsAwareInterface
{
    /**
     * Determine the access token in the authorization header
     * and append oAuth properties to the request as attributes.
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function validateAuthorization(RequestInterface $request);
}
