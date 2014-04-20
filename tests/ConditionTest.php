<?php namespace Cartalyst\Conditions\Tests;
/**
 * Part of the Conditions package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Conditions
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Conditions\Condition;
use Illuminate\Support\Collection;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class ConditionTest extends PHPUnit_Framework_TestCase {

	/**
	 * Holds the conditions instance.
	 *
	 * @var \Cartalyst\Conditions\Condition
	 */
	protected $condition;

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
	public function it_can_be_instantiated()
	{
		$this->condition = new Condition;
	}

	/** @test */
	public function it_can_set_rules()
	{
		$this->condition->setRules([
			'price <= 20',
		]);

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

		$this->condition->setRules([
			'price <= 20',
		]);

		$this->condition->setActions([
			'value' => '5',
		]);

		$this->assertEquals($this->condition->validate($testCollection1), true);
		$this->assertEquals($this->condition->validate($testCollection2), false);
		$this->assertEquals($this->condition->validate($testCollection3), true);
	}

	/** @test */
	public function it_can_apply_actions()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setRules([
			'price <= 20',
		]);

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

		$this->assertEquals($this->condition->apply($testCollection), 31.50);
		$this->assertEquals($this->condition->result(), 11.5);
	}

	/** @test */
	public function it_can_apply_percentage_actions()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setRules([
			'price >= 20',
		]);

		$this->condition->setActions([
			'value' => '17.50%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 23.50);
		$this->assertEquals($this->condition->result(), 3.5);
	}

	/** @test */
	public function it_can_apply_multiple_actions()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setRules([
			'price != 10',
		]);

		$this->condition->setActions([
			[
				'value' => '10',
			],
			[
				'value' => '10%',
			],
		]);

		$this->assertEquals($this->condition->apply($testCollection), 33);
		$this->assertEquals($this->condition->result(), 13);
	}

	/** @test */
	public function it_can_apply_with_a_multiplier()
	{
		$testCollection = $this->createCollection(20, 5);

		$this->condition->setRules([
			'price <= 20',
		]);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '10',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 150);
		$this->assertEquals($this->condition->result(), 130);
	}

	/** @test */
	public function it_can_apply_with_multiple_rules_and_actions()
	{
		$testCollection = $this->createCollection(20, 0, ['name' => 'test']);

		$this->condition->setRules([
			'name = test',
			'price <= 20',
		]);

		$this->condition->setActions([
			[
				'value' => '10',
			],
			[
				'value' => '10%',
			],
		]);

		$this->assertEquals($this->condition->apply($testCollection), 33);
		$this->assertEquals($this->condition->result(), 13);
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

		$this->assertEquals($this->condition->apply($testCollection),  159.6875);
		$this->assertEquals($this->condition->result(), 134.6875);
	}

	/** @test */
	public function it_can_apply_subtraction()
	{
		$testCollection = $this->createCollection(25, 5);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 109.375);
		$this->assertEquals($this->condition->result(), 84.375);
	}

	/** @test */
	public function it_can_apply_multiplication()
	{
		$testCollection = $this->createCollection(25);

		$this->condition->setActions([
			'value' => '*12.50%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 78.125);
		$this->assertEquals($this->condition->result(), 53.125);
	}

	/** @test */
	public function it_does_not_apply_if_validation_fails()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setRules([
			'name = test',
			'price < 20',
		]);

		$this->condition->setActions([
			[
				'value' => '10',
			],
			[
				'value' => '10%',
			],
		]);

		$this->assertFalse($this->condition->apply($testCollection));
		$this->assertEquals($this->condition->result(), 0);
	}

	/** @test */
	public function it_can_apply_without_rules()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setActions([
			'value' => '10',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 30);
		$this->assertEquals($this->condition->result(), 10);
	}

	/** @test */
	public function it_can_override_the_target_value()
	{
		$testCollection = $this->createCollection(10, 5);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$this->assertEquals($this->condition->apply($testCollection, 25), 109.375);
		$this->assertEquals($this->condition->result(), 84.375);
	}

	/** @test */
	public function it_can_handle_max_values()
	{
		$testCollection = $this->createCollection(250);

		// Percentage max
		$this->condition->setActions([
			'value' => '10%',
			'max'   => 20,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 270);
		$this->assertEquals($this->condition->result(), 20);

		$this->condition->reset();

		// Negative max
		$this->condition->setActions([
			'value' => '-10%',
			'max'   => -20,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 230);
		$this->assertEquals($this->condition->result(), -20);

		$this->condition->reset();

		// Multiplication max
		$this->condition->setActions([
			'value' => '*25.50%',
			'max'   => 20,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 270);
		$this->assertEquals($this->condition->result(), 20);

		$this->condition->reset();

		// Ignore max if not reached
		$this->condition->setActions([
			'value' => '10%',
			'max'   => 200,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 275);
		$this->assertEquals($this->condition->result(), 25);
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

		$this->assertEquals($this->condition->result(), 40);

		$this->condition->reset();

		// Absolute value
		$this->condition->setActions([
			'value'     => '-10',
			'inclusive' => true,
		]);

		$this->condition->apply($testCollection);

		$this->assertEquals($this->condition->result(), -10);
	}

	/** @test */
	public function it_can_run_a_callback_on_sucessful_validation()
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
			]
		], function() use ($mock)
		{
			$mock->callback();
		});

		$this->assertEquals($this->condition->apply($testCollection), 1012);
		$this->assertEquals($this->condition->result(), 212);

		$this->condition->reset();

		// Invalid condition
		$this->condition->setRules([
			'price > 900',
		]);

		$this->condition->setActions([
			[
				'value' => '10%',
			],
			[
				'value' => '15%',
			]
		], function() use ($mock)
		{
			$mock->callback();
		});

		$this->assertEquals($this->condition->apply($testCollection), false);
		$this->assertEquals($this->condition->result(), 0);
	}

	/** @test */
	public function it_can_validate_rules_closure()
	{
		$testCollection = $this->createCollection(800, 10);

		// Valid condition
		$promotion = true;

		$this->condition->setRules(function($collection) use ($promotion)
		{
			return $collection->get('quantity') > 5 && $promotion;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 880);
		$this->assertEquals($this->condition->result(), 80);

		$this->condition->reset();

		// Invalid condition
		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), false);
		$this->assertEquals($this->condition->result(), 0);
	}

	/** @test */
	public function it_will_reset_subtotal_on_failure()
	{
		$testCollection = $this->createCollection(800, 10);

		// Valid condition
		$promotion = true;

		$this->condition->setRules(function($collection) use ($promotion)
		{
			return $collection->get('quantity') > 5 && $promotion;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 880);
		$this->assertEquals($this->condition->result(), 80);

		// Invalid condition
		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals($this->condition->apply($testCollection), false);
		$this->assertEquals($this->condition->result(), 0);
	}

	/**
	 * Creates a collection with the given data.
	 *
	 * @param  float  $price
	 * @param  int  $quantity
	 * @param  array  $additionalData
	 * @return \Illuminate\Support\Collection
	 */
	protected function createCollection($price, $quantity = 0, $additionalData = [])
	{
		$data = [
			'price'    => $price,
			'quantity' => $quantity,
		];

		foreach ($additionalData as $key => $value)
		{
			$data[$key] = $value;
		}

		return new Collection($data);
	}

}
