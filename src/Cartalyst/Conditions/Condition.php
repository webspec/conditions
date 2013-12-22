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

use Illuminate\Support\Collection;

class Condition extends Collection {

	/**
	 * Set the condition rules.
	 *
	 * @param array $rule
	 * @return void
	 */
	public function setRules($rule)
	{
		if (is_array($rule))
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

		$rules[] = $rule;

		$this->put('rules', $rules);
	}

	/**
	 * Set the condition actions.
	 *
	 * @param array $action
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
	 * Apply conditions
	 *
	 * @param \Illuminate\Support\Collection $collection
	 * @return \Illuminate\Support\Collection
	 */
	public function apply(Collection $collection)
	{
		if ($this->isMulti($collection->all()))
		{
			foreach ($collection as $key => $value)
			{
				$collection[$key] = $this->apply(new Collection($value));
			}

			return;
		}

		if ($this->validate($collection))
		{
			foreach ($this->get('actions') as $action)
			{
				$result = $this->applyAction($collection, $action);
				$collection->put($action->get('result'), $result);
			}
		}

		return $collection;
	}

	/**
	 * Apply an action
	 *
	 * @param \Illuminate\Support\Collection $collection
	 * @param \Illuminate\Support\Collection $action
	 * @return float
	 */
	protected function applyAction(Collection $collection, Collection $action)
	{
		$operation = $action->get('value');
		$multiplier = $action->get('multiplier') ? $collection->get($action->get('multiplier')) : 1;
		$target = $collection->get($action->get('target')) * $multiplier;

		// Match operator
		preg_match('/[+\-\*\/]/', $operation, $operator);
		$operator = implode($operator);

		// Match value
		preg_match('/[0-9\.]+/', $operation, $value);
		$value = implode($value);

		// Match percentage
		preg_match('/[%]/', $operation, $percentage);

		// Apply percentage to value
		$value = implode($percentage) ? ($target * ($value) / 100) : $value * $multiplier;

		// Calculate values
		return $this->calculate($target, $operator, $value);
	}

	/**
	 * Validate a set of rules
	 *
	 * @param \Illuminate\Support\Collection $collection
	 * @return bool
	 */
	protected function validate(Collection $collection)
	{
		$rules = $this->get('rules', array());

		$valid = true;

		foreach ($rules as $rule)
		{
			if ( ! $this->validateRule($collection, $rule))
			{
				$valid = false;

				break;
			}
		}

		return $valid;
	}

	/**
	 * Validate a single rule
	 *
	 * @param \Illuminate\Support\Collection $collection
	 * @param string $rule
	 * @return bool
	 */
	protected function validateRule(Collection $collection, $rule)
	{
		// Match operator
		preg_match('/[=\<\>\!]+/', $rule, $operator);
		$operator = !implode($operator) ?: implode($operator);

		// Values
		$values = array_map('trim', preg_split('/[=\<\>\!]+/', $rule));

		if ($this->operatorCheck($collection->get($values[0]), $operator, $values[1]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Perform arithmetic calculations
	 *
	 * @param string $operator
	 * @param string $target
	 * @param string $value
	 * @return float
	 */
	protected function calculate($target, $operator, $value)
	{
		switch ($operator)
		{
			case '+':
			default:

				return $target + $value;

				break;

			case '*':

				return $target * $value;

				break;

			case '-':

				return $target - $value;

				break;
		}
	}

	/**
	 * Operator Check
	 *
	 * @param string $target
	 * @param string $operator
	 * @param string $value
	 * @return bool
	 */
	protected function operatorCheck($target, $operator, $value)
	{
		switch ($operator)
		{
			case '=':
			default:

				return $target == $value;

				break;

			case '<':

				return $target < $value;

				break;

			case '<=':

				return $target <= $value;

				break;

			case '>':

				return $target > $value;

				break;

			case '>=':

				return $target >= $value;

				break;

			case '!=':

				return $target != $value;

				break;
		}
	}

	/**
	 * Checks if the provided array is a multidimensional array.
	 *
	 * @param array $array
	 * @return bool
	 */
	protected function isMulti($array)
	{
		$array = array_shift($array);

		return is_array($array);
	}

}
