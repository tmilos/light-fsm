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

class StateConfiguration
{
    /** @var string|int */
    private $state;

    /**
     * @var TransitionConfiguration[] event => TransitionConfiguration
     */
    private $transitions = [];

    /** @var callable[] */
    private $entryCallbacks = [];

    /** @var callable[] */
    private $exitCallbacks = [];

    /** @var string|int|null */
    private $parentState;

    /**
     * @param string|int $state
     */
    public function __construct($state)
    {
        $this->state = $state;
    }

    /**
     * @return string|int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string|int    $event
     * @param string|int    $nextState
     * @param callable|null $guardCallback f($data)
     * @param string|null   $guardName
     *
     * @return StateConfiguration
     */
    public function permit($event, $nextState, $guardCallback = null, $guardName = null)
    {
        $this->transitions[$event] = new TransitionConfiguration($this->state, $event, $nextState, $guardCallback, $guardName);

        return $this;
    }

    /**
     * @param string|int $parentState
     *
     * @return StateConfiguration
     */
    public function subStateOf($parentState)
    {
        $this->parentState = $parentState;

        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getParentState()
    {
        return $this->parentState;
    }

    /**
     * @param callable    $callback     f($isSubState, $data, $state)
     * @param string|null $listenerName
     *
     * @return StateConfiguration
     */
    public function onEntry($callback, $listenerName = null)
    {
        if ($listenerName) {
            $this->entryCallbacks[$listenerName] = $callback;
        } else {
            $this->entryCallbacks[] = $callback;
        }

        return $this;
    }

    /**
     * @param callable    $callback     f($isSubState, $data, $state)
     * @param string|null $listenerName
     *
     * @return StateConfiguration
     */
    public function onExit($callback, $listenerName = null)
    {
        if ($listenerName) {
            $this->exitCallbacks[$listenerName] = $callback;
        } else {
            $this->exitCallbacks[] = $callback;
        }

        return $this;
    }

    /**
     * @param string|int $event
     *
     * @return TransitionConfiguration|null
     */
    public function getTransition($event)
    {
        return isset($this->transitions[$event]) ? $this->transitions[$event ] : null;
    }

    /**
     * @return TransitionConfiguration[]
     */
    public function getAllTransitions()
    {
        return $this->transitions;
    }

    /**
     * @param bool  $isSubState
     * @param mixed $data
     */
    public function triggerEntry($isSubState, $data)
    {
        foreach ($this->entryCallbacks as $callback) {
            call_user_func($callback, $isSubState, $data, $this->state);
        }
    }

    /**
     * @param bool  $isSubState
     * @param mixed $data
     */
    public function triggerExit($isSubState, $data)
    {
        foreach ($this->exitCallbacks as $callback) {
            call_user_func($callback, $isSubState, $data, $this->state);
        }
    }

    /**
     * @return \callable[]
     */
    public function getAllEntryCallbacks()
    {
        return $this->entryCallbacks;
    }

    /**
     * @return \callable[]
     */
    public function getAllExitCallbacks()
    {
        return $this->exitCallbacks;
    }
}
