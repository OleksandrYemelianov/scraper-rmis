<?php

namespace App\Domain\Model;

class Category extends AppModel
{
    private function __construct(
        public int $profile_id,
        public int    $id = 0,
        public string $name = '',
        public string $url = '',
        public ?int   $collectionId = null,
        public array  $prices = []
    )
    {}

    public static function getInstance(array $data): self
    {
        if (empty($data['profile_id'])) {
            throw new \RuntimeException('Category. Profile_id is required');
        }

        $obj =  new self(
            profile_id: $data['profile_id'],
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            url: $data['url'] ?? 0,
            collectionId: $data['collectionId'] ?? null,
            prices: $data['prices'] ?? []
        );

        return $obj;
    }

    public function addPrices(array $price): void
    {
        $this->prices[] = $price;
    }
}