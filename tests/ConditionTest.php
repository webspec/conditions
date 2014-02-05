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
		$this->condition = new Condition(array(
			'target' => 'price',
		));
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


	public function testConditionCanBeInstantiated()
	{
		$this->condition = new Condition;
	}


	public function testApplyACondition()
	{
		$this->condition->setRules(array(
			'price <= 20',
		));

		$this->condition->setActions(array(
			array(
				'value' => '5.00',
			),
			array(
				'value' => '5.00',
			),
			array(
				'value' => '5.00%',
			),
		));

		$testCollection = new Collection(array(
			'price' => 20.00,
		));

		$this->assertEquals($this->condition->apply($testCollection), 31.50);

		$this->assertEquals($this->condition->result(), 11.5);
	}


	public function testCanApplyPercentage()
	{
		$this->condition->setRules(array(
			'price <= 20.00',
		));

		$this->condition->setActions(array(
			'value' => '17.50%',
		));

		$testCollection = new Collection(array(
			'price' => 20.00,
		));

		$this->assertEquals($this->condition->apply($testCollection), 23.50);

		$this->assertEquals($this->condition->result(), 3.5);
	}


	public function testCanApplyMultiActions()
	{
		$this->condition->setRules(array(
			'price <= 20.00',
		));

		$this->condition->setActions(array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		));

		$testCollection = new Collection(array(
			'price' => 20.00,
		));

		$this->assertEquals($this->condition->apply($testCollection), 33.00);

		$this->assertEquals($this->condition->result(), 13);
	}


	public function testCanApplyMultiplier()
	{
		$this->condition->setRules(array(
			'price <= 20.00',
		));

		$this->condition->setActions(array(
			'multiplier' => 'quantity',
			'value'      => '10.00',
		));

		$testCollection = new Collection(array(
			'price'    => 20.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection), 150.00);

		$this->assertEquals($this->condition->result(), 130);
	}


	public function testCanApplyMultiRulesMultiActions()
	{
		$this->condition->setRules(array(
			'name = test',
			'price <= 20.00',
		));

		$this->condition->setActions(array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		));

		$testCollection = new Collection(array(
			'price' => 20.00,
			'name'  => 'test',
		));

		$this->assertEquals($this->condition->apply($testCollection), 33.00);

		$this->assertEquals($this->condition->result(), 13);
	}


	public function testCanApplyMultiPercentagesAndRegularArithmetic()
	{
		$this->condition->setActions(array(
			// Add 12.5% Tax
			array(
				'multiplier' => 'quantity',
				'value'      => '12.50%',
			),

			// Add 10% service charge
			array(
				'value' => '10.00%',
			),

			// Add 5.00 to the total
			array(
				'value' => '5.00',
			),
		));

		$testCollection = new Collection(array(
			'price'    => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00),  159.6875);

		$this->assertEquals($this->condition->result(), 134.6875);
	}


	public function testCanApplySubtractActions()
	{
		$this->condition->setActions(array(
			'multiplier' => 'quantity',
			'value'      => '-12.50%',
		));

		$testCollection = new Collection(array(
			'price'    => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00), 109.375);

		$this->assertEquals($this->condition->result(), 84.375);
	}


	public function testCanApplyMultiplyActions()
	{
		$this->condition->setActions(array(
			'multiplier' => 'quantity',
			'value'      => '*12.50%',
		));

		$testCollection = new Collection(array(
			'price'    => 25.00,
			'quantity' => 5,
		));

		$this->assertEquals($this->condition->apply($testCollection, 25.00), 390.625);

		$this->assertEquals($this->condition->result(), 365.625);
	}


	public function testNoMatchingRules()
	{
		$this->condition->setRules(array(
			'name = test',
			'price < 20.00',
		));

		$this->condition->setActions(array(
			array(
				'value' => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		));

		$testCollection = new Collection(array(
			'name'  => 'test',
			'price' => 20.00,
		));

		$this->assertFalse($this->condition->apply($testCollection));

		$this->assertEquals($this->condition->result(), 0);
	}


	public function testWithoutRules()
	{
		$this->condition->setActions(array(
			'value' => '10.00',
		));

		$testCollection = new Collection(array(
			'price' => 20.00,
			'name'  => 'test',
		));

		$this->assertEquals($this->condition->apply($testCollection), 30.00);

		$this->assertEquals($this->condition->result(), 10);
	}


	public function testDifferentProperties()
	{
		$this->condition = new Condition(array(
			'target' => 'weight',
		));

		$this->condition->setRules(array(
			'weight >= 5',
			'tons != 4',
		));

		$this->condition->setActions(array(
			array(
				'target'     => 'weight',
				'multiplier' => 'tons',
				'value'      => '10.00',
			),
			array(
				'value' => '10.00%',
			),
		));

		$testCollection = new Collection(array(
			array(
				'weight' => 20.00,
				'tons'   => 5.00,
			),
			array(
				'weight' => 2.00,
				'tons'   => 5.00,
			),
		));

		$item1 = $testCollection->first();

		$this->assertEquals($this->condition->apply(new Collection($item1)), 165);

		$this->assertEquals($this->condition->result(), 145);

		// Condition results
		$results = $this->condition->apply($testCollection);

		$this->assertEquals($results[0]['total'], 165);

		$this->assertEquals($results[0]['result'], 145);

		// Not matching
		$this->assertFalse($results[1]['total']);

		$this->assertEquals($results, array(
			array(
				'total'  => 165,
				'result' => 145,
			),
			array(
				'total'  => false,
				'result' => false,
			),
		));
	}

}
