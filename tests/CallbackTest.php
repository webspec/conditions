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

namespace Cartalyst\Conditions\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Cartalyst\Conditions\Callback;
use Cartalyst\Conditions\Condition;
use Cartalyst\Collections\Collection;

class CallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup resources and dependencies
     */
    public function setUp()
    {
        $this->condition = new Condition([
            'target' => 'price',
        ]);
    }

    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function it_can_use_callback_class_instances()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $this->condition->setActions([
            'value' => Callback::make('Cartalyst\Conditions\Tests\TestCallback', ['foo']),
        ]);

        $this->assertEquals(20, $this->condition->result($testCollection));
    }

    /**
     * @test
     */
    public function it_can_resolve_callbacks_out_of_containers()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $testCallback = new TestCallback();

        $container = new \Illuminate\Container\Container;
        $container->offsetSet('Cartalyst\Conditions\Tests\TestCallback', $testCallback);

        Callback::setContainer($container);

        $callback = Callback::make('Cartalyst\Conditions\Tests\TestCallback');

        $this->assertEquals(20, $callback());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_run_a_callback_class_action_on_successful_validation()
    {
        $testCollection = new Collection(['price' => 800]);

        $mock = m::mock('callbackMock');

        $mock->shouldReceive('callback')->once();

        // Valid condition
        $this->condition->setActions([
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@ten'),
            ],
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@fifteen'),
            ]
        ], function ($collection, $subtotal) use ($mock) {
            $mock->callback();

            $this->assertEquals(1012, $subtotal);
        });

        $this->assertEquals(1012, $this->condition->apply($testCollection));
        $this->assertEquals(212, $this->condition->result());

        $this->condition->reset();

        // Invalid condition
        $this->condition->setRules([
            'price > 900',
        ]);

        $this->condition->setActions([
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@ten'),
            ],
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@fifteen'),
            ]
        ], function () use ($mock) {
            $mock->callback();
        });

        $this->assertEquals(false, $this->condition->apply($testCollection));
        $this->assertEquals(0, $this->condition->result());
    }
}

class TestCallback
{
    public function fire()
    {
        return 20;
    }
}

class PercentageCallback
{
    public function ten($collection, $value)
    {
        return $value * 10 / 100;
    }

    public function fifteen($collection, $value)
    {
        return $value * 15 / 100;
    }
}
