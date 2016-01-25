<?php

namespace LightFsm\Tests\Functional;

use LightFsm\StateMachine;

class PhoneCallTest extends \PHPUnit_Framework_TestCase
{
    const STATE_OFF_HOOK = 'off-hook';
    const STATE_RINGING = 'ringing';
    const STATE_CONNECTED = 'connected';
    const STATE_ON_HOLD = 'on-hold';

    const EVENT_CALL_DIALED = 'call-dialed';
    const EVENT_HUNG_UP = 'hang-up';
    const EVENT_CALL_CONNECTED = 'call-connected';
    const EVENT_PLACE_ON_HOLD = 'place-on-hold';

    /** @var float */
    private $startTime = 0;

    /** @var float */
    private $endTime = 0;

    /** @var StateMachine */
    private $stateMachine;

    /** @var array */
    private $stateLog = [];

    protected function setUp()
    {
        parent::setUp();

        $this->stateMachine = $this->buildPhoneCall(function($newState) {
            $this->stateLog[] = $newState;
        });
    }

    public function test_hung_up()
    {
        $this->assertEquals(self::STATE_OFF_HOOK, $this->stateMachine->getCurrentState());

        $this->fireAndAssertState(self::EVENT_CALL_DIALED, self::STATE_OFF_HOOK, 'invalid');
        $this->fireAndAssertState(self::EVENT_CALL_DIALED, self::STATE_RINGING, 'valid-number');

        $this->fireAndAssertState(self::EVENT_CALL_CONNECTED, self::STATE_CONNECTED);

        $this->fireAndAssertState(self::EVENT_CALL_CONNECTED, self::STATE_CONNECTED);
        usleep(100000); // 0.1 second call duration

        $this->fireAndAssertState(self::EVENT_PLACE_ON_HOLD, self::STATE_ON_HOLD);
        $this->assertTrue($this->stateMachine->isInState(self::STATE_CONNECTED));

        $this->fireAndAssertState(self::EVENT_CALL_CONNECTED, self::STATE_CONNECTED);

        $this->fireAndAssertState(self::EVENT_HUNG_UP, self::STATE_OFF_HOOK);

        $this->assertGreaterThan(0, $this->startTime);
        $this->assertGreaterThan(0, $this->endTime);

        $duration = $this->endTime - $this->startTime;
        $this->assertGreaterThanOrEqual(0.098, $duration);

        $this->assertEquals([
            self::STATE_RINGING,
            self::STATE_CONNECTED,
            self::STATE_ON_HOLD,
            self::STATE_CONNECTED,
            self::STATE_OFF_HOOK
        ], $this->stateLog);
    }

    public function test_dot_graph()
    {
        $actual = str_replace("\r", '', trim($this->stateMachine->toDotGraph()));
        $expected = <<<EOT
digraph {
    "off-hook" -> "ringing" [label="call-dialed [IsNumberValid]"];
    "ringing" -> "off-hook" [label="hang-up"];
    "ringing" -> "connected" [label="call-connected"];
    "connected" -> "off-hook" [label="hang-up"];
    "connected" -> "on-hold" [label="place-on-hold"];
    "on-hold" -> "connected" [label="call-connected"];
    node [shape=box];
    "connected" -> "startTimer" [label="On Entry"];
    "connected" -> "endTimer" [label="On Exit"];
}
EOT;
        $expected = str_replace("\r", '', trim($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $event
     * @param string $expectedState
     * @param mixed  $data
     */
    private function fireAndAssertState($event, $expectedState, $data = null)
    {
        $this->stateMachine->fire($event, $data);
        $this->assertEquals($expectedState, $this->stateMachine->getCurrentState());
    }

    /**
     * @param callable $changeCallback
     *
     * @return StateMachine
     */
    private function buildPhoneCall($changeCallback)
    {
        $phoneCall = new StateMachine(self::STATE_OFF_HOOK, $changeCallback);

        $phoneCall->configure(self::STATE_OFF_HOOK)
            ->permit(self::EVENT_CALL_DIALED, self::STATE_RINGING, [$this, 'isNumberValid'], 'IsNumberValid');

        $phoneCall->configure(self::STATE_RINGING)
            ->permit(self::EVENT_HUNG_UP, self::STATE_OFF_HOOK)
            ->permit(self::EVENT_CALL_CONNECTED, self::STATE_CONNECTED);

        $phoneCall->configure(self::STATE_CONNECTED)
            ->onEntry([$this, 'startTimer'], 'startTimer')
            ->onExit([$this, 'stopTimer'], 'endTimer')
            ->permit(self::EVENT_HUNG_UP, self::STATE_OFF_HOOK)
            ->permit(self::EVENT_PLACE_ON_HOLD, self::STATE_ON_HOLD);

        $phoneCall->configure(self::STATE_ON_HOLD)
            ->subStateOf(self::STATE_CONNECTED)
            ->permit(self::EVENT_CALL_CONNECTED, self::STATE_CONNECTED);

        return $phoneCall;
    }

    public function isNumberValid($data)
    {
        return 'valid-number' === $data;
    }

    /**
     * @param bool $isSubState
     */
    public function startTimer($isSubState)
    {
        if (!$isSubState) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * @param bool $isSubState
     */
    public function stopTimer($isSubState)
    {
        if (!$isSubState) {
            $this->endTime = microtime(true);
        }
    }
}
