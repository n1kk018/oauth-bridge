<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\GrantScopesAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait GrantScopesAwareTrait
{
    protected $grantScopesModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getGrantScopesModelClass(): string
    {
        if (empty($this->grantScopesModelClass) || !class_exists($this->grantScopesModelClass)) {
            throw new IllegalStateException('GrantScopes model class is empty or class does not exist');
        }

        return $this->grantScopesModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setGrantScopesModelClass(string $modelClass)
    {
        $this->grantScopesModelClass = $modelClass;
    }
}
