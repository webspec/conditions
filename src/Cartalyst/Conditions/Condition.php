<?php namespace Cartalyst\Conditions;
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
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Cart\Cart;

use Illuminate\Support\Collection;

class Condition extends Collection {

	/**
	 * Set the condition rules
	 *
	 * @param  array  $rule
	 * @return void
	 */
	public function setRules($rule)
	{
		if ($this->isMulti($rule))
		{
			foreach ($rule as $r)
			{
				$this->setRules($r);
			}

			return;
		}

		$rules = array();

		foreach ($this->get('rules', array()) as $r)
		{
			$rules[] = $r;
		}

		$rules[] = new Collection($rule);

		$this->put('rules', $rules);
	}

	/**
	 * Set the condition actions.
	 *
	 * @param  array  $action
	 * @return void
	 */
	public function setActions($action)
	{
		if ($this->isMulti($action))
		{
			foreach ($action as $a)
			{
				$this->setActions($a);
			}

			return;
		}

		$actions = array();

		foreach ($this->get('actions', array()) as $a)
		{
			$actions[] = $a;
		}

		$actions[] = new Collection($action);

		$this->put('actions', $actions);
	}

	/**
	 *
	 *
	 *
	 */
	public function validate(Cart $cart)
	{
		$rules = $this->get('rules', array());

		$actions = $this->get('actions', array());

		$valid = true;

		foreach ($rules as $rule)
		{
			if ( ! $this->validateRule($cart, $rule))
			{
				$valid = false;

				break;
			}
		}

		if ($valid)
		{
			$this->put('valid', true);

			return true;
		}
	}

	/**
	 * Checks if the provided array is a multidimensional array.
	 *
	 * @param  array  $array
	 * @return bool
	 */
	protected function isMulti($array)
	{
		$array = array_shift($array);

		return is_array($array);
	}

	## need to rethink the foreach
	protected $item = null;
	protected function validateRule(Cart $cart, $rule)
	{
		// Get the target of the rule
		$target = $rule->get('target');

		// Check if the target is an item
		if (strpos($target, 'item-') !== false)
		{
			// Prepare the target
			$target = str_replace('item-', '', $target);

			// Prepare the operator
			$operator = $rule->get('operator', '=');

			// Check if we have the item information
			if (is_null($this->item))
			{
				$this->item = $cart->find(array(
					$target => $rule->get('value'),
				));
			}

			// Loop through the items we did found
			foreach ($this->item as $i)
			{
				if ($this->operatorCheck($i->{$target}, $operator, $rule->get('value')))
				{
					return true;
				}
			}
		}

		return false;
	}

	protected function operatorCheck($value, $operator, $check)
	{
		switch ($operator)
		{
			case '=':
			default:
				return $value == $check;

				break;

			case '<=':

				return $value <= $check;

				break;

			case '>=':
				return $value >= $check;

				break;

			case '!=':

				return $value != $check;

				break;
		}
	}

}
