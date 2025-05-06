<?php
namespace App\Application\Service;

use App\Domain\Model\Sku;
use App\Domain\Repository\SkuRepository;

class SkuService {
    public function __construct(
        private SkuRepository $skuRepository
    ) {}

    public function update(array $data): void
    {
        $sku = Sku::getInstance($data);
        $this->skuRepository->update($sku);
    }
}
