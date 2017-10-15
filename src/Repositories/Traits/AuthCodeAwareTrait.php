<?php

namespace Preferans\Oauth\Repositories\Traits;

use Phalcon\Mvc\ModelInterface;
use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\AuthCodeAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait AuthCodeAwareTrait
{
    protected $authCodesModelClass;

    /**
     * Gets AuthCodes model class.
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getAuthCodesModelClass(): string
    {
        if (empty($this->authCodesModelClass) || !class_exists($this->authCodesModelClass)) {
            throw new IllegalStateException('AuthCodes model class is empty or class does not exist');
        }

        return $this->authCodesModelClass;
    }

    /**
     * Sets AuthCodes model class.
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setAuthCodesModelClass(string $modelClass)
    {
        $this->authCodesModelClass = $modelClass;
    }

    /**
     * Tries to get AuthCode from the database.
     *
     * @param $identity
     *
     * @return ModelInterface|null
     */
    protected function findByIdentity($identity)
    {
        $accessToken = $this->createAuthCodesModel();

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
    protected function createAuthCodesModel(): ModelInterface
    {
        $accessTokenModelClass = $this->getAuthCodesModelClass();

        return new $accessTokenModelClass();
    }
}
