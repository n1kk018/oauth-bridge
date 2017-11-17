<?php

namespace Preferans\Oauth\Traits;

use Phalcon\Events\Manager;
use Phalcon\Events\ManagerInterface;

/**
 * Preferans\Oauth\Traits\EventsAwareTrait
 *
 * @package Preferans\Oauth\Traits
 */
trait EventsAwareTrait
{
    /**
     * The events manager instance.
     *
     * @var ManagerInterface|null
     */
    protected $eventsManager;

    /**
     * Sets the events manager.
     *
     * @param ManagerInterface $eventsManager
     * @return void
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Returns the internal event manager.
     *
     * @return ManagerInterface
     */
    public function getEventsManager(): ManagerInterface
    {
        if (!$this->eventsManager) {
            $this->eventsManager = new Manager();
        }

        return $this->eventsManager;
    }
}
