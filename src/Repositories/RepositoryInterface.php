<?php

namespace Preferans\Oauth\Repositories;


use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;

/**
 * Preferans\Oauth\Repositories\RepositoryInterface
 *
 * @package Preferans\Oauth\Repositories
 */
interface RepositoryInterface
{
    /**
     * Sets Models Manager.
     *
     * @param ManagerInterface $modelsManager
     *
     * @return void
     */
    public function setModelManager(ManagerInterface $modelsManager);

    /**
     * Gets Models Manager.
     *
     * @return ManagerInterface
     */
    public function getModelManager(): ManagerInterface;

    /**
     * Creates a Query Builder.
     *
     * @return BuilderInterface
     */
    public function createQueryBuilder(): BuilderInterface;
}
