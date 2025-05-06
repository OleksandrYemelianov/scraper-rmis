<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\ParseStateService;
use App\Application\Service\ProfileService;
use App\Infrastructure\Parser\ParserFactory;
use DI\ContainerBuilder;

// Инициализация контейнера
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/dependencies.php');
$container = $containerBuilder->build();

// Получение зависимостей
$parserService = $container->get(ParseStateService::class);
$parserFactory = $container->get(ParserFactory::class);
$profileService = $container->get(ProfileService::class);

// Проверка аргументов
$profileId = $argv[1] ?? null;
if (!$profileId) {
    fwrite(STDERR, "Error: Profile ID is required\n");
    exit(1);
}

try {
    $profile_context = $profileService->getProfile($profileId);
    $parserService->getState($profileId);
    $parser = $parserFactory->create($profile_context);

    // Запуск парсера
    $result = $parser->run();
    $complete = $result['complete'] ?? 0;

    // Вывод значения complete
    echo $complete . "\n";
    exit(0);
} catch (\Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
