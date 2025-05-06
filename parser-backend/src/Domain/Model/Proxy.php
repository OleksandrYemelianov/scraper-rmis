<?php

namespace App\Domain\Model;

class Proxy extends AppModel
{
    private function __construct(
        public int $profile_id,
        public int $id = 0,
        public string $host = '',
        public int $port = 0,
        public string $login = '',
        public string $password = ''
    )
    {}

    public static function getInstance(array $data): self
    {
        if (empty($data['profile_id'])) {
            throw new \RuntimeException('Proxy. Profile_id is required');
        }

        return new self(
            profile_id: $data['profile_id'],
            id: $data['id'] ?? 0,
            host: $data['host'] ?? '',
            port: empty($data['port']) ? 0 : $data['port'],
            login: $data['login'] ?? '',
            password: $data['password'] ?? ''
        );
    }
}