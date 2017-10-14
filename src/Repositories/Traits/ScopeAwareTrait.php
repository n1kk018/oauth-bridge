<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\ScopeAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait ScopeAwareTrait
{
    protected $scopeModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getScopeModelClass(): string
    {
        if (empty($this->scopeModelClass) || !class_exists($this->scopeModelClass)) {
            throw new IllegalStateException('Scope model class is empty or class does not exist');
        }

        return $this->scopeModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setScopeModelClass(string $modelClass)
    {
        $this->scopeModelClass = $modelClass;
    }
}
