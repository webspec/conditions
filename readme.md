#Condition v1.0.0-alpha

. Part of the Cartalyst Arsenal & licensed [OSI BSD 3](license.md). Code well, rock on.

##Package Story

History and future capabilities.

####Incomplete
- None at the moment.

####Complete
- *27-Dez-13* - v1.0.0
	- ```Condition::setRules($rules);``` Can set rules.
	- ```Condition::setActions($actions);``` Can set actions
	- ```Condition::apply(Collection $collection, $target);``` Can apply the conditions to a collection.
	- ```Condition::validate(Collection $collection);``` Can validate a collection against a set of rules.


Versioning
----------

We version under the [Semantic Versioning](http://semver.org/) guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

Support
--------

Have a bug? Please create an issue here on GitHub that conforms with [necolas's guidelines](https://github.com/necolas/issue-guidelines).

https://github.com/cartalyst/condition/issues

Follow us on Twitter, [@cartalyst](http://twitter.com/cartalyst).

Join us for a chat on IRC.

Server: irc.freenode.net
Channel: #cartalyst
