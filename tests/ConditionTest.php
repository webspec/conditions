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
 * @version    4.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Conditions\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Conditions\Condition;
use Cartalyst\Collections\Collection;

class ConditionTest extends TestCase
{
    /**
     * Holds the conditions instance.
     *
     * @var \Cartalyst\Conditions\Condition
     */
    protected $condition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->condition = new Condition(['target' => 'price']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_set_rules()
    {
        $this->condition->setRules(['price <= 20']);

        $this->assertCount(1, $this->condition->getRules());
    }

    /** @test */
    public function it_set_actions()
    {
        $this->condition->setActions([
            [
                'value' => '5',
            ],
            [
                'value' => '5',
            ],
            [
                'value' => '5%',
            ],
        ]);

        $this->assertCount(3, $this->condition->getActions());
    }

    /** @test */
    public function it_can_validate_rules()
    {
        $testCollection1 = $this->createCollection(20);
        $testCollection2 = $this->createCollection(25);
        $testCollection3 = $this->createCollection(1);

        $this->condition->setRules(['price <= 20']);

        $this->condition->setActions(['value' => '5']);

        $this->assertTrue($this->condition->validate($testCollection1));
        $this->assertFalse($this->condition->validate($testCollection2));
        $this->assertTrue($this->condition->validate($testCollection3));
    }

    /** @test */
    public function it_can_apply_actions()
    {
        $testCollection = $this->createCollection(20);

        $this->condition->setRules(['price <= 20']);

        $this->condition->setActions([
            [
                'value' => '5',
            ],
            [
                'value' => '5',
            ],
            [
                'value' => '5%',
            ],
        ]);

        $this->assertSame(31.50, $this->condition->apply($testCollection));
        $this->assertSame(11.5, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_callback_actions()
    {
        $testCollection = $this->createCollection(15);

        $this->condition->setRules(['price <= 20']);

        $this->condition->setActions([
            'value' => function (Collection $collection, $price) {
                return '-'.floor($price / 2);
            },
        ]);

        $this->assertSame(8.0, $this->condition->apply($testCollection));
        $this->assertSame(-7.0, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_percentage_actions()
    {
        $testCollection = $this->createCollection(20);

        $this->condition->setRules(['price >= 20']);

        $this->condition->setActions(['value' => '17.50%']);

        $this->assertSame(23.50, $this->condition->apply($testCollection));
        $this->assertSame(3.5, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_multiple_actions()
    {
        $testCollection = $this->createCollection(20);

        $this->condition->setRules(['price != 10']);

        $this->condition->setActions([
            [
                'value' => '10',
            ],
            [
                'value' => '10%',
            ],
        ]);

        $this->assertSame(33.0, $this->condition->apply($testCollection));
        $this->assertSame(13.0, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_with_a_multiplier()
    {
        $testCollection = $this->createCollection(20, 5);

        $this->condition->setRules(['price <= 20']);

        $this->condition->setActions([
            'multiplier' => 'quantity',
            'value'      => '10',
        ]);

        $this->assertSame(150.0, $this->condition->apply($testCollection));
        $this->assertSame(130.0, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_with_multiple_rules_and_actions()
    {
        $testCollection = $this->createCollection(20, 0, ['name' => 'test']);

        $this->condition->setRules(['name = test', 'price <= 20']);

        $this->condition->setActions([
            [
                'value' => '10',
            ],
            [
                'value' => '10%',
            ],
        ]);

        $this->assertSame(33.0, $this->condition->apply($testCollection));
        $this->assertSame(13.0, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_percentages_and_arithmetic()
    {
        $testCollection = $this->createCollection(25, 5);

        $this->condition->setActions([
            // Add 12.5% Tax
            [
                'multiplier' => 'quantity',
                'value'      => '12.50%',
            ],
            // Add 10% service charge
            [
                'value' => '10%',
            ],
            // Add 5 to the total
            [
                'value' => '5',
            ],
        ]);

        $this->assertSame(159.6875, $this->condition->apply($testCollection));
        $this->assertSame(134.6875, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_subtraction()
    {
        $testCollection = $this->createCollection(25, 5);

        $this->condition->setActions([
            'multiplier' => 'quantity',
            'value'      => '-12.50%',
        ]);

        $this->assertSame(109.375, $this->condition->apply($testCollection));
        $this->assertSame(84.375, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_multiplication()
    {
        $testCollection = $this->createCollection(25);

        $this->condition->setActions(['value' => '*12.50%']);

        $this->assertSame(78.125, $this->condition->apply($testCollection));
        $this->assertSame(53.125, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_division()
    {
        $testCollection = $this->createCollection(24);

        $this->condition->setActions(['value' => '/3']);

        $this->assertSame(8.0, $this->condition->apply($testCollection));
        $this->assertSame(-16.0, $this->condition->result());

        $this->condition->reset();

        // Percentage division
        $this->condition->setActions(['value' => '/50%']);

        $this->assertSame(2.0, $this->condition->apply($testCollection));
        $this->assertSame(-22.0, $this->condition->result());
    }

    /** @test */
    public function it_does_not_apply_if_validation_fails()
    {
        $testCollection = $this->createCollection(20);

        $this->condition->setRules(['name = test', 'price < 20']);

        $this->condition->setActions([
            [
                'value' => '10',
            ],
            [
                'value' => '10%',
            ],
        ]);

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_can_apply_without_rules()
    {
        $testCollection = $this->createCollection(20);

        $this->condition->setActions(['value' => '10']);

        $this->assertSame(30.0, $this->condition->apply($testCollection));
        $this->assertSame(10.0, $this->condition->result());
    }

    /** @test */
    public function it_can_override_the_target_value()
    {
        $testCollection = $this->createCollection(10, 5);

        $this->condition->setActions([
            'multiplier' => 'quantity',
            'value'      => '-12.50%',
        ]);

        $this->assertSame(109.375, $this->condition->apply($testCollection, 25));
        $this->assertSame(84.375, $this->condition->result());
    }

    /** @test */
    public function it_can_handle_max_values()
    {
        $testCollection = $this->createCollection(250);

        // Percentage max
        $this->condition->setActions(['value' => '10%', 'max' => 20]);

        $this->assertSame(270.0, $this->condition->apply($testCollection));
        $this->assertSame(20.0, $this->condition->result());

        $this->condition->reset();

        // Negative max
        $this->condition->setActions(['value' => '-10%', 'max' => -20]);

        $this->assertSame(230.0, $this->condition->apply($testCollection));
        $this->assertSame(-20.0, $this->condition->result());

        $this->condition->reset();

        // Multiplication max
        $this->condition->setActions(['value' => '*25.50%', 'max' => 20]);

        $this->assertSame(270.0, $this->condition->apply($testCollection));
        $this->assertSame(20.0, $this->condition->result());

        $this->condition->reset();

        // Division max
        $this->condition->setActions(['value' => '/2', 'max' => -100]);

        $this->assertSame(150.0, $this->condition->apply($testCollection));
        $this->assertSame(-100.0, $this->condition->result());

        $this->condition->reset();

        // Ignore max if not reached
        $this->condition->setActions(['value' => '10%', 'max' => 200]);

        $this->assertSame(275.0, $this->condition->apply($testCollection));
        $this->assertSame(25.0, $this->condition->result());
    }

    /** @test */
    public function it_can_calculate_inclusive_values()
    {
        $testCollection = $this->createCollection(440);

        // Percentage value
        $this->condition->setActions([
            'value'     => '10%',
            'inclusive' => true,
        ]);

        $this->condition->apply($testCollection);

        $this->assertSame(40.00000000000006, $this->condition->result());

        $this->condition->reset();

        // Absolute value
        $this->condition->setActions([
            'value'     => '-10',
            'inclusive' => true,
        ]);

        $this->condition->apply($testCollection);

        $this->assertSame(-10.0, $this->condition->result());
    }

    /** @test */
    public function it_can_run_a_callback_on_successful_validation()
    {
        $testCollection = $this->createCollection(800);

        $mock = m::mock('callbackMock');

        $mock->shouldReceive('callback')->once();

        // Valid condition
        $this->condition->setActions([
            [
                'value' => '10%',
            ],
            [
                'value' => '15%',
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
                'value' => '10%',
            ],
            [
                'value' => '15%',
            ],
        ], function () use ($mock) {
            $mock->callback();
        });

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_can_run_a_callback_on_insucessful_validation()
    {
        $testCollection = $this->createCollection(800);

        $mock = m::mock('callbackMock');

        $mock->shouldReceive('callback')->once()->with('valid1');
        $mock->shouldReceive('callback')->once()->with('invalid2');

        // Valid condition
        $this->condition->setActions([
            [
                'value' => '10%',
            ],
        ]);

        $this->condition->whenValid(function (Collection $collection, $subtotal) use ($mock) {
            $mock->callback('valid1');

            $this->assertSame(880.0, $subtotal);
        });

        $this->condition->whenInvalid(function (Collection $collection, $subtotal) use ($mock) {
            $mock->callback('invalid1');
        });

        $this->assertSame(880.0, $this->condition->apply($testCollection));
        $this->assertSame(80.0, $this->condition->result());

        $this->condition->reset();

        // Invalid condition
        $this->condition->setRules(['price > 900']);

        $this->condition->setActions([
            [
                'value' => '10%',
            ],
        ]);

        $this->condition->whenValid(function (Collection $collection, $subtotal) use ($mock) {
            $mock->callback('valid2');
        });

        $this->condition->whenInvalid(function (Collection $collection, $subtotal) use ($mock) {
            $mock->callback('invalid2');

            $this->assertSame(800, $subtotal);
        });

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_can_run_a_valid_callback_with_no_actions()
    {
        $testCollection = $this->createCollection(800);

        $mock = m::mock('callbackMock');

        $mock->shouldReceive('callback')->once()->with('valid');

        $this->condition->setRules(['price > 500']);

        $this->condition->whenValid(function (Collection $collection) use ($mock) {
            $mock->callback('valid');
        });

        $this->condition->whenInvalid(function (Collection $collection) use ($mock) {
            $mock->callback('invalid');
        });

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_can_validate_rules_closure()
    {
        $testCollection = $this->createCollection(800, 10);

        // Valid condition
        $promotion = true;

        $this->condition->setRules(function ($collection) use ($promotion) {
            return $collection->get('quantity') > 5 && $promotion;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(880.0, $this->condition->apply($testCollection));
        $this->assertSame(80.0, $this->condition->result());

        $this->condition->reset();

        // Invalid condition
        $this->condition->setRules(function ($collection) {
            return $collection->get('price') * 10 > 10000;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_can_validate_serializable_rules_closure()
    {
        $testCollection = $this->createCollection(800, 10);

        // Valid condition
        $promotion = true;

        $closure = new \Opis\Closure\SerializableClosure(function ($collection) use ($promotion) {
            return $collection->get('quantity') > 5 && $promotion;
        });

        $this->condition->setRules($closure);

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(880.0, $this->condition->apply($testCollection));
        $this->assertSame(80.0, $this->condition->result());

        $this->condition->reset();

        $closure = new \Opis\Closure\SerializableClosure(function ($collection) {
            return $collection->get('price') * 10 > 10000;
        });

        // Invalid condition
        $this->condition->setRules($closure);

        $this->condition->setActions(['value' => '10%']);

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_passes_the_target_value_to_rules_closure()
    {
        $testCollection = $this->createCollection(60, 1);

        $this->condition->setRules(function (Collection $collection, $price) {
            return $price > 50;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(66.0, $this->condition->apply($testCollection));
        $this->assertSame(6.0, $this->condition->result());

        // Invalid condition
        $this->condition->setRules(function (Collection $collection, $price) {
            return $price > 100;
        });

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_will_reset_subtotal_on_failure()
    {
        $testCollection = $this->createCollection(800, 10);

        // Valid condition
        $promotion = true;

        $this->condition->setRules(function ($collection) use ($promotion) {
            return $collection->get('quantity') > 5 && $promotion;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(880.0, $this->condition->apply($testCollection));
        $this->assertSame(80.0, $this->condition->result());

        // Invalid condition
        $this->condition->setRules(function ($collection) {
            return $collection->get('price') * 10 > 10000;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertFalse($this->condition->apply($testCollection));
        $this->assertSame(0.0, $this->condition->result());
    }

    /** @test */
    public function it_will_apply_condition_if_a_collection_is_passed()
    {
        $testCollection = $this->createCollection(800, 10);

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(80.0, $this->condition->result($testCollection));

        // Invalid condition
        $this->condition->setRules(function ($collection) {
            return $collection->get('price') * 10 > 10000;
        });

        $this->condition->setActions(['value' => '10%']);

        $this->assertSame(0.0, $this->condition->result($testCollection));
    }

    /**
     * Creates a collection with the given data.
     *
     * @param float $price
     * @param int   $quantity
     * @param array $additionalData
     *
     * @return \Cartalyst\Collections\Collection
     */
    protected function createCollection($price, $quantity = 0, $additionalData = [])
    {
        $data = ['price' => $price, 'quantity' => $quantity];

        foreach ($additionalData as $key => $value) {
            $data[$key] = $value;
        }

        return new Collection($data);
    }
}
