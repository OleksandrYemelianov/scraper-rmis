<?php

namespace App\Domain\Model;

class Sku extends AppModel
{
    private function __construct(
        public int $profile_id,
        public int $id = 0,
        public string $begin = '',
        public ?int $number = null,
        public string $end = ''
    ) {}

    public static function getInstance(array $data): self
    {
        if (empty($data['profile_id'])) {
            throw new \RuntimeException('Sku. Profile_id is required');
        }

        return new self(
            profile_id: $data['profile_id'],
            id: $data['id'] ?? 0,
            begin: $data['begin'] ?? '',
            number: $data['number'] ?? null,
            end: $data['end'] ?? ''
        );
    }
}
