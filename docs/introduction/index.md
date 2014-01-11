# Introduction

A framework agnostic conditions package that allows you to apply different types of conditions on Illuminate Collections.

The package follows the FIG standard PSR-0 to ensure a high level of
interoperability between shared PHP code and is fully unit-tested.

## Getting started

The package requires at least PHP version 5.3.

Have a [read through the Installation Guide]({url}/introduction/installation).

## Quick Example

	// 17.5% tax condition
	$condition = new Condition(array(
		'name' => 'VAT (17.5%)',
		'type' => 'tax',
		'target' => 'subtotal',
	));

	$condition->setActions(array(
		array('value' => '17.5%')
	));

	$collection = new Collection(
		'name' => 'an item',
		'subtotal' => 200,
	);

	$condition->apply($collection); // 235

	$condition->result(); // 35

> **Note:** You can apply conditions on any Illuminate Collection
