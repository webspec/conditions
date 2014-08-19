<?php namespace Cartalyst\Conditions;
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
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\SerializableClosure;

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
	 * @var \Closure|\Illuminate\Support\SerializableClosure
	 */
	protected $callback;

	/**
	 * Holds the validation callback.
	 *
	 * @var \Closure|\Illuminate\Support\SerializableClosure
	 */
	protected $validationClosure;

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
	 * Sets the condition rules.
	 *
	 * @param  mixed  $rules
	 * @return void
	 */
	public function setRules($rules)
	{
		if ($rules instanceof Closure || $rules instanceof SerializableClosure)
		{
			$this->validationClosure = $rules;

			return;
		}

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
	 * Sets the condition actions.
	 *
	 * @param  array  $actions
	 * @param  \Closure|\Illuminate\Support\SerializableClosure  $callback
	 * @return void
	 */
	public function setActions($actions, $callback = null)
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

		$_actions = $this->get('actions', []);

		$_actions[] = new Collection($actions);

		$this->put('actions', $_actions);
	}

	/**
	 * Apply the conditions to the given collection.
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  float  $target
	 * @return float|false
	 */
	public function apply(Collection $collection, $target = 0)
	{
		$this->result = 0;

		$this->subtotal = 0;

		$target = $target ?: $collection->get($this->get('target'));

		if ($this->validate($collection, $this->validationClosure))
		{
			$this->subtotal = $target;

			foreach ($this->get('actions') as $action)
			{
				$target = $this->result ?: $target;

				$this->result = $this->applyAction($collection, $action, $target);
			}

			if ($this->callback instanceof Closure || $this->callback instanceof SerializableClosure)
			{
				call_user_func($this->callback);
			}
		}

		return $this->result ?: false;
	}

	/**
	 * Returns the total condition value.
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @return float
	 */
	public function result(Collection $collection = null)
	{
		if ($collection)
		{
			$this->apply($collection);
		}

		return $this->result - $this->subtotal;
	}

	/**
	 * Validate a set of rules.
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  \Closure|\Illuminate\Support\SerializableClosure  $callback
	 * @return bool
	 */
	public function validate(Collection $collection, $callback = null)
	{
		if ($callback instanceof Closure || $callback instanceof SerializableClosure)
		{
			return call_user_func($callback, $collection);
		}

		$rules = $this->get('rules', []);

		foreach ($rules as $rule)
		{
			if ( ! $this->validateRule($collection, $rule))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Resets the condition.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->result = 0;

		$this->subtotal = 0;

		$this->callback = null;

		$this->validationClosure = null;

		$this->put('rules', []);

		$this->put('actions', []);
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
		$max = $action->get('max') ?: 0;

		$operation = $action->get('value');

		$multiplier = $action->get('multiplier') ? $collection->get($action->get('multiplier')) : 1;

		list($operator, $percentage, $value) = $this->parseAction($operation, $collection);

		if ($inclusive = $action->get('inclusive'))
		{
			$ratio = 1 + ($value / 100);

			$value = $percentage ? $target - ($target / $ratio) : $value;
		}

		$value = ($percentage && ! $inclusive) ? ($target * ($value) / 100) : $value;

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
		preg_match('/[=\<\>\!]+/', $rule, $operator);

		$operator = head($operator) ?: null;

		$values = array_map('trim', preg_split('/[=\<\>\!]+/', $rule));

		return $this->operatorCheck($collection->get($values[0]), $operator, $values[1]) ?: false;
	}

	/**
	 * Performs arithmetic calculations.
	 *
	 * @param  string  $target
	 * @param  string  $operator
	 * @param  string  $value
	 * @param  int  $max
	 * @return float
	 */
	protected function calculate($target, $operator, $value, $max = 0)
	{
		switch ($operator)
		{
			default:
			case '+':

				if ($max)
				{
					return ($target + $max) > ($target + $value) ? ($target + $value) : ($target + $max);
				}

				return $target + $value;

			case '*':

				if ($max)
				{
					return ($target + $max) > ($target * $value) ? ($target * $value) : ($target + $max);
				}

				return $target * $value;

			case '-':

				if ($max)
				{
					return ($target + $max) < ($target - $value) ? ($target - $value) : ($target + $max);
				}

				return $target - $value;
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
			default:
			case '=':  return $target == $value;

			case '<=': return $target <= $value;

			case '>=': return $target >= $value;

			case '<':  return $target < $value;

			case '>':  return $target > $value;

			case '!=': return $target != $value;
		}
	}

	/**
	 * Parse action.
	 *
	 * @param  string|\Closure|\Illuminate\Support\SerializableClosure  $value
	 * @param  \Illuminate\Support\Collection  $collection
	 * @return array
	 */
	protected function parseAction($value, Collection $collection)
	{
		if ($value instanceof Closure || $value instanceof SerializableClosure)
		{
			$value = call_user_func($value, $collection);
		}

		$percentage = strpos($value, '%');

		preg_match('/[+\-\*\/]/', $value, $operator);

		preg_match('/[0-9\.]+/', $value, $values);

		return [
			head($operator),
			$percentage,
			head($values),
		];
	}

	/**
	 * Checks if the given array is a multidimensional array.
	 *
	 * @param  array  $array
	 * @return bool
	 */
	protected function isMulti($array)
	{
		return is_array(array_shift($array));
	}

}
