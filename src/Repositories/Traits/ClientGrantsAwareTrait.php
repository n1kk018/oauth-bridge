<?php

namespace Preferans\Oauth\Repositories\Traits;

use Preferans\Oauth\Exceptions\IllegalStateException;

/**
 * Preferans\Oauth\Repositories\Traits\ClientGrantsAwareTrait
 *
 * @package Preferans\Oauth\Repositories\Traits
 */
trait ClientGrantsAwareTrait
{
    protected $clientGrantsModelClass;

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws IllegalStateException
     */
    public function getClientGrantsModelClass(): string
    {
        if (empty($this->clientGrantsModelClass) || !class_exists($this->clientGrantsModelClass)) {
            throw new IllegalStateException('Client Grants model class is empty or class does not exist');
        }

        return $this->clientGrantsModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientGrantsModelClass(string $modelClass)
    {
        $this->clientGrantsModelClass = $modelClass;
    }
}
