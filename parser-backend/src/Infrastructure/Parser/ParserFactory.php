<?php

namespace App\Infrastructure\Parser;

use App\Application\Parser\ParseStateUpdater;
use App\Application\Parser\ParsingContext;
use App\Application\Service\ParserService;
use App\Application\Service\SkuService;

class ParserFactory
{
    public function __construct(private ParseStateUpdater $stateUpdater, private SkuService $skuService)
    {
    }

    public function create(ParsingContext $profile_context): AppParser
    {
        try {
            if (empty($profile_context->getDiler())) {
                throw new \Exception("Поле поставщик обязательно");
            }
            $className = sprintf('App\Infrastructure\Parser\%sParser', ucfirst(strtolower($profile_context->getDiler())));
            if (!class_exists($className)) {
                throw new \Exception("Неизвестный парсер");
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Error parsing: " . $e->getMessage(), 0, $e);
        }

        return new $className(
            $profile_context,
            $this->stateUpdater,
            new ParserService($profile_context->getDiler()),
            $this->skuService
        );
    }
}
