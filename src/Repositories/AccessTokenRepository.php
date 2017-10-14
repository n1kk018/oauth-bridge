<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Exceptions;
use League\OAuth2\Server\Entities;
use Preferans\Oauth\Entities\AccessTokenEntity;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

/**
 * Preferans\Oauth\Repositories\AccessTokenRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    use Traits\ScopeAwareTrait, Traits\AccessTokensAwareTrait;

    protected $accessTokenModelClass;

    /**
     * {@inheritdoc}
     *
     * @param Entities\ClientEntityInterface  $clientEntity
     * @param Entities\ScopeEntityInterface[] $scopes
     * @param mixed                  $userIdentifier
     *
     * @return Entities\AccessTokenEntityInterface
     */
    public function getNewToken(Entities\ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     *
     * @param Entities\AccessTokenEntityInterface $accessTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAccessToken(Entities\AccessTokenEntityInterface $accessTokenEntity)
    {
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

        if ($accessToken = $this->findByIdentity($accessTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

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
            throw new Exceptions\EntityException('Unable to revoke access token entity');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tokenId
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $accessToken = $this->createAccessTokenModel();

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
