<?php

namespace App\Application\Parser;

interface ParsingContext
{
    public function getId(): int;
    public function getDiler(): string;
    public function getData(): array;
}