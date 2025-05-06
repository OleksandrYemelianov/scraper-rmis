<?php

namespace App\Application\Parser;

use App\Domain\Model\Profile;

class ProfileParsingContext implements ParsingContext
{
    public function __construct(private Profile $profile)
    {}

    public function getId(): int
    {
        return $this->profile->id;
    }

    public function getDiler(): string
    {
        return $this->profile->diler;
    }

    public function getData(): array
    {
        return $this->profile->getContext();
    }
}
