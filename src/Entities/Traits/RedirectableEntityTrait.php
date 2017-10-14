<?php

namespace Preferans\Oauth\Entities\Traits;

/**
 * Preferans\Oauth\Entities\Traits\RedirectableEntityTrait
 *
 * @package Preferans\Oauth\Entities\Traits
 */
trait RedirectableEntityTrait
{
    protected $redirectUri;

    /**
     * Sets the entity's redirect uri.
     *
     * @param string $redirectUri
     * @return void
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * gets the entity's redirect uri.
     *
     * @return string
     **/
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
}
