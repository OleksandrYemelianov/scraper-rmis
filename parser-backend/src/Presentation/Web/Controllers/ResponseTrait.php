<?php

namespace App\Presentation\Web\Controllers;

use Psr\Http\Message\ResponseInterface;

trait ResponseTrait
{
    public function appResponse(ResponseInterface $response, array $data = [], int $status = 200): ResponseInterface
    {
        $response = $response->withStatus($status);
        if (!empty($data)) {
            $response->getBody()->write(json_encode($data));
        }    

        return $response->withHeader('Content-Type', 'application/json');
    }
}
