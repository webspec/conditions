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
 * @version    1.3.0
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
	 * @var mixed
	 */
	protected $validCallback;

	/**
	 * Holds the invalid condition callback.
	 *
	 * @var mixed
	 */
	protected $invalidCallback;

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
		if ( ! is_array($rules))
		{
			$rules = [$rules];
		}

		$this->put('rules', $rules);

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
	 * @param  mixed  $callback
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
	 * Sets the valid condition callback.
	 *
	 * @param  mixed  $callback
	 * @return $this
	 */
	public function whenValid($callback)
	{
		if ($this->isCallback($callback))
		{
			$this->validCallback = $callback;
		}

		return $this;
	}

	/**
	 * Sets the invalid condition callback.
	 *
	 * @param  mixed  $callback
	 * @return $this
	 */
	public function whenInvalid($callback)
	{
		if ($this->isCallback($callback))
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
		$this->subtotal = $target ?: $collection->get($this->get('target'));

		$this->result = $this->subtotal;

		$callback = $this->invalidCallback;

		if ($this->validate($collection, $this->subtotal))
		{
			foreach ($this->get('actions', []) as $action)
			{
				$this->result = $this->applyAction($collection, $action, $this->result);
			}

			$callback = $this->validCallback;
		}

		if ($this->isCallback($callback))
		{
			$callback($collection, $this->result);
		}

		return $this->result === $this->subtotal ? false : $this->result;
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
	 * @param  float  $target
	 * @return bool
	 */
	public function validate(Collection $collection, $target = null)
	{
		$target = $target ?: $collection->get($this->get('target'));

		foreach ($this->get('rules', []) as $rule)
		{
			if ( ! $this->validateRule($collection, $rule, $target))
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

		list($operator, $percentage, $value) = $this->parseAction($operation, $collection, $target);

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
	 * @param  mixed  $rule
	 * @param  float  $target
	 * @return bool
	 */
	protected function validateRule(Collection $collection, $rule, $target)
	{
		if ($this->isCallback($rule))
		{
			return $rule($collection, $target);
		}

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
	 * @param  mixed  $value
	 * @param  \Illuminate\Support\Collection  $collection
	 * @param  float  $target
	 * @return array
	 */
	protected function parseAction($value, Collection $collection, $target)
	{
		if ($this->isCallback($value))
		{
			$value = $value($collection, $target);
		}

		preg_match('/[+\-\*\/]/', $value, $operator);

		preg_match('/[0-9\.]+/', $value, $values);

		return [ head($operator), strpos($value, '%'), head($values) ];
	}

	/**
	 * Checks if the given object is a closure.
	 *
	 * @param  mixed  $object
	 * @return bool
	 */
	protected function isCallback($object)
	{
		$isClosure = $object instanceof Closure || $object instanceof SerializableClosure;

		return $isClosure || $object instanceof Callback;
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
