<?php

namespace Preferans\Oauth\Repositories\Traits;

use Phalcon\Mvc\ModelInterface;
use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\RefreshTokensAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait RefreshTokensAwareTrait
{
    protected $refreshTokensModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getRefreshTokensModelClass(): string
    {
        if (empty($this->refreshTokensModelClass) || !class_exists($this->refreshTokensModelClass)) {
            throw new IllegalStateException('RefreshTokens model class is empty or class does not exist');
        }

        return $this->refreshTokensModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setRefreshTokensModelClass(string $modelClass)
    {
        $this->refreshTokensModelClass = $modelClass;
    }

    /**
     * Tries to get RefreshTokens from the database.
     *
     * @param $identity
     *
     * @return ModelInterface|null
     */
    protected function findByIdentity($identity)
    {
        $refreshToken = $this->createRefreshTokensModel();

        $refreshToken = $refreshToken::findFirst([
            'conditions' => 'id = :identity:',
            'bind'       => compact('identity'),
        ]);

        return $refreshToken ?? null;
    }

    /**
     * Creates a RefreshTokens Model instance.
     *
     * @return ModelInterface
     */
    protected function createRefreshTokensModel(): ModelInterface
    {
        $refreshTokensModelClass = $this->getRefreshTokensModelClass();

        return new $refreshTokensModelClass();
    }
}
