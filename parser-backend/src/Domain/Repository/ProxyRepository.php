<?php
namespace App\Domain\Repository;

use App\Domain\Model\Proxy;

class ProxyRepository extends AppRepository
{
    public function __construct(private readonly \PDO $pdo)
    {
        parent::__construct($this->pdo, '`profiles_proxy`');
    }

    public function get(int $profile_id): array
    {
        return $this->getByField(
            Proxy::getInstance(['profile_id' => $profile_id]),
            'profile_id'
        ) ?? [];
    }

    public function save(Proxy $proxy): void
    {
        try {
            $this->create($proxy);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(Proxy $proxy): void
    {
        $this->deleteByField($proxy, 'profile_id');
        $this->create($proxy);
    }
}
