<?php

namespace App\Application\Parser;

interface ParserInterface
{
    public function run(): array;
    public function refresh(): array;
}