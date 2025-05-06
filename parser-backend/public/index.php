<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use App\Domain\Model\Profile;
use App\Domain\Model\Sku;
use Slim\Factory\AppFactory;
use DI\Container;
use App\Infrastructure\Middleware\AuthMiddleware;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dependencies = require __DIR__ . '/../config/dependencies.php';
$container = new Container($dependencies);

// Создание приложения
AppFactory::setContainer($container);
$app = AppFactory::create();

// CORS Middleware

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    if ($request->getMethod() === 'OPTIONS') {
        return $response->withStatus(204);
    }

    return $response;
});

// Добавляем middleware для группы /api
$app->add(new AuthMiddleware());
$app->add(BodyParsingMiddleware::class);

// Определяем группу маршрутов
$app->group('/api', require __DIR__ . '/../src/Presentation/Web/Routes/api.php');

// Запуск приложения
$app->run();
