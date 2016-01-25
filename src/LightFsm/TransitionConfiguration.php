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

class TransitionConfiguration
{
    /** @var string|int */
    private $state;

    /** @var string|int */
    private $event;

    /** @var string|int */
    private $nextState;

    /** @var callable|null */
    private $guardCallback;

    /** @var string|null */
    private $guardName;

    /**
     * @param string|int    $state
     * @param string|int    $event
     * @param string|int    $nextState
     * @param callable|null $guardCallback
     * @param string|null   $guardName
     */
    public function __construct($state, $event, $nextState, $guardCallback, $guardName)
    {
        $this->state = $state;
        $this->event = $event;
        $this->nextState = $nextState;
        $this->guardCallback = $guardCallback;
        $this->guardName = $guardName;
    }

    /**
     * @return int|string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int|string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return int|string
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * @return callable|null
     */
    public function getGuardCallback()
    {
        return $this->guardCallback;
    }

    /**
     * @return null|string
     */
    public function getGuardName()
    {
        return $this->guardName;
    }
}
