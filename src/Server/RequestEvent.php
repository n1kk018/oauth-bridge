<?php

namespace Preferans\Oauth\Server;

use League\Event\Event;
use Phalcon\Http\RequestInterface;

/**
 * Preferans\Oauth\Server\RequestEvent
 *
 * @package Preferans\Oauth\Server
 */
class RequestEvent extends Event
{
    const CLIENT_AUTHENTICATION_FAILED = 'client.authentication.failed';
    const USER_AUTHENTICATION_FAILED = 'user.authentication.failed';
    const REFRESH_TOKEN_CLIENT_FAILED = 'refresh_token.client.failed';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * RequestEvent constructor.
     *
     * @param string           $name
     * @param RequestInterface $request
     */
    public function __construct($name, RequestInterface $request)
    {
        parent::__construct($name);

        $this->request = $request;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
