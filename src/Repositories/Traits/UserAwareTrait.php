<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\UserAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait UserAwareTrait
{
    protected $userModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getUserModelClass(): string
    {
        if (empty($this->userModelClass) || !class_exists($this->userModelClass)) {
            throw new IllegalStateException('User model class is empty or class does not exist');
        }

        return $this->userModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setUserModelClass(string $modelClass)
    {
        $this->userModelClass = $modelClass;
    }
}
