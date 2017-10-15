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
     * @param string      $clientIdentifier   The client's identifier
     * @param string      $grantType          The grant type used
     * @param null|string $clientSecret       The client's secret (if sent)
     * @param bool        $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true);
}
