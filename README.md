# Light FSM

[![License](https://img.shields.io/packagist/l/tmilos/light-fsm.svg)](https://packagist.org/packages/tmilos/light-fsm)
[![Build Status](https://travis-ci.org/tmilos/light-fsm.svg?branch=master)](https://travis-ci.org/tmilos/light-fsm)
[![Coverage Status](https://coveralls.io/repos/github/tmilos/light-fsm/badge.svg?branch=master)](https://coveralls.io/github/tmilos/light-fsm?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tmilos/light-fsm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tmilos/light-fsm/?branch=master)

Finite-state machine FSM PHP library. Create state machines and lightweight state machine-based workflows directly in PHP code.

```php
<?php
$phoneCall = new StateMachine(State::OFF_HOOK);

$phoneCall->configure(State::OFF_HOOK)
    ->permit(Event::CALL_DIALED, State::RINGING);

$phoneCall->configure(State::RINGING)
    ->permit(Event::HUNG_UP, State::OFF_HOOK)
    ->permit(Event::CALL_CONNECTED, State::CONNECTED);

$phoneCall->configure(State::CONNECTED)
    ->onEntry([$this, 'startTimer'])
    ->onExit([$this, 'stopTimer'])
    ->permit(Event::HUNG_UP, State::OFF_HOOK)
    ->permit(Event::PLACE_ON_HOLD, State::ON_HOLD);

$phoneCall->fire(Event::CALL_DIALED);
$this->assertEquals(State::RINGING, $phoneCall->getCurrentState());
```

This project, as well as the example above, was inspired by [stateless](https://github.com/dotnet-state-machine/stateless).


## Features

 * State and trigger events of type string or int
 * Firing trigger events with additional data
 * Hierarchical states
 * Entry/exit events for states
 * Introspection
 * Guard callbacks to support conditional transitions
 * Ability to store state externally (for example, in a property tracked by an ORM)
 * Export to DOT graph


## Firing trigger events with additional data

Event can be fired with additional data ``StateMachine::fire($event, $data)`` that will be passed and available to entry/exit and guard
listeners, so they can base their logic based on it.


## Hierarchical States

In the example below, the ``ON_HOLD`` state is a substate of the ``CONNECTED`` state. This means that an ``ON_HOLD`` call is still connected.

```php
<?php
$phoneCall->configure(State::ON_HOLD)
    ->subStateOf(State::CONNECTED)
    ->permit(Event::CALL_CONNECTED, State::CONNECTED);
```

In addition to the ``StateMachine::getCurrentState()`` method, which will report the precise current state, an ``isInState($state)``
method is also provided. ``isInState($state)`` will take substates into account, so that if the example above was in the
``ON_HOLD`` state, ``isInState(State::CONNECTED)`` would also evaluate to ``true``.


## Entry/Exit Events

In the example, the ``startTimer()`` method will be executed when a call is connected. The ``stopTimer()`` will be executed when
call completes.

When call moves between the ``CONNECTED`` and ``ON_HOLD`` states, since the ``ON_HOLD`` state is a substate of the ``CONNECTED`` state,
these listeners can distinguish substates and note that call is still connected based on the first ``$isSubState`` argument.


## External State Storage

In order to listen for state changes for persistence purposes, for example with some ORM tool, pass the listener callback
to the ``StateMachine`` constructor.

```php
<?php
$stateMachine = new StateMachine(
    function() use ($stateObject) {
        return $stateObject->getValue();
    },
    function($state) use ($stateObject) {
        $stateObject->setValue($state);
        $orm->persist($stateObject);
    }
);
```

## Introspection

The state machine can provide a list of the trigger events than can be successfully fired within the current state by
the ``StateMachine::getPermittedTriggers()`` method.


## Guard Clauses

The state machine will choose between multiple transitions based on guard clauses, e.g.:

```php
<?php
$phoneCall->configure(State::OFF_HOOK)
    .permit(Trigger::CALL_DIALLED, State::RINGING, function($data) { return IsValidNumber($data); })
    .permit(Trigger::CALL_DIALLED, State::BEEPING, function($data) { return !IsValidNumber($data); });
```

## Export to DOT graph

It can be useful to visualize state machines on runtime. With this approach the code is the authoritative source and state diagrams
are by-products which are always up to date.

```php
<?php
$phoneCall->configure(State::OFF_HOOK)
    .permit(Trigger::CALL_DIALED, State::RINGING, 'IsValidNumber');
string graph = phoneCall.toDotGraph();
```

The ``StateMachine.toDotGraph()`` method returns a string representation of the state machine in the
[DOT graph language](https://en.wikipedia.org/wiki/DOT_(graph_description_language)), e.g.:

```
digraph {
 "off-hook" -> "ringing" [label="call-dialed [IsValidNumber]"];
}
```

This can then be rendered by tools that support the DOT graph language, such as the
[dot command line tool](http://www.graphviz.org/doc/info/command.html) from [graphviz.org](http://www.graphviz.org/) or
[viz.js](https://github.com/mdaines/viz.js). See (http://www.webgraphviz.com) for instant gratification. Command line example
to generate a PDF file:

```bash
> dot -T pdf -o phoneCall.pdf phoneCall.dot
```

