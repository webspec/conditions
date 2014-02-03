# Introduction

A framework agnostic conditions package that allows you to apply different types of conditions on Illuminate Collections.

The package follows the FIG standard PSR-4 to ensure a high level of interoperability between shared PHP code and is fully unit-tested with 100% code coverage.

## Getting started

The package requires PHP 5.3+.

Have a [read through the Installation Guide]({url}/introduction/installation).

## Quick Example

### Instantiate our Collection

	$collection = new Illuminate\Support\Collection(array(
		'name'     => 'Example item',
		'subtotal' => 200,
	));

> **Note:** You can apply Conditions to any Illuminate Collection.

### Create a new Condition

	$condition = new Condition(array(
		'name'   => 'VAT (17.5%)',
		'type'   => 'tax',
		'target' => 'subtotal',
	));

### Set the Actions

	$condition->setActions(array(
		'value' => '17.5%',
	));

### Apply the Condition to our Collection

	$condition->apply($collection); // 235

	$condition->result(); // 35
