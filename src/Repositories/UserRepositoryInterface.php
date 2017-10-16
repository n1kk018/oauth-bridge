<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\UserEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;

/**
 * Preferans\Oauth\Repositories\UserRepositoryInterface
 *
 * @package Preferans\Oauth\Repositories
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a user entity.
     *
     * @param string                     $username
     * @param string                     $password
     * @param string|null                $grantType The grant type used
     * @param ClientEntityInterface|null $clientEntity
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType = null,
        ClientEntityInterface $clientEntity = null
    );
}
