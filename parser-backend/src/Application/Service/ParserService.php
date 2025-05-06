<?php

namespace App\Application\Service;

use App\Domain\Model\Product;
use App\Domain\Repository\PreProductRepository;
use App\Domain\Repository\ProductRepository;
use App\Domain\Repository\JsonlStorageRepository;

class ParserService
{
    private JsonlStorageRepository $productRepository;
    private JsonlStorageRepository $preProductRepository;

    public function __construct(string $diler)
    {
        $this->productRepository = new ProductRepository($diler);
        $this->preProductRepository = new PreProductRepository($diler);
    }

    /*
     * @desc Запись предварительной информации о продукте
     * Это может быть ссылка на продукт или еще какая-либо инфа
     * Каждый элемент массива это инфа об одном продукте
     */
    public function savePreProducts(array $products_data): void
    {
        $this->preProductRepository->save($products_data);
    }

    public function saveProducts(array $products_data): void
    {
        $prepare_product_data = [];
        foreach ($products_data as $item) {
            $product = Product::getInstance($item);
            $prepare_product_data[] = $product->getContext();
        }

        $this->productRepository->save($prepare_product_data);
    }

    public function removePreProducts(): void
    {
        $this->preProductRepository->remove();
    }

    public function removeProducts(): void
    {
        $this->productRepository->remove();
    }

    public function getPreProducts(int $begin, int $length): array
    {
        try {
            return $this->preProductRepository->load($begin, $length);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error parsing: " . $e->getMessage(), 0, $e);
        }
    }

    public function getCountPreProducts(): int
    {
        return $this->preProductRepository->getLineCount();
    }
}
