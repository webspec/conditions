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
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Support\Collection;

class Condition extends Collection {

	/**
	 * Holds the condition result.
	 *
	 * @var float
	 */
	protected $result;

	/**
	 * Holds the condition subtotal
	 *
	 * @var float
	 */
	protected $subtotal;

	/**
	 * Set the condition rules.
	 *
	 * @param  array  $rules
	 * @return void
	 */
	public function setRules($rules)
	{
		if (is_array($rules))
		{
			foreach ($rules as $rule)
			{
				$this->setRules($rule);
			}

			return;
		}

		$rules = $this->get('rules', array()) + (array) $rules;

		$this->put('rules', $rules);
	}

	/**
	 * Set the condition actions.
	 *
	 * @param  array  $actions
	 * @return void
	 */
	public function setActions($actions)
	{
		if ($this->isMulti($actions))
		{
			foreach ($actions as $action)
			{
				$this->setActions($action);
			}

			return;
		}

		$_actions = $this->get('actions', array());

		$_actions[] = new Collection($actions);

		$this->put('actions', $_actions);
	}

	/**
	 * Apply conditions
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  float  $target
	 * @return float
	 */
	public function apply(Collection $collection, $target = 0)
	{
		// Reset result
		$this->result = 0;

		// Override target if provided
		$target = $target ?: $collection->get($this->get('target'));

		if ($this->isMulti($collection->all()))
		{
			foreach ($collection as $key => $value)
			{
				$results[$key]['total'] = $this->apply(new Collection($value));
				$results[$key]['result'] = $this->result();
			}

			return $results;
		}

		if ($this->validate($collection))
		{
			$this->subtotal = $target;

			foreach ($this->get('actions') as $action)
			{
				// If result was previously set, use that
				$target = $this->result ?: $target;

				// Apply condition
				$result = $this->applyAction($collection, $action, $target);

				// Set result
				$this->result = $result;
			}
		}

		return $this->result ?: false;
	}

	/**
	 * Return net result.
	 *
	 * @return float
	 */
	public function result()
	{
		return $this->result - $this->subtotal;
	}

	/**
	 * Validate a set of rules.
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @return bool
	 */
	public function validate(Collection $collection)
	{
		$valid = true;

		foreach ($this->get('rules', array()) as $rule)
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
	 * Apply an action
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  \Illuminate\Support\Collection  $action
	 * @param  float  $target
	 * @return float
	 */
	protected function applyAction(Collection $collection, Collection $action, $target)
	{
		$operation = $action->get('value');

		$multiplier = $action->get('multiplier') ? $collection->get($action->get('multiplier')) : 1;

		// Match operator
		preg_match('/[+\-\*\/]/', $operation, $operator);
		$operator = implode($operator);

		// Match value
		preg_match('/[0-9\.]+/', $operation, $value);
		$value = implode($value);

		// Match percentage
		preg_match('/[%]/', $operation, $percentage);

		// Apply percentage if specified
		$value = implode($percentage) ? ($target * ($value) / 100) : $value;

		// Calculate values
		return $this->calculate($target, $operator, $value) * $multiplier;
	}

	/**
	 * Validate a single rule
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  string  $rule
	 * @return bool
	 */
	protected function validateRule(Collection $collection, $rule)
	{
		// Match operator
		preg_match('/[=\<\>\!]+/', $rule, $operator);
		$operator = implode($operator) ?: null;

		// Values
		$values = array_map('trim', preg_split('/[=\<\>\!]+/', $rule));

		if ($this->operatorCheck($collection->get($values[0]), $operator, $values[1]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Perform arithmetic calculations.
	 *
	 * @param  string  $operator
	 * @param  string  $target
	 * @param  string  $value
	 * @return float
	 */
	protected function calculate($target, $operator, $value)
	{
		switch ($operator)
		{
			case '+':
			default:  return $target + $value;

			case '*': return $target * $value;

			case '-': return $target - $value;
		}
	}

	/**
	 * Perform a comparison between the target and the value.
	 *
	 * @param  string  $target
	 * @param  string  $operator
	 * @param  string  $value
	 * @return bool
	 */
	protected function operatorCheck($target, $operator, $value)
	{
		switch ($operator)
		{
			case '=':
			default:   return $target == $value;

			case '<':  return $target < $value;

			case '<=': return $target <= $value;

			case '>':  return $target > $value;

			case '>=': return $target >= $value;

			case '!=': return $target != $value;
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

}
