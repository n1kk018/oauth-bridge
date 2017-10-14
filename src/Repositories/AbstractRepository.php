<?php

namespace Preferans\Oauth\Repositories;

use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Preferans\Oauth\Interfaces\RepositoryInterface;

/**
 * Preferans\Oauth\Repositories\AbstractRepository
 *
 * @package Preferans\Oauth\Repositories
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The internal Models Manager.
     *
     * @var ManagerInterface
     */
    protected $modelsManager;

    /**
     * Sets Models Manager.
     *
     * @param ManagerInterface $modelsManager
     *
     * @return void
     */
    public function setModelManager(ManagerInterface $modelsManager)
    {
        $this->modelsManager = $modelsManager;
    }

    /**
     * Gets Models Manager.
     *
     * @return ManagerInterface
     */
    public function getModelManager(): ManagerInterface
    {
        return $this->modelsManager;
    }

    /**
     * {@inheritdoc}
     *
     * @return BuilderInterface
     */
    public function createQueryBuilder(): BuilderInterface
    {
        return $this->getModelManager()->createBuilder();
    }
}
