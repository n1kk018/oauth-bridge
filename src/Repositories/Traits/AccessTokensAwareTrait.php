<?php

namespace Preferans\Oauth\Repositories\Traits;

use Phalcon\Mvc\ModelInterface;
use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\AccessTokensAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait AccessTokensAwareTrait
{
    protected $accessTokensModelClass;

    /**
     * Gets AccessTokens model class.
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getAccessTokensModelClass(): string
    {
        if (empty($this->accessTokensModelClass) || !class_exists($this->accessTokensModelClass)) {
            throw new IllegalStateException('AccessTokens model class is empty or class does not exist');
        }

        return $this->accessTokensModelClass;
    }

    /**
     * Sets AccessTokens model class.
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setAccessTokensModelClass(string $modelClass)
    {
        $this->accessTokensModelClass = $modelClass;
    }

    /**
     * Tries to get AccessToken from the database.
     *
     * @param $identity
     *
     * @return ModelInterface|null
     */
    protected function findByIdentity($identity)
    {
        $accessToken = $this->createAccessTokensModel();

        $accessToken = $accessToken::findFirst([
            'conditions' => 'id = :identity:',
            'bind'       => compact('identity'),
        ]);

        return $accessToken ?? null;
    }

    /**
     * Creates a AccessToken Model instance.
     *
     * @return ModelInterface
     */
    protected function createAccessTokensModel(): ModelInterface
    {
        $accessTokenModelClass = $this->getAccessTokensModelClass();

        return new $accessTokenModelClass();
    }
}
