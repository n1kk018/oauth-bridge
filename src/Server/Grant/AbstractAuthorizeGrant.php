<?php

namespace Preferans\Oauth\Server\Grant;

/**
 * Preferans\Oauth\Server\Grant\AbstractAuthorizeGrant
 *
 * @package Preferans\Oauth\Server\Grant
 */
abstract class AbstractAuthorizeGrant extends AbstractGrant
{
    /**
     * @param string $uri
     * @param array  $params
     * @param string $queryDelimiter
     *
     * @return string
     */
    public function makeRedirectUri($uri, $params = [], $queryDelimiter = '?')
    {
        $uri .= (strstr($uri, $queryDelimiter) === false) ? $queryDelimiter : '&';

        return $uri . http_build_query($params);
    }
}
