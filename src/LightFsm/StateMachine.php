<?php

/*
 * This file is part of the light-fsm package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightFsm;

class StateMachine
{
    /** @var StateConfiguration */
    private $currentState;

    /** @var StateConfiguration[] */
    private $states = [];

    /** @var callable|null */
    private $changeCallback;

    /**
     * @param string|int|callable $initialState
     * @param callable|null       $changeCallback f($newState, $event, $oldState, $isSubState, $data)
     */
    public function __construct($initialState, $changeCallback = null)
    {
        $state = is_callable($initialState) ? call_user_func($initialState) : $initialState;
        $this->states[$state] = $this->currentState = new StateConfiguration($state);
        $this->changeCallback = $changeCallback;
    }

    /**
     * @param string|int $state
     *
     * @return StateConfiguration
     */
    public function configure($state)
    {
        return $this->getState($state);
    }

    /**
     * @param string|int $event
     * @param mixed      $data
     */
    public function fire($event, $data = null)
    {
        $transition = $this->currentState->getTransition($event);
        if (null === $transition) {
            return;
        }

        if ($transition->getGuardCallback()) {
            $ok = call_user_func($transition->getGuardCallback(), $data);
            if (!$ok) {
                return;
            }
        }

        $nextState = $this->getState($transition->getNextState());

        $this->transition($this->currentState, $event, $nextState, $data);
    }

    /**
     * @return int|string
     */
    public function getCurrentState()
    {
        return $this->currentState->getState();
    }

    /**
     * @param string|int $state
     *
     * @return bool
     */
    public function isInState($state)
    {
        $parentState = $this->getState($state);

        return $this->isSubState($this->currentState, $parentState);
    }

    public function toDotGraph()
    {
        $result = "digraph {\n";
        $listeners = '';
        foreach ($this->states as $state) {
            foreach ($state->getAllEntryCallbacks() as $name => $callback) {
                if (is_int($name)) {
                    $name = 'listener';
                }
                $listeners .= sprintf("    \"%s\" -> \"%s\" [label=\"On Entry\"];\n", $state->getState(), $name);
            }
            foreach ($state->getAllExitCallbacks() as $name => $callback) {
                if (is_int($name)) {
                    $name = 'listener';
                }
                $listeners .= sprintf("    \"%s\" -> \"%s\" [label=\"On Exit\"];\n", $state->getState(), $name);
            }

            foreach ($state->getAllTransitions() as $transition) {
                if ($transition->getGuardCallback()) {
                    $guardName = $transition->getGuardName() ?: 'condition';
                    $result .= sprintf("    \"%s\" -> \"%s\" [label=\"%s [%s]\"];\n", $state->getState(), $transition->getNextState(), $transition->getEvent(), $guardName);
                } else {
                    $result .= sprintf("    \"%s\" -> \"%s\" [label=\"%s\"];\n", $state->getState(), $transition->getNextState(), $transition->getEvent());
                }
            }
        }

        if ($listeners) {
            $result .= "    node [shape=box];\n";
            $result .= $listeners;
        }

        $result .= "}\n";

        return $result;
    }

    /**
     * @param StateConfiguration $child
     * @param StateConfiguration $parent
     *
     * @return bool
     */
    private function isSubState(StateConfiguration $child, StateConfiguration $parent)
    {
        $state = $child;

        while ($state) {
            if ($state->getState() === $parent->getState()) {
                return true;
            }

            if ($state->getParentState()) {
                $state = $this->getState($state->getParentState());
            } else {
                $state = null;
            }
        }

        return false;
    }

    /**
     * @param string|int $state
     *
     * @return StateConfiguration
     */
    private function getState($state)
    {
        if (false === isset($this->states[$state])) {
            $this->states[$state] = new StateConfiguration($state);
        }

        return $this->states[$state];
    }

    /**
     * @param StateConfiguration $previousState
     * @param string|int         $event
     * @param StateConfiguration $nextState
     * @param mixed              $data
     */
    private function transition(StateConfiguration $previousState, $event, StateConfiguration $nextState, $data)
    {
        $isSubState = $this->isSubState($this->currentState, $nextState);

        $previousState->triggerExit($isSubState, $data);
        $this->currentState = $nextState;
        $nextState->triggerEntry($isSubState, $data);

        if ($this->changeCallback) {
            call_user_func($this->changeCallback, $nextState->getState(), $event, $previousState->getState(), $isSubState, $data);
        }
    }
}
