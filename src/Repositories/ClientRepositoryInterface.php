<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ClientEntityInterface;

/**
 * Preferans\Oauth\Repositories\ClientRepositoryInterface
 *
 * @package Preferans\Oauth\Repositories
 */
interface ClientRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string      $clientIdentifier The client's identifier
     * @param null|string $grantType        The grant type used (if sent) [optional]
     * @param null|string $clientSecret     The client's secret (if sent) [optional]
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null);
}
