<?php
namespace App\Domain\Repository;

use App\Domain\Model\Sku;

class SkuRepository extends AppRepository
{
    public function __construct(private readonly \PDO $pdo)
    {
        parent::__construct($this->pdo, '`profiles_sku`');
    }

    public function get(int $profile_id): array
    {
        return $this->getByField(
            Sku::getInstance(['profile_id' => $profile_id]),
            'profile_id'
        ) ?? [];
    }

    public function save(Sku $sku): void
    {
        $this->create($sku);
    }

    public function update(Sku $sku): void
    {
        $this->deleteByField($sku, 'profile_id');
        $this->create($sku);
    }
}
