# Actions

Actions define the behavior the condition will apply on the collection.

## Define actions {#define-actions}

	$condition->setActions(array(
		array(
			'value' => '-10%',
		),
	));

The action above will deduct 10 percent of the target property.

## Accumulative actions {#accumulative-actions}

You can define multiple actions to be applied on one collection, simply pass another array with your desired action.
Defining multiple actions on one condition will accumulate based on the target property.

	$condition->setActions(array(
		array(
			'value' => '17.5%',
		),
		array(
			'value' => '5%',
		),
	));

The example above will add 17.5% first, then add 5% to the result of the previous action.

> **Note** The example above is not equal to passing 22.5%

## Multiplier {#multiplier}

Actions can contain a reference to a multiplier field

	$collection = new Collection(array(
		'name'     => 'An item',
		'price'    => 100.00,
		'quantity' => 5,
	));

	$condition = new Condition(array(
		'target' => 'price',
	));

	$condition->setActions(array(
		'value'      => '20%',
		'multiplier' => 'quantity',
	));

The example above will multiply the condition result by the quantity.
