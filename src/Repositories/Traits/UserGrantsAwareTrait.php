<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\UserGrantsAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait UserGrantsAwareTrait
{
    protected $userGrantsModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getUserGrantsModelClass(): string
    {
        if (empty($this->userGrantsModelClass) || !class_exists($this->userGrantsModelClass)) {
            throw new IllegalStateException('UserGrants model class is empty or class does not exist');
        }

        return $this->userGrantsModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setUserGrantsModelClass(string $modelClass)
    {
        $this->userGrantsModelClass = $modelClass;
    }
}
