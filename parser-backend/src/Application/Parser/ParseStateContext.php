<?php

namespace App\Application\Parser;

use App\Domain\Model\ParseState;

class ParseStateContext implements StateContext
{
    public function __construct(private ParseState $parseState)
    {}

    public function getData(): array
    {
        return $this->parseState->getContext();
    }
}
