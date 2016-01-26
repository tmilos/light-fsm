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

class EventConfiguration
{
    /** @var string|int */
    private $event;

    /** @var TransitionConfiguration[] */
    private $transitions = [];

    /**
     * @param string|int $event
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * @return int|string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string|int    $state
     * @param string|int    $event
     * @param string|int    $nextState
     * @param callable|null $guardCallback f($data)
     * @param string|null   $guardName
     */
    public function addTransition($state, $event, $nextState, $guardCallback = null, $guardName = null)
    {
        $this->transitions[] = new TransitionConfiguration($state, $event, $nextState, $guardCallback, $guardName);
    }

    /**
     * @param mixed $data
     *
     * @return TransitionConfiguration|null
     */
    public function getTransition($data)
    {
        foreach ($this->transitions as $transition) {
            $ok = true;
            if ($transition->getGuardCallback()) {
                $ok = call_user_func($transition->getGuardCallback(), $data);
            }
            if ($ok) {
                return $transition;
            }
        }

        return null;
    }

    /**
     * @return TransitionConfiguration[]
     */
    public function getAllTransitions()
    {
        return $this->transitions;
    }
}
