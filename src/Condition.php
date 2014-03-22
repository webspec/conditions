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

use Closure;
use Illuminate\Support\Collection;

class Condition extends Collection {

	/**
	 * Holds the condition result.
	 *
	 * @var float
	 */
	protected $result;

	/**
	 * Holds the condition subtotal.
	 *
	 * @var float
	 */
	protected $subtotal;

	/**
	 * Holds the condition callback.
	 *
	 * @var  \Closure
	 */
	protected $callback;

	/**
	 * Returns the condition rules.
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->get('rules');
	}

	/**
	 * Set the condition rules.
	 *
	 * @param  array  $rules
	 * @return void
	 */
	public function setRules($rules)
	{
		$this->put('rules', $rules);
	}

	/**
	 * Returns the condition actions.
	 *
	 * @return array
	 */
	public function getActions()
	{
		return $this->get('actions');
	}

	/**
	 * Set the condition actions.
	 *
	 * @param  array          $actions
	 * @param  \Closure|null  $callback
	 * @return void
	 */
	public function setActions($actions, Closure $callback = null)
	{
		if ($callback)
		{
			$this->callback = $callback;
		}

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

		if ($callback)
		{
			$this->callback = $callback;
		}

		$this->put('actions', $_actions);
	}

	/**
	 * Apply the conditions to the given collection.
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
				$result = $this->apply(new Collection($value));

				array_set($results, "{$key}.total", $result ?: false);

				array_set($results, "{$key}.result", $result ? $this->result() : false);
			}

			if ($this->callback && $result)
			{
				call_user_func($this->callback);
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

		if ($this->callback && $this->result)
		{
			call_user_func($this->callback);
		}

		return $this->result ?: false;
	}

	/**
	 * Returns net result.
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
	 * Apply an action to the given collection.
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

		$max = $action->get('max') ?: 0;

		// Match operator
		preg_match('/[+\-\*\/]/', $operation, $operator);
		$operator = implode($operator);

		// Match value
		preg_match('/[0-9\.]+/', $operation, $value);
		$value = implode($value);

		// Match percentage
		preg_match('/[%]/', $operation, $percentage);

		// Inclusive
		if ($inclusive = $action->get('inclusive'))
		{
			$ratio = 1 + ($value / 100);

			$value = implode($percentage) ? $target - ($target / $ratio) : $value;
		}

		// Apply percentage if specified
		$value = implode($percentage) && ! $inclusive ? ($target * ($value) / 100) : $value;

		// Calculate values
		return $this->calculate($target, $operator, $value, $max) * $multiplier;
	}

	/**
	 * Validate a single rule.
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
	 * Performs arithmetic calculations.
	 *
	 * @param  string  $operator
	 * @param  string  $target
	 * @param  string  $value
	 * @return float
	 */
	protected function calculate($target, $operator, $value, $max = 0)
	{
		switch ($operator)
		{
			case '+':
			default:

				return $max ? $target + $max > $target + $value ? $target + $value : $target + $max : $target + $value;

			case '*':

				return $target * $value;

			case '-':

				return $max ? $target + $max < $target - $value ? $target - $value : $target + $max : $target - $value;
		}
	}

	/**
	 * Performs a comparison between the target and the value.
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
