<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\GrantsAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait GrantsAwareTrait
{
    protected $grantsModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getGrantsModelClass(): string
    {
        if (empty($this->grantsModelClass) || !class_exists($this->grantsModelClass)) {
            throw new IllegalStateException('Grants model class is empty or class does not exist');
        }

        return $this->grantsModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     *
     * @return void
     */
    public function setGrantsModelClass(string $modelClass)
    {
        $this->grantsModelClass = $modelClass;
    }
}
