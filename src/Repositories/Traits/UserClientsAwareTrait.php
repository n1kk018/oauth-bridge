<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\UserClientsAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait UserClientsAwareTrait
{
    protected $userClientsModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getUserClientsModelClass(): string
    {
        if (empty($this->userClientsModelClass) || !class_exists($this->userClientsModelClass)) {
            throw new IllegalStateException('UserClients model class is empty or class does not exist');
        }

        return $this->userClientsModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setUserClientsModelClass(string $modelClass)
    {
        $this->userClientsModelClass = $modelClass;
    }
}
