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
	 * The condition name.
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * The condition value, in case we don't need to provide
	 * condition actions.
	 *
	 * @var  mixed
	 */
	protected $value = null;

	/**
	 * The condition type.
	 *
	 * @var string
	 */
	protected $type = null;

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
	 * Return the condition name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the condition name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Return the condition value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set the condition value.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Return the condition type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set the condition type.
	 *
	 * @param  string  $type
	 * @return void
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

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

		$found = false;

		foreach ($rules as $rule)
		{
			$target = $rule['target'];

			// Check if the target is an item
			if (strpos($target, 'item-') !== false)
			{
				$target = str_replace('item-', '', $target);

				$operator = ! empty($rule['operator']) ? $rule['operator'] : '=';

				// Check if we have the item information
				if (is_null($item))
				{
					$found = $item = $cart->find(array(
						$target => $rule['value'],
					));
				}

				// Loop through the items we did found
				foreach ($item as $i)
				{
					$found = $this->operatorCheck($i->{$target}, $operator, $rule['value']);
				}

				# this is because, the condition failed to be asserted
				if ( ! $found)
				{
					return false;
				}
			}
		}

		return true;
	}

	protected function operatorCheck($value, $operator, $check)
	{
		$found = false;

		switch ($operator)
		{
			case '=':
			default:
				$found = $value == $check;

				break;

			case '<=':

				$found = $value <= $check;

				break;

			case '>=':
				$found = $value >= $check;

				break;

			case '!=':

				$found = $value != $check;

				break;
		}

		return $found;
	}


	public function __get($arg)
	{
		$method = 'get'.ucwords($arg);

		if (method_exists($this, $method))
		{
			return $this->{$method}();
		}
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
