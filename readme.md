# Conditions v1.0.0

A framework agnostic conditions package that allows you to apply different types of conditions on Illuminate Collections.

Part of the Cartalyst Arsenal & licensed [OSI BSD 3](license.txt). Code well, rock on.

## Package Story

History and future capabilities.

### Complete

#### xx-Jan-14 - v1.0.0

- ```Condition::setRules($rules);``` User can set rules.
- ```Condition::setActions($actions);``` User can set actions
- ```Condition::apply(Collection $collection, $target);``` User can apply the conditions to a collection.
- ```Condition::validate(Collection $collection);``` User can validate a collection against a set of rules.

## Requirements

- PHP >=5.3

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
