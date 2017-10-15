<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Exceptions;
use Preferans\Oauth\Entities\RefreshTokenEntity;
use Preferans\Oauth\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

/**
 * Preferans\Oauth\Repositories\RefreshTokenRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    use Traits\RefreshTokensAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }

    /**
     * {@inheritdoc}
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshToken = $this->createRefreshTokensModel();
        $refreshToken->assign(
            [
                'id'              => $refreshTokenEntity->getIdentifier(),
                'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
                'expiration'      => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
            ]
        );

        if ($this->findByIdentity($refreshTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $refreshToken->save();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tokenId
     *
     * @return void
     * @throws Exceptions\EntityException
     */
    public function revokeRefreshToken($tokenId)
    {
        if (!$refreshToken = $this->findByIdentity($tokenId)) {
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
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->createRefreshTokensModel();

        $revoked = $refreshToken::count([
            'conditions' => 'id = :identity: AND (revoked = 1 OR expiration <= ":expiration:")',
            'bind'       => [
                'identity'   => $tokenId,
                'expiration' => date('Y-m-d H:i:s'),
            ],
        ]);

        return $revoked > 0;
    }
}
