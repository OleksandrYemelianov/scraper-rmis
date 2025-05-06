<?php

namespace App\Infrastructure\Parser;

use App\Infrastructure\Parser\AppParser;

class MorleviParser extends AppParser
{
    public function run()
    {
        return ['start' => 'Morlevi'];
    }
}