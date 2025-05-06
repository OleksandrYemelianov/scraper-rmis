<?php

namespace App\Domain\Repository;

class ProductRepository extends JsonlStorageRepository
{
    public function __construct(string $diler)
    {
        $this->setFileName(sprintf('%s.product', $diler));
    }
}
