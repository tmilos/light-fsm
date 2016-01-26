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

class StateReference
{
    /** @var string|int */
    private $state;

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
     * @param string|int $state
     *
     * @return StateReference
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
