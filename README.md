# Light FSM

[![License](https://img.shields.io/packagist/l/tmilos/light-fsm.svg)](https://packagist.org/packages/tmilos/light-fsm)
[![Build Status](https://travis-ci.org/tmilos/light-fsm.svg?branch=master)](https://travis-ci.org/tmilos/light-fsm)
[![Coverage Status](https://coveralls.io/repos/github/tmilos/light-fsm/badge.svg?branch=master)](https://coveralls.io/github/tmilos/light-fsm?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tmilos/light-fsm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tmilos/light-fsm/?branch=master)

Finite-state machine FSM PHP library. Create state machines and lightweight state machine-based workflows directly in PHP code.

```php
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

