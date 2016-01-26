<?php

namespace LightFsm\Tests;

use LightFsm\StateConfiguration;
use LightFsm\TransitionConfiguration;

class StateConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function test_constructs_with_state()
    {
        new StateConfiguration('state');
    }

    public function test_returns_state_constructed_with()
    {
        $config = new StateConfiguration($state = 'state');
        $this->assertEquals($state, $config->getState());
    }

    public function test_permit_creates_new_transition_with_all_arguments()
    {
        $config = new StateConfiguration($state = 'state');
        $config->permit($event = 'event', $nextState = 'next', $guard = ['class', 'foo'], $guardName = 'name');

        $arr = $config->getAllTransitions();
        $this->assertCount(1, $arr);
        /** @var TransitionConfiguration $transition */
        $transition = reset($arr);

        $this->assertEquals($state, $transition->getState());
        $this->assertEquals($event, $transition->getEvent());
        $this->assertEquals($nextState, $transition->getNextState());
        $this->assertEquals($guard, $transition->getGuardCallback());
        $this->assertEquals($guardName, $transition->getGuardName());
    }

    public function test_permit_creates_new_transition_just_with_event_and_next_state()
    {
        $config = new StateConfiguration($state = 'state');
        $config->permit($event = 'event', $nextState = 'next');

        $arr = $config->getAllTransitions();
        $this->assertCount(1, $arr);
        /** @var TransitionConfiguration $transition */
        $transition = reset($arr);

        $this->assertEquals($state, $transition->getState());
        $this->assertEquals($event, $transition->getEvent());
        $this->assertEquals($nextState, $transition->getNextState());
        $this->assertNull($transition->getGuardCallback());
        $this->assertNull($transition->getGuardName());
    }

    public function test_permit_returns_this_instance()
    {
        $config = new StateConfiguration($state = 'state');
        $result = $config->permit('event', 'next');
        $this->assertSame($config, $result);
    }

    public function test_parent_state()
    {
        $config = new StateConfiguration('state');
        $this->assertNull($config->getParentState());
        $config->subStateOf($expected = 'parent');
        $this->assertEquals($expected, $config->getParentState());
    }

    public function test_on_entry_records_callback_with_name()
    {
        $config = new StateConfiguration('state');
        $config->onEntry($callback = ['class', 'foo'], $name = 'name');

        $arr = $config->getAllEntryCallbacks();
        $this->assertCount(1, $arr);
        $this->assertEquals([$name=>$callback], $arr);
    }

    public function test_on_entry_records_callback_without_name()
    {
        $config = new StateConfiguration('state');
        $config->onEntry($callback = ['class', 'foo']);

        $arr = $config->getAllEntryCallbacks();
        $this->assertCount(1, $arr);
        $this->assertEquals([0=>$callback], $arr);
    }

    public function test_on_exit_records_callback_with_name()
    {
        $config = new StateConfiguration('state');
        $config->onExit($callback = ['class', 'foo'], $name = 'name');

        $arr = $config->getAllExitCallbacks();
        $this->assertCount(1, $arr);
        $this->assertEquals([$name=>$callback], $arr);
    }

    public function test_on_exit_records_callback_without_name()
    {
        $config = new StateConfiguration('state');
        $config->onExit($callback = ['class', 'foo']);

        $arr = $config->getAllExitCallbacks();
        $this->assertCount(1, $arr);
        $this->assertEquals([0=>$callback], $arr);
    }

    public function test_get_transition()
    {
        $config = new StateConfiguration($state = 'state');
        $config->permit(1, '11');
        $config->permit(2, '22');

        $transition = $config->getTransition(2, null);
        $this->assertNotNull($transition);
        $this->assertEquals($state, $transition->getState());
        $this->assertEquals(2, $transition->getEvent());
        $this->assertEquals('22', $transition->getNextState());
    }

    public function test_trigger_entry_calls_all_entry_callbacks()
    {
        $countFirst = $countSecond = 0;
        $expectedData = 'data';

        $config = new StateConfiguration($initialState = 'state');

        $config->onEntry(function($isSubState, $data, $state) use (&$countFirst, $initialState, $expectedData) {
            $this->assertFalse($isSubState);
            $this->assertEquals($expectedData, $data);
            $this->assertEquals($initialState, $state);

            $countFirst++;
        });
        $config->onEntry(function($isSubState, $data, $state) use (&$countSecond, $initialState, $expectedData) {
            $this->assertFalse($isSubState);
            $this->assertEquals($expectedData, $data);
            $this->assertEquals($initialState, $state);

            $countSecond++;
        });

        $config->triggerEntry(false, $expectedData);

        $this->assertEquals(1, $countFirst);
        $this->assertEquals(1, $countSecond);
    }

    public function test_trigger_exit_calls_all_exit_callbacks()
    {
        $countFirst = $countSecond = 0;
        $expectedData = 'data';

        $config = new StateConfiguration($initialState = 'state');

        $config->onExit(function($isSubState, $data, $state) use (&$countFirst, $initialState, $expectedData) {
            $this->assertFalse($isSubState);
            $this->assertEquals($expectedData, $data);
            $this->assertEquals($initialState, $state);

            $countFirst++;
        });
        $config->onExit(function($isSubState, $data, $state) use (&$countSecond, $initialState, $expectedData) {
            $this->assertFalse($isSubState);
            $this->assertEquals($expectedData, $data);
            $this->assertEquals($initialState, $state);

            $countSecond++;
        });

        $config->triggerExit(false, $expectedData);

        $this->assertEquals(1, $countFirst);
        $this->assertEquals(1, $countSecond);
    }
}
