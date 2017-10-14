<?php

namespace Preferans\Oauth\Interfaces;

use League\OAuth2\Server\Repositories;

/**
 * Preferans\Oauth\Interfaces\ClientRepositoryInterface
 *
 * @package Preferans\Oauth\Interfaces
 */
interface ClientRepositoryInterface extends Repositories\ClientRepositoryInterface
{
    /**
     * Gets Client model class.
     *
     * @return string
     */
    public function getClientModelClass(): string;

    /**
     * Sets Client model class.
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientModelClass(string $modelClass);

    /**
     * Gets Grant model class.
     *
     * @return string
     */
    public function getGrantModelClass(): string;

    /**
     * Sets Grant model class.
     *
     * @param string $modelClass
     * @return void
     */
    public function setGrantModelClass(string $modelClass);

    /**
     * Gets ClientGrant model class.
     *
     * @return string
     */
    public function getClientGrantModelClass(): string;

    /**
     * Sets ClientGrant model class.
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientGrantModelClass(string $modelClass);
}
