<?php
namespace App\Domain\Repository;

use App\Domain\Model\Price;

class PriceRepository extends AppRepository
{
    public function __construct(protected \PDO $pdo)
    {
        parent::__construct($this->pdo, '`profiles_prices`');
    }

    public function getAll(int $category_id): array
    {
        return $this->getRowsByField(
            Price::getInstance(['category_id' => $category_id]),
            'category_id'
        ) ?? [];
    }

    public function save(int $category_id, array $prices): void
    {
        if (!isset($category_id)) {
            throw new \RuntimeException('PriceRepository. Category_id is required');
        }

        // Удаляем старые записи
        $this->deleteByField(Price::getInstance(['category_id' => $category_id]), 'category_id');
        foreach ($prices as $_price) {
            $_price['category_id'] = $category_id;
            $this->create(Price::getInstance($_price));
        }
    }
}
