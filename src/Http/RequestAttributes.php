<?php

namespace Preferans\Oauth\Http;

use Phalcon\Http\Request;

/**
 * Preferans\Oauth\Http\RequestAttributes
 *
 * @package Preferans\Oauth\Http
 */
class RequestAttributes implements RequestAttributesInterface
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * {@inheritdoc}
     *
     * @return mixed[] Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     *
     * @return static
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name The attribute name.
     *
     * @return static
     */
    public function unsetAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }

        unset($this->attributes[$name]);

        return $this;
    }
}
