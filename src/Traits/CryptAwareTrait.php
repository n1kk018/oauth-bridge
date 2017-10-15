<?php

namespace Preferans\Oauth\Traits;

use Phalcon\Crypt;

/**
 * Preferans\Oauth\Traits\CryptAwareTrait
 *
 * @package Preferans\Oauth\Traits
 */
trait CryptAwareTrait
{
    /**
     * @var string
     */
    protected $encryptionKey;

    /**
     * The Crypt instance.
     *
     * @var Crypt|null
     */
    protected $crypt;

    /**
     * Sets the Crypt.
     *
     * @param Crypt $crypt
     */
    public function setCrypt(Crypt $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Returns the internal Crypt.
     *
     * @return Crypt
     */
    public function getCrypt()
    {
        if (!$this->crypt) {
            $this->setCrypt(new Crypt());
        }

        return $this->crypt;
    }
}
