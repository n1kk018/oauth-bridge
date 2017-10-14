<?php

namespace Preferans\Oauth\Repositories;

use Preferans\Oauth\Entities\ClientEntity;
use Preferans\Oauth\Exceptions;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Preferans\Oauth\Interfaces\ClientRepositoryInterface;

/**
 * Preferans\Oauth\Repositories\ClientRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    protected $grantModelClass;
    protected $clientModelClass;
    protected $clientGrantModelClass;
    protected $limitClientsToGrants = false;

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
        $builder = $this->modelsManager
            ->createBuilder()
            ->columns(['c.id', 'c.secret', 'c.name'])
            ->addFrom($this->getClientModelClass(), 'c')
            ->where('c.id = :clientIdentifier:', compact('clientIdentifier'))
            ->limit(1);

        if ($mustValidateSecret === true) {
            $builder->andWhere('c.secret = :clientSecret:', compact('clientSecret'));
        }

        if ($this->limitClientsToGrants) {
            $builder
                ->innerJoin($this->getClientGrantModelClass(), 'cg.client_id = c.id', 'cg')
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

    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws Exceptions\IllegalStateException
     */
    public function getClientModelClass(): string
    {
        if (empty($this->clientModelClass) || !class_exists($this->clientModelClass)) {
            throw new Exceptions\IllegalStateException('Client model class is empty or class does not exist');
        }

        return $this->clientModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientModelClass(string $modelClass)
    {
        $this->clientModelClass = $modelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getGrantModelClass(): string
    {
        return $this->grantModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setGrantModelClass(string $modelClass)
    {
        $this->grantModelClass = $modelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getClientGrantModelClass(): string
    {
        return $this->clientModelClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @return void
     */
    public function setClientGrantModelClass(string $modelClass)
    {
        $this->clientModelClass = $modelClass;
    }

    /**
     * Enables/Disables limit clients to grants.
     *
     * @param $flag
     * @return void
     */
    public function limitClientsToGrants($flag)
    {
        $this->limitClientsToGrants = (bool)$flag;
    }
}
