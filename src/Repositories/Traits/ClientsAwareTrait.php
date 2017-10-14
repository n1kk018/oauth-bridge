<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\ClientsAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait ClientsAwareTrait
{
    protected $clientsModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getClientsModelClass(): string
    {
        if (empty($this->clientsModelClass) || !class_exists($this->clientsModelClass)) {
            throw new IllegalStateException('Clients model class is empty or class does not exist');
        }

        return $this->clientsModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setClientsModelClass(string $modelClass)
    {
        $this->clientsModelClass = $modelClass;
    }
}
