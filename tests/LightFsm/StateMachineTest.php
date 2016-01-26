<?php

namespace LightFsm\Tests;

use LightFsm\StateMachine;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_constructed_with_initial_state()
    {
        $stateMachine = new StateMachine($expected = 'a');
        $this->assertEquals($expected, $stateMachine->getCurrentState());
    }

    public function test_can_be_constructed_with_external_state_callbacks()
    {
        $value = 'a';

        $stateMachine = new StateMachine(
            function () use (&$value) { return $value; },
            function ($state) use (&$value) { $value = $state; }
        );
        $stateMachine->configure('a')
            ->permit('to-b', 'b');
        $stateMachine->configure('b')
            ->permit('to-a', 'a');

        $this->assertEquals($value, $stateMachine->getCurrentState());
        $stateMachine->fire('to-b');
        $this->assertEquals($value, $stateMachine->getCurrentState());
        $stateMachine->fire('to-a');
        $this->assertEquals($value, $stateMachine->getCurrentState());
    }

    public function test_can_be_constructed_with_initial_state_callback()
    {
        $stateMachine = new StateMachine(function () {
            return 'a';
        });
        $this->assertEquals('a', $stateMachine->getCurrentState());
    }

    public function test_can_be_constructed_with_initial_state_and_change_listener()
    {
        $value = null;
        $stateMachine = new StateMachine('a', function ($state) use (&$value) {
            $value = $state;
        });
        $stateMachine->configure('a')
            ->permit('to-b', 'b')
            ->permit('to-a', 'a');
        $stateMachine->configure('b')
            ->permit('to-a', 'a');

        $this->assertEquals('a', $value);
        $stateMachine->fire('to-b');
        $this->assertEquals('b', $value);
        $stateMachine->fire('to-a');
        $this->assertEquals('a', $value);
        $stateMachine->fire('to-a');
        $this->assertEquals('a', $value);
    }
}
