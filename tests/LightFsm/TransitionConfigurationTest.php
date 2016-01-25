<?php

namespace LightFsm\Tests;

use LightFsm\TransitionConfiguration;

class TransitionConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function test_returns_values_constructed_with()
    {
        $transition = new TransitionConfiguration($state = 'state', $event = 'event', $nextState = 'next', $guard = [$this, 'foo'], $guardName = 'name');

        $this->assertEquals($state, $transition->getState());
        $this->assertEquals($event, $transition->getEvent());
        $this->assertEquals($nextState, $transition->getNextState());
        $this->assertEquals($guard, $transition->getGuardCallback());
        $this->assertEquals($guardName, $transition->getGuardName());
    }
}
