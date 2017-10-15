<?php

namespace Preferans\Oauth\Server\Grant;

use DateInterval;
use Phalcon\Http\RequestInterface;
use Preferans\Oauth\Server\RequestEvent;
use Preferans\Oauth\Server\ResponseType\ResponseTypeInterface;
use Preferans\Oauth\Entities\UserEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

/**
 * Preferans\Oauth\Server\Grant\PasswordGrant
 *
 * @package Preferans\Oauth\Server\Grant
 */
class PasswordGrant extends AbstractGrant
{
    /**
     * Create a new password grant.
     *
     * @param UserRepositoryInterface         $userRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface      $request
     * @param ResponseTypeInterface $responseType
     * @param DateInterval          $accessTokenTTL
     *
     * @return ResponseTypeInterface
     */
    public function respondToAccessTokenRequest(
        RequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));
        $user = $this->validateUser($request, $client);

        // Finalize the requested scopes
        $scopes = $this->scopeRepository->finalizeScopes(
            $scopes,
            $this->getIdentifier(),
            $client,
            $user->getIdentifier()
        );

        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * Validate the user.
     *
     * @param RequestInterface      $request
     * @param ClientEntityInterface $client
     *
     * @throws OAuthServerException
     *
     * @return UserEntityInterface
     */
    protected function validateUser(RequestInterface $request, ClientEntityInterface $client)
    {
        $username = $this->getRequestParameter('username', $request);
        if (is_null($username)) {
            throw OAuthServerException::invalidRequest('username');
        }

        $password = $this->getRequestParameter('password', $request);
        if (is_null($password)) {
            throw OAuthServerException::invalidRequest('password');
        }

        $user = $this->userRepository->getUserEntityByUserCredentials(
            $username,
            $password,
            $this->getIdentifier(),
            $client
        );

        if (!$user instanceof UserEntityInterface) {
            $this->getEventsManager()->fire(RequestEvent::USER_AUTHENTICATION_FAILED, $request);

            throw OAuthServerException::invalidCredentials();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'password';
    }
}
