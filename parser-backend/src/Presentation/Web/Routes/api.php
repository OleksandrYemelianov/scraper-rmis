<?php
use Slim\Routing\RouteCollectorProxy;
use App\Presentation\Web\Controllers\ProfileController;
use App\Presentation\Web\Controllers\ParserController;

return function (RouteCollectorProxy $group) {
    $group->get('/profiles', [ProfileController::class, 'getAll']);
    $group->post('/profiles', [ProfileController::class, 'create']);
    $group->get('/profiles/{id}', [ProfileController::class, 'get']);
    $group->put('/profiles/{id}', [ProfileController::class, 'update']);
    $group->delete('/profiles/{id}', [ProfileController::class, 'delete']);
    $group->get('/parse/{id}', [ParserController::class, 'runParsing']);
    $group->get('/parse-refresh/{id}', [ParserController::class, 'refreshParsing']);
    $group->get('/parse-state/{id}', [ParserController::class, 'getParseState']);
};
