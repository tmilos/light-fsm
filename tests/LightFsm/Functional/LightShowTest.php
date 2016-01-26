<?php

namespace LightFsm\Tests\Functional;

use LightFsm\StateMachine;

class LightShowTest extends \PHPUnit_Framework_TestCase
{
    const STATE_OFF = 'off';
    const STATE_ON = 'on';
    const STATE_RED = 'red';
    const STATE_GREEN = 'green';
    const STATE_BLUE = 'blue';

    const EVENT_TURN_ON = 'turn-on';
    const EVENT_TURN_OFF = 'turn-off';
    const EVENT_TICK = 'tick';

    /** @var StateMachine */
    private $stateMachine;

    protected function setUp()
    {
        $this->stateMachine = new StateMachine(self::STATE_OFF);

        $this->stateMachine->configure(self::STATE_OFF)
            ->permit(self::EVENT_TURN_ON, self::STATE_RED);

        $this->stateMachine->configure(self::STATE_ON)
            ->permit(self::EVENT_TURN_OFF, self::STATE_OFF);

        $this->stateMachine->configure(self::STATE_RED)
            ->subStateOf(self::STATE_ON)
            ->permit(self::EVENT_TICK, self::STATE_GREEN);

        $this->stateMachine->configure(self::STATE_GREEN)
            ->subStateOf(self::STATE_ON)
            ->permit(self::EVENT_TICK, self::STATE_BLUE);

        $this->stateMachine->configure(self::STATE_BLUE)
            ->subStateOf(self::STATE_ON)
            ->permit(self::EVENT_TICK, self::STATE_RED);
    }

    public function test_light_show()
    {
        $this->assertEquals(self::STATE_OFF, $this->stateMachine->getCurrentState());

        $this->fireAndAssertState(self::EVENT_TURN_OFF, self::STATE_OFF);
        $this->fireAndAssertState(self::EVENT_TURN_ON, self::STATE_RED);
        $this->fireAndAssertState(self::EVENT_TICK, self::STATE_GREEN);
        $this->fireAndAssertState(self::EVENT_TURN_ON, self::STATE_GREEN);
        $this->fireAndAssertState(self::EVENT_TICK, self::STATE_BLUE);
        $this->fireAndAssertState(self::EVENT_TICK, self::STATE_RED);
        $this->fireAndAssertState(self::EVENT_TICK, self::STATE_GREEN);
        $this->fireAndAssertState(self::EVENT_TURN_OFF, self::STATE_OFF);
    }

    /**
     * @param string $event
     * @param string $expectedState
     */
    private function fireAndAssertState($event, $expectedState)
    {
        $this->stateMachine->fire($event);
        $this->assertEquals($expectedState, $this->stateMachine->getCurrentState());
    }
}
