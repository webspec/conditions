# Conditions v1.0.0

A framework agnostic conditions package that allows you to apply different types of conditions on Illuminate Collections.

The package follows the FIG standard PSR-4 to ensure a high level of interoperability between shared PHP code and is fully unit-tested with 100% code coverage.

Part of the Cartalyst Arsenal & licensed [OSI BSD 3](license.txt). Code well, rock on.

## Package Story

History and future capabilities.

### Complete

#### xx-Jan-14 - v1.0.0

- ```Condition::getRules()``` User can return the applied rules.
- ```Condition::setRules($rules|Closure $closure)``` User can set validation rules or closure.
- ```Condition::getActions()``` User can return the applied actions.
- ```Condition::setActions($actions, Closure $callback)``` User can set actions and callback.
- ```Condition::apply(Collection $collection, $target)``` User can apply the conditions to the given collection.
- ```Condition::validate(Collection $collection)``` User can validate the rules against the given collection.
- ```Condition::reset()``` User can reset conditions.

## Requirements

- PHP >=5.4

## Installation

Conditions is installable with Composer. Read further information on how to install.

[Installation Guide](http://cartalyst.com/manual/conditions/introduction/installation)

## Documentation

Refer to the following guide on how to use the Conditions package.

[Documentation](http://cartalyst.com/manual/conditions)

## Versioning

We version under the [Semantic Versioning](http://semver.org/) guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

## Support

Have a bug? Please create an issue here on GitHub that conforms with [necolas's guidelines](https://github.com/necolas/issue-guidelines).

https://github.com/cartalyst/conditions/issues

Follow us on Twitter, [@cartalyst](http://twitter.com/cartalyst).

Join us for a chat on IRC.

Server: irc.freenode.net
Channel: #cartalyst
