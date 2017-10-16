<?php

namespace Preferans\Oauth\Traits;

use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;

/**
 * Preferans\Oauth\Traits\RequestScopesAwareTrait
 *
 * @package Preferans\Oauth\Traits
 */
trait RequestScopesAwareTrait
{
    /**
     * Gets and validate scopes in the request.
     *
     * @param RequestInterface $request
     * @param bool $fromQueryString
     * @param string|string[]|null $redirectUri
     *
     * @throws OAuthServerException
     *
     * @return ScopeEntityInterface[]
     */
    public function getScopesFromRequest($request, $fromQueryString = false, $redirectUri = null): array
    {
        $scopes = [];

        $requestScopes = $fromQueryString ?
            $this->getQueryStringParameter('scope', $request):
            $this->getRequestParameter('scope', $request);

        if ($redirectUri !== null) {
            $redirectUri = is_array($redirectUri) ? $redirectUri[0] : $redirectUri;
        }

        if ($requestScopes !== null) {
            $scopes = $this->validateScopes($requestScopes, $redirectUri);
        }

        return $scopes;
    }
}
