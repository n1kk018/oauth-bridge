<?php

namespace Preferans\Oauth\Interfaces;

use League\OAuth2\Server\Repositories;

/**
 * Preferans\Oauth\Interfaces\AccessTokenRepositoryInterface
 *
 * @package Preferans\Oauth\Interfaces
 */
interface AccessTokenRepositoryInterface extends Repositories\AccessTokenRepositoryInterface
{
    /**
     * Gets Scope model class.
     *
     * @return string
     */
    public function getScopeModelClass(): string;

    /**
     * Sets Scope model class.
     *
     * @param string $modelClass
     * @return void
     */
    public function setScopeModelClass(string $modelClass);

    /**
     * Gets AccessToken model class.
     *
     * @return string
     */
    public function getAccessTokenModelClass(): string;

    /**
     * Sets AccessToken model class.
     *
     * @param string $modelClass
     * @return mixed
     */
    public function setAccessTokenModelClass(string $modelClass);
}
