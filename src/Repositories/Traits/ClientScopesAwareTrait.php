<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\ClientScopesAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait ClientScopesAwareTrait
{
    protected $clientScopesModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getClientScopesModelClass(): string
    {
        if (empty($this->clientScopesModelClass) || !class_exists($this->clientScopesModelClass)) {
            throw new IllegalStateException('Client Scopes model class is empty or class does not exist');
        }

        return $this->clientScopesModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientScopesModelClass(string $modelClass)
    {
        $this->clientScopesModelClass = $modelClass;
    }
}
