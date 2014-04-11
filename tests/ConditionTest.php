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

		$this->assertEquals(count($this->condition->getRules()), 1);
	}

	/** @test */
	public function it_set_actions()
	{
		$this->condition->setActions([
			[
				'value' => '5.00',
			],
			[
				'value' => '5.00',
			],
			[
				'value' => '5.00%',
			],
		]);

		$this->assertEquals(count($this->condition->getActions()), 3);
	}

	/** @test */
	public function it_can_apply_actions()
	{
		$this->condition->setRules([
			'price <= 20',
		]);

		$this->condition->setActions([
			[
				'value' => '5.00',
			],
			[
				'value' => '5.00',
			],
			[
				'value' => '5.00%',
			],
		]);

		$testCollection = new Collection([
			'price' => 20.00,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 31.50);

		$this->assertEquals($this->condition->result(), 11.5);
	}

	/** @test */
	public function it_can_apply_percentage_actions()
	{
		$this->condition->setRules([
			'price >= 20.00',
		]);

		$this->condition->setActions([
			'value' => '17.50%',
		]);

		$testCollection = new Collection([
			'price' => 20.00,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 23.50);

		$this->assertEquals($this->condition->result(), 3.5);
	}

	/** @test */
	public function it_can_apply_multiple_actions()
	{
		$this->condition->setRules([
			'price != 10.00',
		]);

		$this->condition->setActions([
			[
				'value' => '10.00',
			],
			[
				'value' => '10.00%',
			],
		]);

		$testCollection = new Collection([
			'price' => 20.00,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 33.00);

		$this->assertEquals($this->condition->result(), 13);
	}

	/** @test */
	public function it_can_apply_with_a_multiplier()
	{
		$this->condition->setRules([
			'price <= 20.00',
		]);

		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '10.00',
		]);

		$testCollection = new Collection([
			'price'    => 20.00,
			'quantity' => 5,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 150.00);

		$this->assertEquals($this->condition->result(), 130);
	}

	/** @test */
	public function it_can_apply_with_multiple_rules_and_actions()
	{
		$this->condition->setRules([
			'name = test',
			'price <= 20.00',
		]);

		$this->condition->setActions([
			[
				'value' => '10.00',
			],
			[
				'value' => '10.00%',
			],
		]);

		$testCollection = new Collection([
			'price' => 20.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 33.00);

		$this->assertEquals($this->condition->result(), 13);
	}

	/** @test */
	public function it_can_apply_percentages_and_arithmetic()
	{
		$this->condition->setActions([
			// Add 12.5% Tax
			[
				'multiplier' => 'quantity',
				'value'      => '12.50%',
			],
			// Add 10% service charge
			[
				'value' => '10.00%',
			],
			// Add 5.00 to the total
			[
				'value' => '5.00',
			],
		]);

		$testCollection = new Collection([
			'price' => 25.00,
			'quantity' => 5,
		]);

		$this->assertEquals($this->condition->apply($testCollection),  159.6875);

		$this->assertEquals($this->condition->result(), 134.6875);
	}

	/** @test */
	public function it_can_apply_subtraction()
	{
		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$testCollection = new Collection([
			'price'    => 25.00,
			'quantity' => 5,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 109.375);

		$this->assertEquals($this->condition->result(), 84.375);
	}

	/** @test */
	public function it_can_apply_multiplication()
	{
		$this->condition->setActions([
			'value' => '*12.50%',
		]);

		$testCollection = new Collection([
			'price' => 25.00,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 78.125);

		$this->assertEquals($this->condition->result(), 53.125);
	}

	/** @test */
	public function it_does_not_apply_if_validation_fails()
	{
		$this->condition->setRules([
			'name = test',
			'price < 20.00',
		]);

		$this->condition->setActions([
			[
				'value' => '10.00',
			],
			[
				'value' => '10.00%',
			],
		]);

		$testCollection = new Collection([
			'name'  => 'test',
			'price' => 20.00,
		]);

		$this->assertFalse($this->condition->apply($testCollection));

		$this->assertEquals($this->condition->result(), 0);
	}

	/** @test */
	public function it_can_apply_without_rules()
	{
		$this->condition->setActions([
			'value' => '10.00',
		]);

		$testCollection = new Collection([
			'price' => 20.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 30.00);

		$this->assertEquals($this->condition->result(), 10);
	}

	/** @test */
	public function it_can_override_the_target_value()
	{
		$this->condition->setActions([
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		]);

		$testCollection = new Collection([
			'price'    => 10.00,
			'quantity' => 5,
		]);

		$this->assertEquals($this->condition->apply($testCollection, 25.00), 109.375);

		$this->assertEquals($this->condition->result(), 84.375);
	}

	/** @test */
	public function it_can_maxout_at_max_value()
	{
		$this->condition->setActions([
			'value' => '10.00%',
			'max'   => 20.00,
		]);

		$testCollection = new Collection([
			'price' => 800.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 820);

		$this->assertEquals($this->condition->result(), 20);
	}

	/** @test */
	public function it_can_apply_multiplication_with_max_value()
	{
		$this->condition->setActions([
			'value' => '*25.50%',
			'max'   => 20.00,
		]);

		$testCollection = new Collection([
			'price' => 25.00,
		]);

		$this->assertEquals($this->condition->apply($testCollection), 45);

		$this->assertEquals($this->condition->result(), 20.00);
	}

	/** @test */
	public function it_can_maxout_at_negative_max_value()
	{
		$this->condition->setActions([
			'value' => '-10.00%',
			'max'   => -20.00,
		]);

		$testCollection = new Collection([
			'price' => 400.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 380);

		$this->assertEquals($this->condition->result(), -20);
	}

	/** @test */
	public function it_ignores_max_value_if_not_reached()
	{
		$this->condition->setActions([
			'value' => '10.00%',
			'max'   => 200.00,
		]);

		$testCollection = new Collection([
			'price' => 800.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 880);

		$this->assertEquals($this->condition->result(), 80);
	}


	/** @test */
	public function it_can_calculate_inclusive_values()
	{
		$this->condition->setActions([
			'value'     => '-10.00',
			'inclusive' => true,
		]);

		$testCollection = new Collection([
			'price' => 440.00,
			'name'  => 'test',
		]);

		// Apply condition
		$this->condition->apply($testCollection);

		// Inclusive 10%
		$this->assertEquals($this->condition->result(), -10);
	}

	/** @test */
	public function it_can_calculate_inclusive_percentage_values()
	{
		$this->condition->setActions([
			'value'     => '10.00%',
			'inclusive' => true,
		]);

		$testCollection = new Collection([
			'price' => 440.00,
			'name'  => 'test',
		]);

		// Apply condition
		$this->condition->apply($testCollection);

		// Inclusive 10%
		$this->assertEquals($this->condition->result(), 40);
	}

	/** @test */
	public function it_can_run_a_callback_on_sucessful_validation()
	{
		$mock = m::mock('callbackMock');

		$mock->shouldReceive('callback')->once();

		$this->condition->setActions([
			[
				'value' => '10.00%',
			],
			[
				'value' => '15.00%',
			]
		], function() use ($mock)
		{
			$mock->callback();
		});

		$testCollection = new Collection([
			'price' => 800.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 1012);

		$this->assertEquals($this->condition->result(), 212);
	}

	/** @test */
	public function it_ignores_callback_on_failed_validation()
	{
		$mock = m::mock('callbackMock');

		$this->condition->setActions([
			[
				'value' => '10.00%',
			],
			[
				'value' => '15.00%',
			]
		], function() use ($mock)
		{
			$mock->callback();
		});

		$this->condition->setRules([
			'price > 900',
		]);

		$testCollection = new Collection([
			'price' => 800.00,
			'name'  => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), false);

		$this->assertEquals($this->condition->result(), 0);
	}

	/** @test */
	public function it_can_validate_rules_closure()
	{
		$this->condition->setActions([
			'value' => '10.00%',
		]);

		$promotion = true;

		$this->condition->setRules(function($collection) use ($promotion)
		{
			return $collection->get('quantity') > 5 && $promotion;
		});

		$testCollection = new Collection([
			'price'    => 800.00,
			'quantity' => 10.00,
			'name'     => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), 880);

		$this->assertEquals($this->condition->result(), 80);
	}

	/** @test */
	public function it_ignores_failed_rules_closure()
	{
		$this->condition->setActions([
			'value' => '10.00%',
		]);

		$this->condition->setRules(function($collection)
		{
			return $collection->get('price') * 10 > 10000;
		});

		$testCollection = new Collection([
			'price'    => 800.00,
			'quantity' => 10.00,
			'name'     => 'test',
		]);

		$this->assertEquals($this->condition->apply($testCollection), false);

		$this->assertEquals($this->condition->result(), 0);
	}

}
