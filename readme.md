# Conditions

A framework agnostic conditions package that allows you to apply conditions on Illuminate Collections.

The package follows the FIG standard PSR-4 to ensure a high level of interoperability between shared PHP code and is fully unit-tested.

Part of the Cartalyst Arsenal & licensed [Cartalyst PSL](license.txt). Code well, rock on.

## Package Story

Package history and capabilities.

#### 23-Sep-14 - v1.0.1

- Loosen requirements to allow the usage on Laravel 5.0.

#### 10-May-14 - v1.0.0

- ```Condition::getRules()``` Returns the applied rules.
- ```Condition::setRules($rules)``` Sets an array of condition rules or a Closure.
- ```Condition::getActions()``` Returns the applied actions.
- ```Condition::setActions($actions, $callback|null)``` Sets actions and callback.
- ```Condition::apply(Collection $collection, $target)``` Applies the condition on the given collection.
- ```Condition::result(Collection $collection|null)``` Applies the condition on the given collection and returns the difference or returns the difference if the condition is already applied.
- ```Condition::validate(Collection $collection, $callback|null)``` Validates the rules against the given collection.
- ```Condition::reset()``` Resets the condition.

## Installation

Conditions is installable with Composer. Read further information on how to install.

[Installation Guide](https://cartalyst.com/manual/conditions/1.0#installation)

## Documentation

Refer to the following guide on how to use the Conditions package.

[Documentation](https://cartalyst.com/manual/conditions/1.0)

## Versioning

We version under the [Semantic Versioning](http://semver.org/) guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

## Contributing

Please read the [Contributing](contributing.md) guidelines.

## Support

Have a bug? Please create an [issue](https://github.com/cartalyst/conditions/issues) here on GitHub that conforms with [necolas's guidelines](https://github.com/necolas/issue-guidelines).

Follow us on Twitter, [@cartalyst](http://twitter.com/cartalyst).

Join us for a chat on IRC.

Server: irc.freenode.net
Channel: #cartalyst

Email: help@cartalyst.com
