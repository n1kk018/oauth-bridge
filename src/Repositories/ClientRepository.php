<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ClientEntity;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Entities\ClientEntityInterface;

/**
 * Preferans\Oauth\Repositories\ClientRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    use Traits\GrantsAwareTrait, Traits\ClientsAwareTrait, Traits\ClientGrantsAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param string      $clientIdentifier The client's identifier
     * @param null|string $grantType        The grant type used (if sent) [optional]
     * @param null|string $clientSecret     The client's secret (if sent) [optional]
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null)
    {
        $builder = $this->createQueryBuilder()
            ->columns(['c.id', 'c.secret', 'c.name'])
            ->addFrom($this->getClientsModelClass(), 'c')
            ->where('c.id = :clientIdentifier:', compact('clientIdentifier'))
            ->limit(1);

        if ($clientSecret !== null) {
            $builder->andWhere('c.secret = :clientSecret:', compact('clientSecret'));
        }

        if ($grantType !== null) {
            $builder
                ->innerJoin($this->getClientGrantsModelClass(), 'cg.client_id = c.id', 'cg')
                ->innerJoin($this->getGrantsModelClass(), 'g.id = cg.grant_id', 'g')
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
