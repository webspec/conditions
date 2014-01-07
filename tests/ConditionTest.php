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

	protected $condition;

	public function __construct()
	{
		$this->condition = new Condition(
			array(
				'target' => 'price',
			)
		);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testConditionCanBeInstantiated()
	{
		$this->condition = new Condition;
	}

	public function testCanSetRules()
	{
		$condition = m::mock('Condition');
		$condition->shouldReceive('setRules')->once();

		$condition->setRules();
	}

	public function testCanSetActions()
	{
		$condition = m::mock('Condition');
		$condition->shouldReceive('setActions')->once();

		$condition->setActions();
	}

	public function testCanApplyCondition()
	{
		$condition = m::mock('Condition');
		$condition->shouldReceive('apply')->once();

		$condition->apply();
	}

	public function testApplyACondition()
	{
		$rules = array(
			'price <= 20',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'value' => '5.00',
			),
			array(
				'value' => '5.00',
			),
			array(
				'value' => '5.00%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00,
		));

		$this->assertEquals($this->condition->apply($testCollection), 31.50);
	}

	public function testCanApplyPercentage()
	{
		$rules = array(
			'price <= 20.00',
		);

		$this->condition->setRules($rules);

		$actions = array(
			'value' => '17.50%',
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00
		));

		$this->assertEquals($this->condition->apply($testCollection), 23.50);
	}

	public function testCanApplyMultiActions()
	{
		$rules = array(
			'price <= 20.00',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00
		));

		$this->assertEquals($this->condition->apply($testCollection), 33.00);
	}

	public function testCanApplyMultiplier()
	{
		$rules = array(
			'price <= 20.00',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'multiplier' => 'quantity',
				'value' => '10.00',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection), 150.00);
	}

	public function testCanApplyMultiRulesMultiActions()
	{
		$rules = array(
			'name = test',
			'price <= 20.00',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00,
			'name' => 'test'
		));

		$this->assertEquals($this->condition->apply($testCollection), 33.00);
	}

	public function testCanApplyMultiPercentagesAndRegularArithmetic()
	{
		$actions = array(
			// Add 12.5% Tax
			array(
				'multiplier' => 'quantity',
				'value' => '12.50%',
			),

			// Add 10% service charge
			array(
				'value' => '10.00%',
			),

			// Add 5.00 to the total
			array(
				'value' => '5.00',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00),  159.6875);
	}

	public function testCanApplySubtractActions()
	{
		$actions = array(
			// 12.5% Discount
			array(
				'multiplier' => 'quantity',
				'value' => '-12.50%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00), 109.375);
	}

	public function testCanApplyMultiplyActions()
	{
		$actions = array(
			// 12.5% Discount
			array(
				'multiplier' => 'quantity',
				'value' => '*12.50%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00), 390.625);
	}

	public function testNoMatchingRules()
	{
		$rules = array(
			'name = test',
			'price >= 20.00',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			'price' => 20.00,
		));

		$this->assertFalse($this->condition->apply($testCollection));
	}

	public function testWithoutRules()
	{
		$actions = array(
			array(
				'value' => '10.00',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(
			array(
				'price' => 20.00,
				'name' => 'test',
			)
		);

		$this->assertEquals($this->condition->apply($testCollection), 30.00);
	}

	public function testDifferentProperties()
	{

		$this->condition = new Condition(
			array(
				'target' => 'weight',
			)
		);

		$rules = array(
			'weight >= 5',
		);

		$this->condition->setRules($rules);

		$actions = array(
			array(
				'target' => 'weight',
				'multiplier' => 'tons',
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		);

		$this->condition->setActions($actions);

		$testCollection = new Collection(array(
			array(
				'weight' => 20.00,
				'tons' => 5.00
			),
			array(
				'weight' => 2.00,
				'tons' => 5.00
			),
		));

		$item1 = $testCollection->first();
		$this->assertEquals($this->condition->apply(new Collection($item1)), 165);

		// Condition results
		$results = $this->condition->apply($testCollection);

		$this->assertEquals($results[0], 165);

		// Not matching
		$this->assertFalse($results[1]);

		// Collection results array
		$res = array(165, false);

		$this->assertEquals($results, $res);

	}

}
