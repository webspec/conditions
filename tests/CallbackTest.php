<?php

/*
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
 * @version    4.0.1
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Conditions\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Conditions\Callback;
use Cartalyst\Conditions\Condition;
use Cartalyst\Collections\Collection;

class CallbackTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->condition = new Condition([
            'target' => 'price',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_instantiate_a_callback_class_with_a_string()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $this->condition->setActions([
            'value' => Callback::make('Cartalyst\Conditions\Tests\TestCallback', ['foo']),
        ]);

        $this->assertSame(20.0, $this->condition->result($testCollection));
    }

    /** @test */
    public function it_can_instantiate_a_callback_class_with_an_object()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $this->condition->setActions([
            'value' => Callback::make(new TestCallback(), ['foo']),
        ]);

        $this->assertSame(20.0, $this->condition->result($testCollection));
    }

    /** @test */
    public function it_can_call_a_callback_statically()
    {
        $callback1 = Callback::make('Cartalyst\Conditions\Tests\TestCallbackStatic::fire');
        $this->assertSame(25, $callback1());

        $callback2 = Callback::make(['Cartalyst\Conditions\Tests\TestCallbackStatic', 'fire']);
        $this->assertSame(25, $callback2());
    }

    /** @test */
    public function it_can_instantiate_a_callback_class_with_an_array()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $this->condition->setActions([
            'value' => Callback::make([new TestCallback(), 'fire'], ['foo']),
        ]);

        $this->assertSame(20.0, $this->condition->result($testCollection));
    }

    /** @test */
    public function it_can_instantiate_a_callback_class_with_an_array_without_a_fire_method()
    {
        $testCollection = new Collection(['price' => 800, 'quantity' => 10]);

        $this->condition->setActions([
            'value' => Callback::make([new TestCallback()], ['foo']),
        ]);

        $this->assertSame(20.0, $this->condition->result($testCollection));
    }

    /** @test */
    public function it_can_resolve_callbacks_out_of_containers()
    {
        $container = new \Pimple\Container();
        $container->offsetSet('Cartalyst\Conditions\Tests\TestCallback', new TestCallback());

        Callback::setContainer($container);

        $callback = Callback::make('Cartalyst\Conditions\Tests\TestCallback');

        $this->assertInstanceOf('Pimple\Container', Callback::getContainer());

        $this->assertSame(20, $callback());
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
            ],
        ], function ($collection, $subtotal) use ($mock) {
            $mock->callback();

            $this->assertSame(1012.0, $subtotal);
        });

        $this->assertSame(1012.0, $this->condition->apply($testCollection));
        $this->assertSame(212.0, $this->condition->result());

        $this->condition->reset();

        // Invalid condition
        $this->condition->setRules(['price > 900']);

        $this->condition->setActions([
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@ten'),
            ],
            [
                'value' => Callback::make('Cartalyst\Conditions\Tests\PercentageCallback@fifteen'),
            ],
        ], function () use ($mock) {
            $mock->callback();
        });

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }
}

class TestCallback
{
    public function fire()
    {
        return 20;
    }
}

class TestCallbackStatic
{
    private function __construct()
    {
        //
    }

    public static function fire()
    {
        return 25;
    }
}

class PercentageCallback
{
    public function ten($payload, $collection, $value)
    {
        return $value * 10 / 100;
    }

    public function fifteen($payload, $collection, $value)
    {
        return $value * 15 / 100;
    }
}
