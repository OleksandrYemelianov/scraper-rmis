<?php
namespace App\Domain\Model;

class Profile extends AppModel
{
    private function __construct(
        public int $id = 0,
        public string $name = '',
        public string $login = '',
        public string $password = '',
        public string $diler = '',
        public ?int $nds = null,
        public int $parseDesc = 1,
        public int $parsePhoto = 1,
        public array $sku = [],
        public array $proxy = [],
        public array $categories = [],

    ) {}

    public static function getInstance(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            login: $data['login'] ?? '',
            password: $data['password'] ?? '',
            diler: $data['diler'] ?? '',
            nds: $data['nds'] ?? null,
            parseDesc: $data['parseDesc'] ?? 1,
            parsePhoto: $data['parsePhoto'] ?? 1,
            sku: $data['sku'] ?? [],
            proxy: $data['proxy'] ?? [],
            categories: $data['categories'] ?? [],
        );
    }

    public function addCategory(array $category): void
    {
        $this->categories[] = $category;
    }

    public function addSku(array $sku): void
    {
        $this->sku = $sku;
    }

    public function addProxy(array $proxy): void
    {
        $this->proxy = $proxy;
    }
}
