<?php

namespace Preferans\Oauth\Server\Grant;

use DateInterval;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Traits\RequestScopesAwareTrait;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;

/**
 * Preferans\Oauth\Server\Grant\ClientCredentialsGrant
 *
 * @package Preferans\Oauth\Server\Grant
 */
class ClientCredentialsGrant extends AbstractGrant
{
    use RequestScopesAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface      $request
     * @param ResponseTypeInterface $responseType
     * @param DateInterval          $accessTokenTTL
     *
     * @return ResponseTypeInterface
     * @throws OAuthServerException
     */
    public function respondToAccessTokenRequest(
        RequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);

        $scopes = $this->getScopesFromRequest($request);

        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, null, $scopes);

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'client_credentials';
    }
}
