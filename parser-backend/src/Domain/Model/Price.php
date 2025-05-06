<?php

namespace App\Domain\Model;

class Price extends AppModel
{
    public function __construct(
        public int $category_id,
        public int    $id = 0,
        public ?int   $condition = null,
        public string $sign = '+',
        public ?int   $margin = null
    )
    {}

    public static function getInstance(array $data): self
    {
        if (empty($data['category_id'])) {
            throw new \RuntimeException('Price. Category_id is required');
        }

        return new self(
            category_id: $data['category_id'],
            id: $data['id'] ?? 0,
            condition: $data['condition'] ?? null,
            sign: $data['sign'] ?? '+',
            margin: $data['margin'] ?? null
        );
    }
}