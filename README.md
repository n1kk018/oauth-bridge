# oAuth Bridge

A library focused on API Authentication for Phalcon applications.

## Usage

### Access Token Repository

#### Setup

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

## License

oAuth Bridge is open source software licensed under the MIT License.
See the [`LICENSE.txt`](LICENSE.txt) file for more.

© 2017 Serghei Iakovlev. All rights reserved.
