# Installation

The best and easy way to install the Converter package is with [Composer](http://getcomposer.org).

## Preparation

Open your `composer.json` and add the following to the `require` array:

	"cartalyst/conditions": "1.0.*"

Add the following lines after the `require` array on your `composer.json` file:

	"repositories": [
		{
			"type": "composer",
			"url": "http://packages.cartalyst.com"
		}
	]

> **Note:** Make sure your `composer.json` file is in a valid JSON format after the required changes.

## Install the dependencies

Run Composer to install or update the new requirement.

	php composer install

or

	php composer update

Now you are able to require the `vendor/autoload.php` file to autoload the package.

## Example

	// Include the composer autoload file
	require_once 'vendor/autoload.php';

	// Import the necessary classes
	use Cartalyst\Conditions\Condition;
	use Illuminate\Support\Collection;

	// Instantiate a new Condition
	$condition = new Condition([
		'target' => 'price',
	]);

	$condition->setActions([
		'value' => '-10.00',
	]);

	$collection = new Collection([
		'price' => 200,
	]);

	// Condition total
	$total = $condition->apply($collection); // 190

	// Condition net total
	$netTotal = $condition->result(); // -10
