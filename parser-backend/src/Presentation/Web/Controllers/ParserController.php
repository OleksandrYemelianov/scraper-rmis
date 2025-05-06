<?php

namespace App\Presentation\Web\Controllers;

use App\Application\Service\ProfileService;
use App\Infrastructure\Parser\ParserFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Application\Service\ParseStateService;

class ParserController
{
    use ResponseTrait;

    public function __construct(
        private readonly ParseStateService $parserService,
        private readonly ProfileService    $profileService,
        private readonly ParserFactory     $parserFactory
    ) {}

    public function getParseState(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';
        if (!$id) {
            return $this->appResponse($response, ['error' => 'Profile ID is required'], 400);
        }
        $state_context = $this->parserService->getState($id);
        return $this->appResponse($response, $state_context->getData());
    }

    public function runParsing(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->createAndExecuteParser($request, $response, $args, 'run');
    }

    public function refreshParsing(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->createAndExecuteParser($request, $response, $args, 'refresh');
    }

    private function createAndExecuteParser(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
        string $parserMethod
    ): ResponseInterface
    {
        $id = $args['id'] ?? '';
        if (!$id) {
            return $this->appResponse($response, ['error' => 'Profile ID is required'], 400);
        }

        $profile_context = $this->profileService->getProfile($id);
        $this->parserService->getState($id);
        $parser = $this->parserFactory->create($profile_context);

        try {
            $result = $parser->$parserMethod();
            return $this->appResponse($response, $result);
        } catch (\RuntimeException $e) {
            $stackTrace = $e->getTraceAsString();
            return $this->appResponse($response, [
                'error' => $e->getMessage(),
                'stack' => $stackTrace
            ], 400);
        }
    }
}
