# Conditions Change Log

This project follows [Semantic Versioning](CONTRIBUTING.md).

## Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted](https://github.com/cartalyst/conditions/labels/Accepted)
- [Rejected](https://github.com/cartalyst/conditions/labels/Rejected)

---

#### v1.1.3 - 2015-02-13

`UPDATED`

- Updated coding standards to PSR-2.

#### v1.1.2 - 2014-09-23

`REVISED`

- Loosen requirements to allow the usage on Laravel 5.0.

#### v1.1.1 - 2014-09-14

`UPDATED`

- Ability to apply conditions with no actions.
- Minor code tweaks.

#### v1.1.0 - 2014-09-10

`UPDATED`

- Action values can be defined through callbacks.
- Two new methods added `whenValid($closure)` and `whenInvalid($closure)`, the closure will only be executed if the corresponding state is met on appliance.
- Simplified the `calculate()` method.
- Added the ability to use division on action values.
- Improved rules validation.

#### v1.0.2 - 2015-02-13

`UPDATED`

- Updated coding standards to PSR-2.

#### v1.0.1 - 2014-09-23

`REVISED`

- Loosen requirements to allow the usage on Laravel 5.0.

#### v1.0.0 - 2014-05-10

`INIT`

- Can return the applied rules.
- Can set an array of condition rules or a Closure.
- Can return the applied actions.
- Can set actions and callback.
- Can apply the condition on the given collection.
- Can apply the condition on the given collection and returns the difference or returns the difference if the condition is already applied.
- Can validate the rules against a given collection.
- Can reset a condition.
