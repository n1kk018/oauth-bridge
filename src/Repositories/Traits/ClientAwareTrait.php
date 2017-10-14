<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\ClientAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait ClientAwareTrait
{
    protected $clientModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getClientModelClass(): string
    {
        if (empty($this->clientModelClass) || !class_exists($this->clientModelClass)) {
            throw new IllegalStateException('Client model class is empty or class does not exist');
        }

        return $this->clientModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientModelClass(string $modelClass)
    {
        $this->clientModelClass = $modelClass;
    }
}
