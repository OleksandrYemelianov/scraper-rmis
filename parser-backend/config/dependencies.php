<?php

use App\Domain\Repository\AppRepository;
use App\Domain\Repository\CategoryRepository;
use App\Domain\Repository\ParseStateRepository;
use App\Domain\Repository\PriceRepository;
use App\Domain\Repository\ProxyRepository;
use App\Domain\Repository\SkuRepository;
use App\Infrastructure\Parser\AppParser;
use App\Infrastructure\Parser\ParserFactory;
use App\Infrastructure\Storage\JsonDataStorage;
use App\Domain\Repository\ProfileRepository;
use App\Application\Service\ProfileService;
use App\Application\Service\SkuService;
use App\Application\Service\ParseStateService;
use App\Infrastructure\Parser\ApiScraper;
use App\Infrastructure\Parser\HtmlScraper;
use App\Presentation\Web\Controllers\ProfileController;
use App\Presentation\Web\Controllers\ParserController;
use function DI\create;
use function DI\get;

return [
    'pdo' => function () {
        $pdo = new PDO(
            "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    },

    // Repository
    AppRepository::class => create(AppRepository::class)->constructor(get('pdo')),
    ParseStateRepository::class => create(ParseStateRepository::class)->constructor(get('pdo')),
    PriceRepository::class => create(PriceRepository::class)->constructor(get('pdo')),
    CategoryRepository::class => create(CategoryRepository::class)->constructor(
        get('pdo'),
        get(PriceRepository::class)
    ),
    ProxyRepository::class => create(ProxyRepository::class)->constructor(get('pdo')),
    SkuRepository::class => create(SkuRepository::class)->constructor(get('pdo')),
    ProfileRepository::class => create(ProfileRepository::class)->constructor(
        get('pdo'),
        get(CategoryRepository::class),
        get(ProxyRepository::class),
        get(SkuRepository::class)
    ),

    // Service
    JsonDataStorage::class => function () {
        return new JsonDataStorage(__DIR__ . '/../../storage/products.json');
    },
    ParseStateService::class => create(ParseStateService::class)
        ->constructor(
            get(ParseStateRepository::class)
        ),
    ProfileService::class => create(ProfileService::class)->constructor(get(
        ProfileRepository::class)
    ),
    SkuService::class => create(SkuService::class)->constructor(get(
            SkuRepository::class)
    ),

    // Factory
    ParserFactory::class => create(ParserFactory::class)->constructor(
        get(ParseStateService::class),
        get(SkuService::class)
    ),

    // Controller
    ProfileController::class => create(ProfileController::class)->constructor(
        get(ProfileService::class)
    ),
    ParserController::class => create(ParserController::class)
        ->constructor(
            get(ParseStateService::class),
            get(ProfileService::class),
            get(ParserFactory::class)
    ),

];
