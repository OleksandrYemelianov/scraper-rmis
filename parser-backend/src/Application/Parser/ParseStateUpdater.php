<?php

namespace App\Application\Parser;

interface ParseStateUpdater
{
    public function getState(int $id): StateContext;
    public function updateState(int $last, int $current, int $all, int $complete): void;
    public function getCurrentState(): StateContext;
}