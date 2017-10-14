# oAuth Bridge

A library focused on API Authentication for Phalcon applications.

## Usage

### Setup

#### Access Token Repository


```php
namespace Preferans\Provider\AuthServerProvider;

use Phalcon\DiInterface;
use Acme\Models\Scopes;
use Acme\Models\AccessToken;
use Phalcon\Di\ServiceProviderInterface;
use Preferans\Oauth\Repositories\AccessTokenRepository;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $container)
    {
        $container->setShared(
            AccessTokenRepositoryInterface::class,
            function () {
                $repository = new AccessTokenRepository();
                $repository->setScopeModelClass(Scopes::class);
                $repository->setAccessTokensModelClass(AccessToken::class);

                return $repository;
            }
        );
    }
}
````

#### Scope Repository

```php
namespace Preferans\Provider\ScopeRepositoryProvider;

use Phalcon\DiInterface;
use Acme\Models\Users;
use Acme\Models\Grants;
use Acme\Models\Scopes;
use Acme\Models\Clients;
use Acme\Models\UserScopes;
use Acme\Models\GrantScopes;
use Acme\Models\ClientScopes;
use Phalcon\Di\ServiceProviderInterface;
use Preferans\Oauth\Repositories\ScopeRepository;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $container)
    {
        $container->setShared(
            ScopeRepositoryInterface::class,
            function () {
                $repository = new ScopeRepository(
                    true, // Limit scopes to grants
                    true, // Limit clients to scopes
                    true  // Limit users to scopes
                );

                $repository->setScopeModelClass(Scopes::class);
                $repository->setGrantScopesModelClass(GrantScopes::class);
                $repository->setGrantsModelClass(Grants::class);
                $repository->setClientModelClass(Clients::class);
                $repository->setClientScopesModelClass(ClientScopes::class);
                $repository->setUserModelClass(Users::class);
                $repository->setUserScopesModelClass(UserScopes::class);

                return $repository;
            }
        );
    }
}
```

## License

oAuth Bridge is open source software licensed under the MIT License.
See the [`LICENSE.txt`](LICENSE.txt) file for more.

© 2017 Serghei Iakovlev. All rights reserved.
