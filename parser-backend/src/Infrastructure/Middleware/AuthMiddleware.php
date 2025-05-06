<?php
namespace App\Infrastructure\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware
{
    private array $validCredentials;

    public function __construct()
    {
        $this->validCredentials[] = [
            'username' => getenv('VITE_API_LOGIN'), 'password' => getenv('VITE_API_PASS')
        ];
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
            throw new HttpUnauthorizedException($request, 'No authorization header provided');
        }

        $credentials = base64_decode($matches[1]);
        list($username, $password) = explode(':', $credentials, 2);

        foreach ($this->validCredentials as $validCred) {

            if ($username === $validCred['username'] && $password === $validCred['password']) {
                return $handler->handle($request);
            }
        }

        throw new HttpUnauthorizedException($request, 'Invalid credentials');
    }
}
