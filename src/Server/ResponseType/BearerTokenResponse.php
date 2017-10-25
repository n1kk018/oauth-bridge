<?php

namespace Preferans\Oauth\Server\ResponseType;

use DateTime;
use Phalcon\Http\ResponseInterface;
use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Exceptions\IllegalStateException;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;

/**
 * Preferans\Oauth\Server\ResponseType\BearerTokenResponse
 *
 * @package Preferans\Oauth\Server\ResponseType
 */
class BearerTokenResponse extends AbstractResponseType
{
    /**
     * {@inheritdoc}
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws IllegalStateException
     */
    public function generateHttpResponse(ResponseInterface $response)
    {
        if (!$this->accessToken instanceof AccessTokenEntityInterface) {
            throw new IllegalStateException(
                'AccessToken Entity were not set.'
            );
        }

        if (!$this->privateKey instanceof CryptKey) {
            throw new IllegalStateException(
                'CryptKey were not set.'
            );
        }

        $expireDateTime = $this->accessToken->getExpiryDateTime()->getTimestamp();
        $jwtAccessToken = $this->accessToken->convertToJWT($this->privateKey);

        $scopes = array_map(function (ScopeEntityInterface $scopeEntity) {
            return $scopeEntity->getIdentifier();
        }, $this->accessToken->getScopes());

        $responseParams = [
            'token_type'   => 'Bearer',
            'expires_in'   => $expireDateTime - (new DateTime())->getTimestamp(),
            'access_token' => (string) $jwtAccessToken,
            'scope'        => implode(' ', $scopes),
        ];

        if ($this->refreshToken instanceof RefreshTokenEntityInterface) {
            $data = json_encode(
                [
                    'client_id'        => $this->accessToken->getClient()->getIdentifier(),
                    'refresh_token_id' => $this->refreshToken->getIdentifier(),
                    'access_token_id'  => $this->accessToken->getIdentifier(),
                    'scopes'           => $this->accessToken->getScopes(),
                    'user_id'          => $this->accessToken->getUserIdentifier(),
                    'expire_time'      => $this->refreshToken->getExpiryDateTime()->getTimestamp(),
                ]
            );

            $responseParams['refresh_token'] = $this->getCrypt()->encryptBase64($data, $this->encryptionKey);
        }

        $responseParams = array_merge($this->getExtraParams($this->accessToken), $responseParams);

        $response
            ->setStatusCode(200)
            ->setHeader('pragma', 'no-cache')
            ->setHeader('cache-control', 'no-store')
            ->setJsonContent($responseParams);

        return $response;
    }

    /**
     * Add custom fields to your Bearer Token response here, then override
     * AuthorizationServer::getResponseType() to pull in your version of
     * this class rather than the default.
     *
     * @param AccessTokenEntityInterface $accessToken
     *
     * @return array
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        return [];
    }
}
