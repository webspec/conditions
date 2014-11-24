<?php namespace Cartalyst\Conditions\Tests;
/**
 * Part of the Conditions package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Conditions
 * @version    1.3.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
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

		$this->assertEquals(true, $this->condition->validate($testCollection1));
		$this->assertEquals(false, $this->condition->validate($testCollection2));
		$this->assertEquals(true, $this->condition->validate($testCollection3));
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

		$this->assertEquals(31.50, $this->condition->apply($testCollection));
		$this->assertEquals(11.5, $this->condition->result());
	}

	/** @test */
	public function it_can_apply_callback_actions()
	{
		$testCollection = $this->createCollection(15);

		$this->condition->setRules([
			'price <= 20',
		]);

		$this->condition->setActions([
			'value' => function(Collection $collection, $price)
			{
				return '-' . floor($price / 2);
			},
		]);

		$this->assertEquals(8, $this->condition->apply($testCollection));
		$this->assertEquals(-7, $this->condition->result());
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

		$this->assertEquals(23.50, $this->condition->apply($testCollection));
		$this->assertEquals(3.5, $this->condition->result());
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

		$this->assertEquals(33, $this->condition->apply($testCollection));
		$this->assertEquals(13, $this->condition->result());
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

		$this->assertEquals(150, $this->condition->apply($testCollection));
		$this->assertEquals(130, $this->condition->result());
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

		$this->assertEquals(33, $this->condition->apply($testCollection));
		$this->assertEquals(13, $this->condition->result());
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

		$this->assertEquals(159.6875, $this->condition->apply($testCollection));
		$this->assertEquals(134.6875, $this->condition->result());
	}

	/** @test */
	public function it_can_apply_subtraction()
	{
		$testCollection = $this->createCollection(25, 5);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$this->assertEquals(109.375, $this->condition->apply($testCollection));
		$this->assertEquals(84.375, $this->condition->result());
	}

	/** @test */
	public function it_can_apply_multiplication()
	{
		$testCollection = $this->createCollection(25);

		$this->condition->setActions([
			'value' => '*12.50%',
		]);

		$this->assertEquals(78.125, $this->condition->apply($testCollection));
		$this->assertEquals(53.125, $this->condition->result());
	}

	/** @test */
	public function it_can_apply_division()
	{
		$testCollection = $this->createCollection(24);

		$this->condition->setActions([
			'value' => '/3',
		]);

		$this->assertEquals(8, $this->condition->apply($testCollection));
		$this->assertEquals(-16, $this->condition->result());

		$this->condition->reset();

		// Percentage division
		$this->condition->setActions([
			'value' => '/50%',
		]);

		$this->assertEquals(2, $this->condition->apply($testCollection));
		$this->assertEquals(-22, $this->condition->result());
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
		$this->assertEquals(0, $this->condition->result());
	}

	/** @test */
	public function it_can_apply_without_rules()
	{
		$testCollection = $this->createCollection(20);

		$this->condition->setActions([
			'value' => '10',
		]);

		$this->assertEquals(30, $this->condition->apply($testCollection));
		$this->assertEquals(10, $this->condition->result());
	}

	/** @test */
	public function it_can_override_the_target_value()
	{
		$testCollection = $this->createCollection(10, 5);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$this->assertEquals(109.375, $this->condition->apply($testCollection, 25));
		$this->assertEquals(84.375, $this->condition->result());
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

		$this->assertEquals(270, $this->condition->apply($testCollection));
		$this->assertEquals(20, $this->condition->result());

		$this->condition->reset();

		// Negative max
		$this->condition->setActions([
			'value' => '-10%',
			'max'   => -20,
		]);

		$this->assertEquals(230, $this->condition->apply($testCollection));
		$this->assertEquals(-20, $this->condition->result());

		$this->condition->reset();

		// Multiplication max
		$this->condition->setActions([
			'value' => '*25.50%',
			'max'   => 20,
		]);

		$this->assertEquals(270, $this->condition->apply($testCollection));
		$this->assertEquals(20, $this->condition->result());

		$this->condition->reset();

		// Division max
		$this->condition->setActions([
			'value' => '/2',
			'max'   => -100,
		]);

		$this->assertEquals(150, $this->condition->apply($testCollection));
		$this->assertEquals(-100, $this->condition->result());

		$this->condition->reset();

		// Ignore max if not reached
		$this->condition->setActions([
			'value' => '10%',
			'max'   => 200,
		]);

		$this->assertEquals(275, $this->condition->apply($testCollection));
		$this->assertEquals(25, $this->condition->result());
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

		$this->assertEquals(40, $this->condition->result());

		$this->condition->reset();

		// Absolute value
		$this->condition->setActions([
			'value'     => '-10',
			'inclusive' => true,
		]);

		$this->condition->apply($testCollection);

		$this->assertEquals(-10, $this->condition->result());
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
			]
		], function($collection, $subtotal) use ($mock)
		{
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
				'value' => '10%',
			],
			[
				'value' => '15%',
			]
		], function() use ($mock)
		{
			$mock->callback();
		});

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
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

		$this->condition->whenValid(function(Collection $collection, $subtotal) use ($mock)
		{
			$mock->callback('valid1');

			$this->assertEquals(880, $subtotal);
		});

		$this->condition->whenInvalid(function(Collection $collection, $subtotal) use ($mock)
		{
			$mock->callback('invalid1');
		});

		$this->assertEquals(880, $this->condition->apply($testCollection));
		$this->assertEquals(80, $this->condition->result());

		$this->condition->reset();

		// Invalid condition
		$this->condition->setRules([
			'price > 900',
		]);

		$this->condition->setActions([
			[
				'value' => '10%',
			],
		]);

		$this->condition->whenValid(function(Collection $collection, $subtotal) use ($mock)
		{
			$mock->callback('valid2');
		});

		$this->condition->whenInvalid(function(Collection $collection, $subtotal) use ($mock)
		{
			$mock->callback('invalid2');

			$this->assertEquals(800, $subtotal);
		});

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
	}

	/** @test */
	public function it_can_run_a_valid_callback_with_no_actions()
	{
		$testCollection = $this->createCollection(800);

		$mock = m::mock('callbackMock');

		$mock->shouldReceive('callback')->once()->with('valid');

		$this->condition->setRules([
			'price > 500',
		]);

		$this->condition->whenValid(function(Collection $collection) use ($mock)
		{
			$mock->callback('valid');
		});

		$this->condition->whenInvalid(function(Collection $collection) use ($mock)
		{
			$mock->callback('invalid');
		});

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
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

		$this->assertEquals(880, $this->condition->apply($testCollection));
		$this->assertEquals(80, $this->condition->result());

		$this->condition->reset();

		// Invalid condition
		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
	}

	/** @test */
	public function it_can_validate_serializable_rules_closure()
	{
		$testCollection = $this->createCollection(800, 10);

		// Valid condition
		$promotion = true;

		$closure = new \Illuminate\Support\SerializableClosure(function($collection) use($promotion)
		{
			return $collection->get('quantity') > 5 && $promotion;
		});

		$this->condition->setRules($closure);

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(880, $this->condition->apply($testCollection));
		$this->assertEquals(80, $this->condition->result());

		$this->condition->reset();

		$closure = new \Illuminate\Support\SerializableClosure(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		// Invalid condition
		$this->condition->setRules($closure);

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
	}

	/** @test */
	public function it_passes_the_target_value_to_rules_closure()
	{
		$testCollection = $this->createCollection(60, 1);

		$this->condition->setRules(function(Collection $collection, $price)
		{
			return $price > 50;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(66, $this->condition->apply($testCollection));
		$this->assertEquals(6, $this->condition->result());

		// Invalid condition
		$this->condition->setRules(function(Collection $collection, $price)
		{
			return $price > 100;
		});

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
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

		$this->assertEquals(880, $this->condition->apply($testCollection));
		$this->assertEquals(80, $this->condition->result());

		// Invalid condition
		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(false, $this->condition->apply($testCollection));
		$this->assertEquals(0, $this->condition->result());
	}

	/** @test */
	public function it_will_apply_condition_if_a_collection_is_passed()
	{
		$testCollection = $this->createCollection(800, 10);

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(80, $this->condition->result($testCollection));

		// Invalid condition
		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$this->condition->setActions([
			'value' => '10%',
		]);

		$this->assertEquals(0, $this->condition->result($testCollection));
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
