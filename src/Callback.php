<?php namespace Cartalyst\Conditions;

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

use ArrayAccess;

class Callback
{
    /**
     * Holds the container instance.
     *
     * @var ArrayAccess
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
     * Holds the callback's payload.
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Constructor.
     *
     * @param  string  $callback
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
     * @param  ArrayAccess  $container
     * @return void
     */
    public static function setContainer(ArrayAccess $container)
    {
        static::$container = $container;
    }

    /**
     * Fire the callback with the given parameters.
     *
     * @return mixed
     */
    public function __invoke()
    {
        $params = func_get_args();

        $params[] = $this->payload;

        return call_user_func_array([$this->resolve(), $this->method], $params);
    }

    /**
     * Sets the callback class and method names.
     *
     * @param  string  $callback
     * @return void
     */
    protected function setCallback($callback)
    {
        $fragments = explode('@', $callback);

        $this->class = $fragments[0];

        $this->method = isset($fragments[1]) ? $fragments[1] : 'fire';
    }

    /**
     * Resolves the callback class name to a concrete instance.
     *
     * @return mixed
     */
    protected function resolve()
    {
        if (static::$container) {
            return static::$container[$this->class];
        }

        return new $this->class;
    }
}
