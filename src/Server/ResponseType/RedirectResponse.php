<?php

namespace Preferans\Oauth\Server\ResponseType;

use Phalcon\Http\ResponseInterface;

/**
 * Preferans\Oauth\Server\ResponseType\RedirectResponse
 *
 * @package Preferans\Oauth\Server\ResponseType
 */
class RedirectResponse extends AbstractResponseType
{
    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * {@inheritdoc}
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function generateHttpResponse(ResponseInterface $response)
    {
        return $response
            ->setStatusCode(200)
            ->setHeader('Location', $this->redirectUri);
    }
}
