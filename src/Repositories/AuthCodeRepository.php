<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Exceptions;
use Preferans\Oauth\Entities\AuthCodeEntity;
use Preferans\Oauth\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

/**
 * Preferans\Oauth\Repositories\AuthCodeRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{
    use Traits\ScopeAwareTrait, Traits\AuthCodeAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * {@inheritdoc}
     *
     * @param AuthCodeEntityInterface $authCodeEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        if ($this->findByIdentity($authCodeEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $scopes = [];

        foreach ($authCodeEntity->getScopes() as $scope) {
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

        $refreshToken = $this->createAuthCodesModel();
        $refreshToken->assign(
            [
                'id'           => $authCodeEntity->getIdentifier(),
                'user_id'      => $authCodeEntity->getUserIdentifier(),
                'client_id'    => $authCodeEntity->getClient()->getIdentifier(),
                'expiration'   => $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
                'redirect_uri' => $authCodeEntity->getRedirectUri(),
            ]
        );

        $refreshToken->scopes = $scopes;
        $refreshToken->save();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {
        if (!$refreshToken = $this->findByIdentity($codeId)) {
            return;
        }

        $refreshToken->revoked = 1;
        if (!$refreshToken->update()) {
            throw new Exceptions\EntityException('Unable to revoke refresh token');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {
        $accessToken = $this->createAuthCodesModel();

        $revoked = $accessToken::count([
            'conditions' => 'id = :identity: AND (revoked = 1 OR expiration <= ":expiration:")',
            'bind'       => [
                'identity'   => $codeId,
                'expiration' => date('Y-m-d H:i:s'),
            ],
        ]);

        return $revoked > 0;
    }
}
