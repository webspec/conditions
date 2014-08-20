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
 * @version    1.1.0
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
	 * Holds the valid condition callback.
	 *
	 * @var \Closure|\Illuminate\Support\SerializableClosure
	 */
	protected $validCallback;

	/**
	 * Holds the invalid condition callback.
	 *
	 * @var \Closure|\Illuminate\Support\SerializableClosure
	 */
	protected $invalidCallback;

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
	 * @return $this
	 */
	public function setRules($rules)
	{
		if ($this->isClosure($rules))
		{
			$this->validationClosure = $rules;
		}
		else
		{
			$this->put('rules', $rules);
		}

		return $this;
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
	 * @return $this
	 */
	public function setActions($actions, $callback = null)
	{
		$this->whenValid($callback);

		if ($this->isMulti($actions))
		{
			foreach ($actions as $action)
			{
				$this->setActions($action);
			}
		}
		else
		{
			$_actions = $this->get('actions', []);

			$_actions[] = new Collection($actions);

			$this->put('actions', $_actions);
		}

		return $this;
	}

	/**
	 * Sets the condition valid callback.
	 *
	 * @param \Closure|\Illuminate\Support\SerializableClosure  $callback
	 * @return $this
	 */
	public function whenValid($callback)
	{
		if ($this->isClosure($callback))
		{
			$this->validCallback = $callback;
		}

		return $this;
	}

	/**
	 * Sets the condition invalid callback.
	 *
	 * @param \Closure|\Illuminate\Support\SerializableClosure  $callback
	 * @return $this
	 */
	public function whenInvalid($callback)
	{
		if ($this->isClosure($callback))
		{
			$this->invalidCallback = $callback;
		}

		return $this;
	}

	/**
	 * Applies the conditions to the given collection.
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

			if ($this->isClosure($this->validCallback))
			{
				call_user_func($this->validCallback);
			}
		}
		else
		{
			if ($this->isClosure($this->invalidCallback))
			{
				call_user_func($this->invalidCallback);
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
	 * Validates a set of rules against the collection.
	 *
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  \Closure|\Illuminate\Support\SerializableClosure  $callback
	 * @return bool
	 */
	public function validate(Collection $collection, $callback = null)
	{
		if ($this->isClosure($callback))
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

		$this->validCallback = null;

		$this->invalidCallback = null;

		$this->validationClosure = null;

		$this->put('rules', []);

		$this->put('actions', []);
	}

	/**
	 * Applies an action to the given collection.
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
	 * Validates a single rule against the collection.
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
			case '+': return $max ? min($target + $max, $target + $value) : $target + $value;

			case '*': return $max ? min($target + $max, $target * $value) : $target * $value;

			case '-': return $max ? max($target + $max, $target - $value) : $target - $value;

			case '/': return $max ? max($target + $max, $target / $value) : $target / $value;
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
	 * Parses the action.
	 *
	 * @param  string|\Closure|\Illuminate\Support\SerializableClosure  $value
	 * @param  \Illuminate\Support\Collection  $collection
	 * @return array
	 */
	protected function parseAction($value, Collection $collection)
	{
		if ($this->isClosure($value))
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
	 * Checks if the given object is either a closure.
	 *
	 * @param  mixed  $object
	 * @return boolean
	 */
	protected function isClosure($object)
	{
		return $object instanceof Closure || $object instanceof SerializableClosure;
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
