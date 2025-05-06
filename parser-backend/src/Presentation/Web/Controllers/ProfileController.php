<?php
namespace App\Presentation\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Application\Service\ProfileService;


class ProfileController {
    use ResponseTrait;

    public function __construct(
        private ProfileService $profileService
    ) {}

    public function getAll(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $profiles = $this->profileService->getAllProfiles();
        return $this->appResponse($response, $profiles);
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';
        if (!$id) {
            return $this->appResponse($response, ['error' => 'ID is required'], 400);
        }
        $context = $this->profileService->getProfile($id);
        return $this->appResponse($response, $context->getData());
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $context = $this->profileService->createProfile($data);

        return $this->appResponse($response, $context->getData(), 201);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';
        if (!$id) {
            return $this->appResponse($response, ['error' => 'ID is required'], 400);
        }
        $data = $request->getParsedBody();
        $context = $this->profileService->updateProfile($id, $data);

        return $this->appResponse($response, $context->getData());
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';
        if (!$id) {
            return $this->appResponse($response, ['error' => 'ID is required'], 400);
        }
        $this->profileService->deleteProfile($id);

        return $this->appResponse($response, [], 204);
    }
}
