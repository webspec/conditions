<?php

/**
 * Part of the Conditions package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Conditions
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Conditions;

use ArrayAccess;

class Callback
{
    /**
     * Holds the container instance.
     *
     * @var \ArrayAccess
     */
    protected static $container;

    /**
     * Holds the callback's class name.
     *
     * @var string
     */
    protected $class;

    /**
     * Holds the callback's method name.
     *
     * @var string
     */
    protected $method;

    /**
     * Whether the callback method should be called statically.
     *
     * @var bool
     */
    protected $static = false;

    /**
     * Holds the callback's payload.
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Constructor.
     *
     * @param  string|array  $callback
     * @param  mixed  $payload
     * @return void
     */
    public function __construct($callback, $payload = [])
    {
        $this->setCallback($callback);

        $this->payload = $payload;
    }

    /**
     * Make a new Callback instance.
     *
     * @param  string  $callback
     * @param  mixed  $payload
     * @return static
     */
    public static function make($callback, $payload = [])
    {
        return new static($callback, $payload);
    }

    /**
     * Sets the container instance.
     *
     * @param  \ArrayAccess  $container
     * @return void
     */
    public static function setContainer(ArrayAccess $container)
    {
        static::$container = $container;
    }

    /**
     * Fires the callback with the given parameters.
     *
     * @return mixed
     */
    public function __invoke()
    {
        $params = func_get_args();

        array_unshift($params, $this->payload);

        return call_user_func_array([ $this->resolve(), $this->method ], $params);
    }

    /**
     * Sets the callback class and method names.
     *
     * @param  object|string|array  $callback
     * @return void
     */
    protected function setCallback($callback)
    {
        if (is_object($callback)) {
            return $this->setObjectCallback($callback);
        }

        if (is_array($callback)) {
            return $this->setArrayCallback($callback);
        }

        return $this->setStringCallback($callback);
    }

    /**
     * Sets the callback class and method names via an object.
     *
     * @param  object  $callback
     * @return void
     */
    protected function setObjectCallback($callback)
    {
        $this->class = get_class($callback);

        $this->method = 'fire';
    }

    /**
     * Sets the callback class and method names via an array.
     *
     * @param  array  $callback
     * @return void
     */
    protected function setArrayCallback(array $callback)
    {
        $this->static = is_string($callback[0]);

        $this->class = $this->static ? $callback[0] : get_class($callback[0]);

        $this->method = isset($callback[1]) ? $callback[1] : 'fire';
    }

    /**
     * Sets the callback class and method names via a string.
     *
     * @param  string  $callback
     * @return void
     */
    protected function setStringCallback($callback)
    {
        $this->static = strpos($callback, '::') !== false;

        $fragments = preg_split('/@|::/', $callback);

        $this->class = $fragments[0];

        $this->method = isset($fragments[1]) ? $fragments[1] : 'fire';
    }

    /**
     * Returns the resolved callback instance or class name.
     *
     * @return string|object
     */
    protected function resolve()
    {
        if ($this->static) {
            return $this->class;
        }

        if (static::$container) {
            return static::$container[$this->class];
        }

        return new $this->class;
    }
}
