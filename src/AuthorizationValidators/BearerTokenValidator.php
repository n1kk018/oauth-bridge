<?php

namespace Preferans\Oauth\AuthorizationValidators;

use RuntimeException;
use Lcobucci\JWT\Parser;
use InvalidArgumentException;
use Lcobucci\JWT\ValidationData;
use Phalcon\Http\RequestInterface;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Preferans\Oauth\Server\CryptKey;
use Preferans\Oauth\Traits\CryptAwareTrait;
use Preferans\Oauth\Http\AttributesAwareRequest;
use Preferans\Oauth\Exceptions\OAuthServerException;
use Preferans\Oauth\Http\AttributesAwareRequestInterface;
use Preferans\Oauth\Repositories\AccessTokenRepositoryInterface;

/**
 * Preferans\Oauth\AuthorizationValidators\BearerTokenValidator
 *
 * @package Preferans\Oauth\AuthorizationValidators
 */
class BearerTokenValidator implements AuthorizationValidatorInterface
{
    use CryptAwareTrait;

    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var CryptKey
     */
    protected $publicKey;

    /**
     * BearerTokenValidator constructor.
     *
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     */
    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * Set the public key
     *
     * @param CryptKey $key
     */
    public function setPublicKey(CryptKey $key)
    {
        $this->publicKey = $key;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     * @throws OAuthServerException
     */
    public function validateAuthorization(RequestInterface $request)
    {
        $header = $request->getHeader('authorization');

        if (empty($header)) {
            throw OAuthServerException::accessDenied('Missing "Authorization" header');
        }

        $jwt = $this->parseBearerValue($header);
        if (empty($jwt) || !is_string($jwt)) {
            throw OAuthServerException::accessDenied('Missing "Authorization" header');
        }

        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($jwt);
            if (!$token->verify(new Sha256(), $this->publicKey->getKeyPath())) {
                throw OAuthServerException::accessDenied('Access token could not be verified');
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if (!$token->validate($data)) {
                throw OAuthServerException::accessDenied('Access token is invalid');
            }

            // Check if token has been revoked
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw OAuthServerException::accessDenied('Access token has been revoked');
            }

            // Since Phalcon's Request is immutable we can safely create a new instance here.
            // But in fact, it is recommended to use the AttributesAwareRequestInterface in your
            // applications to achieve maximum performance.
            if (!$request instanceof AttributesAwareRequestInterface) {
                $request = new AttributesAwareRequest();
            }

            // Return the request with additional attributes
            return $request
                ->setAttribute('oauth_access_token_id', $token->getClaim('jti'))
                ->setAttribute('oauth_client_id', $token->getClaim('aud'))
                ->setAttribute('oauth_user_id', $token->getClaim('sub'))
                ->setAttribute('oauth_scopes', $token->getClaim('scopes'));
        } catch (InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied($exception->getMessage());
        } catch (RuntimeException $exception) {
            // JWR couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied('Error while decoding to JSON');
        }
    }

    /**
     * Parse Bearer Header
     *
     * @param string $header
     *
     * @return null|string
     */
    protected function parseBearerValue($header)
    {
        if (strpos(trim($header), 'Bearer') !== 0) {
            return null;
        }

        return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header));
    }
}
