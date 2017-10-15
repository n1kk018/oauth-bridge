<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Exceptions;
use Preferans\Oauth\Entities\AccessTokenEntity;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

/**
 * Preferans\Oauth\Repositories\AccessTokenRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    use Traits\ScopeAwareTrait, Traits\AccessTokensAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param ClientEntityInterface  $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed                           $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        if ($userIdentifier !== null) {
            $accessToken->setUserIdentifier($userIdentifier);
        }

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     *
     * @param AccessTokenEntityInterface $accessTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        if ($this->findByIdentity($accessTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $scopes = [];

        foreach ($accessTokenEntity->getScopes() as $scope) {
            $result = $this->createQueryBuilder()
                ->addFrom($this->getScopeModelClass())
                ->where('id = :id:', [
                    'id' => $scope->getIdentifier(),
                ])
                ->limit(1)
                ->getQuery()
                ->execute();

            if ($result->count() > 0) {
                $scopes[] = $result->getFirst();
            }
        }

        $accessToken = $this->createAccessTokensModel();
        $accessToken->assign(
            [
                'id'         => $accessTokenEntity->getIdentifier(),
                'user_id'    => $accessTokenEntity->getUserIdentifier(),
                'client_id'  => $accessTokenEntity->getClient()->getIdentifier(),
                'expiration' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
            ]
        );

        $accessToken->scopes = $scopes;
        $accessToken->save();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tokenId
     *
     * @return void
     * @throws Exceptions\EntityException
     */
    public function revokeAccessToken($tokenId)
    {
        if (!$accessToken = $this->findByIdentity($tokenId)) {
            return;
        }

        $accessToken->revoked = 1;
        if (!$accessToken->update()) {
            throw new Exceptions\EntityException('Unable to revoke access token');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tokenId
     *
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $accessToken = $this->createAccessTokensModel();

        $revoked = $accessToken::count([
            'conditions' => 'id = :identity: AND (revoked = 1 OR expiration <= ":expiration:")',
            'bind'       => [
                'identity'   => $tokenId,
                'expiration' => date('Y-m-d H:i:s'),
            ],
        ]);

        return $revoked > 0;
    }
}
