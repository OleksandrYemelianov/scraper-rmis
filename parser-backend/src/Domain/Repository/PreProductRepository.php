<?php

namespace App\Domain\Repository;

class PreProductRepository extends JsonlStorageRepository
{
    public function __construct(string $diler)
    {
        $this->setFileName(sprintf('%s.pre', $diler));
    }
}
