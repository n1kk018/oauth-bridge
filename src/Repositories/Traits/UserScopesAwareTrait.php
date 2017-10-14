<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\UserScopesAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait UserScopesAwareTrait
{
    protected $userScopesModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getUserScopesModelClass(): string
    {
        if (empty($this->userScopesModelClass) || !class_exists($this->userScopesModelClass)) {
            throw new IllegalStateException('UserScopes model class is empty or class does not exist');
        }

        return $this->userScopesModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setUserScopesModelClass(string $modelClass)
    {
        $this->userScopesModelClass = $modelClass;
    }
}
