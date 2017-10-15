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
    public function getEventsManager()
    {
        if (!$this->eventsManager) {
            $this->setEventsManager(new Manager());
        }

        return $this->eventsManager;
    }
}
