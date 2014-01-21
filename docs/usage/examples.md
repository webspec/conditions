# Examples

## Apply 10% Discount if subtotal is larger than 200

	$collection = new Collection(array(
		'name'     => 'Item',
		'subtotal' => 400,
	));

	$condition = new Condition(array(
		'target' => 'subtotal',
	));

	$condition->setRules(array(
		'subtotal > 200',
	));

	$condition->setActions(array(
		'value' => '-10%',
	));

	$condition->apply($collection); // 360

	$condition->result() // -40

## Apply 12.5% Tax without rules

	$collection = new Collection(array(
		'name'     => 'Item',
		'subtotal' => 400,
	));

	$condition = new Condition(array(
		'target' => 'subtotal',
	));

	$condition->setActions(array(
		'value' => '12.5%',
	));

	$condition->apply($collection); // 450

	$condition->result() // 50
