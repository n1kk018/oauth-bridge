<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ScopeEntity;
use Preferans\Oauth\Entities\ScopeEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * Preferans\Oauth\Repositories\ClientRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    use Traits\ScopeAwareTrait, Traits\GrantScopesAwareTrait, Traits\GrantsAwareTrait, Traits\ClientsAwareTrait;
    use Traits\ClientScopesAwareTrait, Traits\UserAwareTrait, Traits\UserScopesAwareTrait;

    protected $limitScopesToGrants;
    protected $limitClientsToScopes;
    protected $limitUsersToScopes;

    /**
     * ScopeRepository constructor.
     *
     * @param bool|mixed $limitScopesToGrants
     * @param bool|mixed $limitClientsToScopes
     * @param bool|mixed $limitUsersToScopes
     */
    public function __construct($limitScopesToGrants = true, $limitClientsToScopes = true, $limitUsersToScopes = true)
    {
        $this->limitScopesToGrants = (bool)$limitScopesToGrants;
        $this->limitClientsToScopes = (bool)$limitClientsToScopes;
        $this->limitUsersToScopes = (bool)$limitUsersToScopes;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface
     * @throws OAuthServerException
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $result = $this->createQueryBuilder()
            ->columns(['s.id'])
            ->addFrom($this->getScopeModelClass(), 's')
            ->where('s.id = :identifier:', compact('identifier'))
            ->limit(1)
            ->getQuery()
            ->execute();

        if ($result->count() <= 0) {
            throw OAuthServerException::invalidScope($identifier);
        }

        $result = $result->getFirst();

        $scope = new ScopeEntity();
        $scope->setIdentifier($result->id);

        return $scope;
    }

    /**
     * {@inheritdoc}
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param ClientEntityInterface  $clientEntity
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     * @throws OAuthServerException
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $entities = [];

        if (empty($scopes)) {
            return $entities;
        }

        $builder = $this->createQueryBuilder()
            ->addFrom($this->getScopeModelClass(), 's')
            ->inWhere('s.id', array_map(function (ScopeEntityInterface $scope) {
                return $scope->getIdentifier();
            }, $scopes));

        if ($this->limitScopesToGrants) {
            $builder
                ->innerJoin($this->getGrantScopesModelClass(), 'gs.scope_id = s.id', 'gs')
                ->innerJoin($this->getGrantsModelClass(), 'g.id = gs.grant_id', 'g')
                ->andWhere('g.id = :grantType:', compact('grantType'));
        }

        if ($this->limitClientsToScopes) {
            $builder
                ->innerJoin($this->getClientScopesModelClass(), 'cs.scope_id = s.id', 'cs')
                ->innerJoin($this->getClientsModelClass(), 'c.id = cs.client_id', 'c')
                ->andWhere('c.id = :client_id:', [
                    'client_id' => $clientEntity->getIdentifier(),
                ]);
        }

        if ($this->limitUsersToScopes) {
            $builder
                ->innerJoin($this->getUserScopesModelClass(), 'us.scope_id = s.id', 'us')
                ->innerJoin($this->getUserModelClass(), 'u.id = us.user_id', 'u')
                ->andWhere('u.id = :userIdentifier:', compact('userIdentifier'));
        }

        $query = $builder->getQuery();
        $result = $query->execute();

        if (!$result || $result->count() <= 0) {
            $scope = current($scopes);
            throw OAuthServerException::invalidScope($scope->getIdentifier());
        }

        foreach ($result as $scope) {
            $entity = new ScopeEntity();
            $entity->setIdentifier($scope->id);

            $entities[] = $entity;
        }

        return $entities;
    }
}
