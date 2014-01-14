# Rules

Rules validate the collection before applying the condition, it returns false if the rule didn't pass.

## Define rules {#define-rules}

	$condition->setRules(array(
		'price < 200',
	));

The condition above will only apply if the price property is smaller than 200.

## Multiple rules {#multiple-rules}

You can define multiple rules to be validated on a collection, condition is only applied if all rules are valid.

	$condition->setRules(array(
		'price > 200',
		'quantity > 10',
	));

The condition above will only apply if both rules are met.

## Comparison Operators {#comparison-operators}

Operator  | Example
--------- | ----------------
=         | 'name = item
!=        | 'name != item1'
<         | 'price < 20'
<=        | 'price <= 10'
>         | 'quantity > 2'
>=        | 'quantity >= 5'
