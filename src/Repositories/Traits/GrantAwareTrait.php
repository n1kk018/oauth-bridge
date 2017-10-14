<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\GrantAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait GrantAwareTrait
{
    protected $grantModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getGrantModelClass(): string
    {
        if (empty($this->grantModelClass) || !class_exists($this->grantModelClass)) {
            throw new IllegalStateException('Grant model class is empty or class does not exist');
        }

        return $this->grantModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setGrantModelClass(string $modelClass)
    {
        $this->grantModelClass = $modelClass;
    }
}
