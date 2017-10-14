<?php

namespace Preferans\Oauth\Interfaces;

use Phalcon\Mvc\Model\ManagerInterface;

/**
 * Preferans\Oauth\Interfaces\RepositoryInterface
 *
 * @package Preferans\Oauth\Repositories
 */
interface RepositoryInterface
{
    /**
     * Sets Models Manager.
     *
     * @param ManagerInterface $modelsManager
     * @return void
     */
    public function setModelManager(ManagerInterface $modelsManager);

    /**
     * Gets Models Manager.
     *
     * @return ManagerInterface
     */
    public function getModelManager(): ManagerInterface;
}
