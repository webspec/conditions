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

class Condition {

	/**
	 * Holds all the condition rules.
	 *
	 * @var array
	 */
	protected $rules = array();

	/**
	 * Holds all the condition actions.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Return the condition rules.
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Set the condition rules.
	 *
	 * @param  array  $rules
	 * @return void.
	 */
	public function setRules(array $rules)
	{
		if ($this->isMulti($rules))
		{
			foreach ($rules as $rule)
			{
				$this->setRules($rule);
			}

			return;
		}

		$this->rules[] = $rules;
	}

	/**
	 * Return the condition rules.
	 *
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * Set the condition actions.
	 *
	 * @param  array  $actions
	 * @return void.
	 */
	public function setActions(array $actions)
	{
		if ($this->isMulti($actions))
		{
			foreach ($actions as $action)
			{
				$this->setActions($action);
			}

			return;
		}
		$this->actions[] = $actions;
	}


	public function validate(Cart $cart)
	{
		$rules = $this->getRules();

		$actions = $this->getActions();

			if ($this->validateConditionRules($cart, $rules))
			{
				echo 'valido';
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
	protected function validateConditionRules(Cart $cart, $rules)
	{
		$item = null;

		$find = false;

		foreach ($rules as $rule)
		{
			$target = $rule['target'];

			if (strpos($target, 'item-') !== false)
			{
				$target = str_replace('item-', '', $target);

				if (is_null($item))
				{
					$find = $item = $cart->find(array(
						$target => $rule['value'],
					));
				}
				else
				{
					foreach ($item as $i)
					{
						$find = $this->operatorCheck($i->{$target}, $rule['operator'], $rule['value']);
					}
				}



				# this is because, the condition failed to be asserted
				if ( ! $find)
				{
					return false;
				}
			}
		}

		return true;
	}

	protected function operatorCheck($value, $operator, $check)
	{
		$find = false;

		switch ($operator)
		{
			case '=':
			default:
				$find = $value == $check;

				break;

			case '<=':

				$find = $value <= $check;

				break;

			case '>=':
				$find = $value >= $check;

				break;

			case '!=':

				$find = $value != $check;

				break;
		}

		return $find;
	}

/*
	## this method will return the value with the discount applied
	public function discount()
	{
		$value = $this->getValue();

		$discount = $this->discount;

		if ($this->type === 'percent')
		{
		    #$value = ($value * ((100-$discount) / 100));
		    $value = $value - ($value * $discount);
		}
		else
		{
		    $value = $value - $discount;
		}

		return $value;
	}

*/

}
