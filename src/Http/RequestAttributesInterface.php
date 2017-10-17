<?php

namespace Preferans\Oauth\Http;

/**
 * Preferans\Oauth\Http\RequestAttributesInterface
 *
 * @package Preferans\Oauth\Http
 */
interface RequestAttributesInterface
{
    /**
     * Retrieve attributes derived from the request.
     *
     * @return mixed[] Attributes derived from the request.
     */
    public function getAttributes();

    /**
     * Retrieve a single derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null);

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     *
     * @return static
     */
    public function setAttribute($name, $value);

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name The attribute name.
     *
     * @return static
     */
    public function unsetAttribute($name);
}
