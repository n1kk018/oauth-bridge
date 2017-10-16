<?php

namespace Preferans\Oauth\Repositories;

use Phalcon\Security;
use Preferans\Oauth\Entities\UserEntity;
use Preferans\Oauth\Entities\UserEntityInterface;
use Preferans\Oauth\Entities\ClientEntityInterface;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Repositories\Traits\UserAwareTrait;
use Preferans\Oauth\Repositories\Traits\GrantsAwareTrait;
use Preferans\Oauth\Repositories\Traits\ClientsAwareTrait;
use Preferans\Oauth\Repositories\Traits\UserGrantsAwareTrait;
use Preferans\Oauth\Repositories\Traits\UserClientsAwareTrait;

/**
 * Preferans\Oauth\Repositories\UserRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    use UserAwareTrait, GrantsAwareTrait, UserGrantsAwareTrait, UserClientsAwareTrait, ClientsAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param string                     $username
     * @param string                     $password
     * @param string|null                $grantType The grant type used
     * @param ClientEntityInterface|null $client
     *
     * @return UserEntityInterface
     * @throws OAuthServerException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType = null,
        ClientEntityInterface $client = null
    ) {
        $builder = $this->createQueryBuilder()
            ->columns(['u.id', 'u.name', 'u.password'])
            ->addFrom($this->getUserModelClass(), 'u')
            ->where('u.name = :username:', compact('username'))
            ->limit(1);

        if ($client !== null) {
            $builder
                ->innerJoin($this->getUserClientsModelClass(), 'uc.user_id = u.id', 'uc')
                ->innerJoin($this->getClientsModelClass(), 'c.id = uc.client_id', 'c')
                ->andWhere('Client.id = :client_id:', [
                    'client_id' => $client->getIdentifier(),
                ]);
        }

        if ($grantType !== null) {
            $builder
                ->innerJoin($this->getUserGrantsModelClass(), 'ug.user_id = u.id', 'ug')
                ->innerJoin($this->getGrantsModelClass(), 'g.id = ug.grant_id', 'g')
                ->andWhere('g.id = :grantType:', compact('grantType'));
        }

        $query = $builder->getQuery();
        $result = $query->execute();

        if ($result->count() <= 0) {
            throw OAuthServerException::invalidCredentials();
        }

        $result = $result->getFirst();
        $security = $this->getSecurity();

        if ($security->checkHash($password, $result->password) !== true) {
            throw OAuthServerException::invalidCredentials();
        }

        $user = new UserEntity();
        $user->setIdentifier($result->id);

        return $user;
    }

    /**
     * Gets Security component.
     *
     * @return Security
     */
    protected function getSecurity()
    {
        if (!$this->getDI()->has('security')) {
            $this->getDI()->set('security', Security::class);
        }

        return $this->getDI()->get('security');
    }
}
