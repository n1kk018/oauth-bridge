<?php

namespace Preferans\Oauth\Repositories;

use Phalcon\Security;
use Preferans\Oauth\Entities\UserEntity;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

/**
 * Preferans\Oauth\Repositories\UserRepository
 *
 * @package Preferans\Oauth\Repositories
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    use Traits\UserAwareTrait, Traits\GrantsAwareTrait, Traits\UserGrantsAwareTrait;

    protected $limitUsersToClients;
    protected $limitUsersToGrants;

    /**
     * ScopeRepository constructor.
     *
     * @param bool $limitUsersToClients
     * @param bool $limitUsersToGrants
     */
    public function __construct($limitUsersToClients = true, $limitUsersToGrants = true)
    {
        $this->limitUsersToClients = (bool)$limitUsersToClients;
        $this->limitUsersToGrants = (bool)$limitUsersToGrants;
    }

    /**
     * {@inheritdoc}
     *
     * @param string                $username
     * @param string                $password
     * @param string                $grantType The grant type used
     * @param ClientEntityInterface $client
     *
     * @return UserEntityInterface
     * @throws OAuthServerException
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client)
    {
        $builder = $this->createQueryBuilder()
            ->columns(['u.id', 'u.name', 'u.password'])
            ->addFrom($this->getUserModelClass(), 'u')
            ->where('u.name = :username:', compact('username'))
            ->limit(1);

        if ($this->limitUsersToGrants) {
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
