# Changelog

### v4.0.1 - 2020-04-12

`UPDATED`

- Dependency to use `opis/closure` instead of `jeremeamia/superclosure` which is now abandoned

### v4.0.0 - 2020-03-03

- Updated for Laravel 7.

### v3.0.0 - 2019-09-04

- BC Break: PHP 7.2 is the minimum required PHP version
- BC Break: Laravel 6.0 is the minimum supported Laravel version

### v2.0.0 - 2015-03-15

`ADDED`

- Added support for Callback classes to be used instead of only closures when defining actions.

`UPDATED`

- Updated Super Closure dependency to 2.0.
- Conditions now extends a Cartalyst Collection.

`REMOVED`

- Removed `illuminate/support` dependency.
- Removed usage of Illuminate Collections.

### v1.2.1 - 2015-02-13

`UPDATED`

- Updated coding standards to PSR-2.

### v1.2.0 - 2014-10-27

`ADDED`

- Ability to pass the subtotal into the validation callbacks.

### v1.1.3 - 2015-02-13

`UPDATED`

- Updated coding standards to PSR-2.

### v1.1.2 - 2014-09-23

`REVISED`

- Loosen requirements to allow the usage on Laravel 5.0.

### v1.1.1 - 2014-09-14

`UPDATED`

- Ability to apply conditions with no actions.
- Minor code tweaks.

### v1.1.0 - 2014-09-10

`UPDATED`

- Action values can be defined through callbacks.
- Two new methods added `whenValid($closure)` and `whenInvalid($closure)`, the closure will only be executed if the corresponding state is met on appliance.
- Simplified the `calculate()` method.
- Added the ability to use division on action values.
- Improved rules validation.

### v1.0.2 - 2015-02-13

`UPDATED`

- Updated coding standards to PSR-2.

### v1.0.1 - 2014-09-23

`REVISED`

- Loosen requirements to allow the usage on Laravel 5.0.

### v1.0.0 - 2014-05-10

`INIT`

- Can return the applied rules.
- Can set an array of condition rules or a Closure.
- Can return the applied actions.
- Can set actions and callback.
- Can apply the condition on the given collection.
- Can apply the condition on the given collection and returns the difference or returns the difference if the condition is already applied.
- Can validate the rules against a given collection.
- Can reset a condition.
