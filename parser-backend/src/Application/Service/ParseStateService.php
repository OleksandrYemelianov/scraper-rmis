<?php

namespace App\Application\Service;

use App\Application\Parser\ParseStateUpdater;
use App\Application\Parser\ParseStateContext;
use App\Application\Parser\StateContext;
use App\Domain\Model\ParseState;
use App\Domain\Repository\ParseStateRepository;
use App\Domain\Repository\ProductRepository;

class ParseStateService implements ParseStateUpdater
{
    private ?ParseState $currentState = null;
    private ?int $profile_id = null;

    public function __construct(
        private readonly ParseStateRepository $parseStateRepository
    ) {}

    public function getState(int $id): StateContext
    {
        $this->currentState = $this->parseStateRepository->getByProfileId($id);
        return new ParseStateContext($this->currentState);
    }

    public function updateState(int $last, int $current, int $all, int $complete): void
    {
        if (!$this->currentState) {
            throw new \RuntimeException("ParseState not initialized. Call getState first.");
        }
        $this->currentState->updateState($last, $current, $all, $complete);
        $this->parseStateRepository->updateState($this->currentState);
    }

    public function getCurrentState(): StateContext
    {
        if (!$this->currentState) {
            throw new \RuntimeException("ParseState not initialized. Call getState first.");
        }
        return new ParseStateContext($this->currentState);
    }
}
