<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ClientEntity;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * Preferans\Oauth\Repositories\ClientRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    use Traits\GrantAwareTrait, Traits\ClientAwareTrait, Traits\ClientGrantsAwareTrait;

    protected $limitClientsToGrants = false;

    /**
     * ClientRepository constructor.
     *
     * @param bool $limitClientsToGrants
     */
    public function __construct($limitClientsToGrants = false)
    {
        $this->limitClientsToGrants = $limitClientsToGrants;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     * @param bool $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $builder = $this->createQueryBuilder()
            ->columns(['c.id', 'c.secret', 'c.name'])
            ->addFrom($this->getClientModelClass(), 'c')
            ->where('c.id = :clientIdentifier:', compact('clientIdentifier'))
            ->limit(1);

        if ($mustValidateSecret === true) {
            $builder->andWhere('c.secret = :clientSecret:', compact('clientSecret'));
        }

        if ($this->limitClientsToGrants) {
            $builder
                ->innerJoin($this->getClientGrantsModelClass(), 'cg.client_id = c.id', 'cg')
                ->innerJoin($this->getGrantModelClass(), 'g.id = cg.grant_id', 'g')
                ->andWhere('g.id = :grantType:', compact('grantType'));
        }

        $query = $builder->getQuery();
        $result = $query->execute();

        if ($result->count() <= 0) {
            throw OAuthServerException::invalidClient();
        }

        $result = $result->getFirst();

        $client = new ClientEntity();
        $client->setName($result->name);
        $client->setIdentifier($result->id);

        return $client;
    }
}
