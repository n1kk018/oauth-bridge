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
     * @param string                $username
     * @param string                $password
     * @param string                $grantType    The grant type used
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    );
}
