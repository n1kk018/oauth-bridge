<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Entities\ScopeEntityInterface;

/**
 * Preferans\Oauth\Repositories\ScopeRepositoryInterface
 *
 * @package Preferans\Oauth\Repositories
 */
interface ScopeRepositoryInterface extends RepositoryInterface
{
    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier);

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are
     * valid and optionally append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param ClientEntityInterface  $clientEntity
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    );
}
