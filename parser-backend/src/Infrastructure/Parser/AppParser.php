<?php

namespace App\Infrastructure\Parser;

use App\Application\Parser\ParserInterface;
use App\Application\Parser\ParseStateUpdater;
use App\Application\Parser\ParsingContext;
use App\Application\Service\ParserService;
use App\Application\Service\SkuService;

abstract class AppParser implements ParserInterface
{
    private array $price_ranges = [];
    protected int $batch;
    protected int $sku_number;
    protected array $profile;

    public function __construct(
        protected ParsingContext $context,
        protected ParseStateUpdater $stateUpdater,
        protected ParserService $parserService,
        protected SkuService $skuService
    ) {}

    abstract protected function getBatch(): int;
    abstract protected function getPreProductsByCategory(array $category_info): array;
    abstract protected function getProductData(array $pre_product, array $category_setting): array;

    protected function getParseState(): array
    {
        return $this->stateUpdater->getCurrentState()->getData();
    }

    protected function updateParseState(int $last, int $current, int $all, int $complete): void
    {
        $this->stateUpdater->updateState(
            last: $last,
            current: $current,
            all: $all,
            complete: $complete
        );
    }

    public function run(): array
    {
        $state = $this->getParseState();
        $this->profile = $this->context->getData();

        $this->setPriceRanges(empty($this->profile['categories']) ? [] : $this->profile['categories']);
        $this->setSkuNumber($state);

        $pre_products = $this->parserService->getPreProducts($state['last'], $this->getBatch());
        $products = [];
        foreach ($pre_products as $pre_product) {
            $category_setting = $this->getCategorySetting($this->getParseCategoryId($pre_product));
            $product_data = $this->getProductData($pre_product, $category_setting);
            if (!empty($product_data)) {
                $products[] = $product_data;
            }
        }
        $this->parserService->saveProducts($products);

        $state['last'] += $this->getBatch();
        if ($state['last'] >= $state['all']) {
            $state['last'] = $state['all'];
            $state['complete'] = 1;
        }
        $state['current'] = $state['last'];

        // Нумерация sku должна меняться в адаптере магазина. Только он знает какие sku новые, а какие нет
        //$this->saveProfileSku();

        $this->updateParseState(
            $state['last'],
            $state['current'],
            $state['all'],
            $state['complete']
        );

        return $state;
    }

    public function refresh(): array
    {
        $profile = $this->context->getData();
        $this->parserService->removePreProducts();

        foreach ($profile['categories'] as $category) {
            if (empty($category['url'])) {
                continue;
            }
            $pre_products = $this->getPreProductsByCategory($category);
            if (empty($pre_products)) {
                continue;
            }

            foreach ($pre_products as &$_product) {
                $_product = $this->setParseCategoryId($_product, $category['id']);
            }
            $this->parserService->savePreProducts($pre_products);
        }

        // удаляем старый файл с подготовленными продуктами
        $this->parserService->removeProducts();
        $state = $this->getParseState();
        $all = $this->parserService->getCountPreProducts();
        $this->updateParseState(0, 0, $all, 0, $state['sku_number']);
        return $this->getParseState();
    }

    private function setSkuNumber(array $state)
    {
        $this->sku_number = (int) $this->profile['sku']['number'];
    }

    private function saveProfileSku()
    {
        $data = $this->profile['sku'];
        $data['profile_id'] = $this->profile['id'];
        $data['number'] = $this->sku_number;
        $this->skuService->update($data);
    }

    protected function getSku(): string
    {
        $this->sku_number++;
        $begin = empty($this->profile['sku']['begin']) ? '' : $this->profile['sku']['begin'];
        $end = empty($this->profile['sku']['end']) ? '' : $this->profile['sku']['end'];
        return $begin . $this->sku_number . $end;
    }

    protected function getCategorySetting($category_id): array
    {
        if (empty($this->profile['categories'])) {
            return [];
        }
        $category_settings = [];
        foreach ($this->profile['categories'] as $category) {
            if ($category_id == $category['id']) {
                $category_settings = $category;
            }    
        }

        return $category_settings;
    }

    protected function setParseCategoryId(array $pre_product, int $category_id): array
    {
        $pre_product['parse_category_id'] = $category_id;
        return $pre_product;
    }

    protected function getParseCategoryId(array $pre_product): int
    {
        return $pre_product['parse_category_id'] ?? 0;
    }

    protected function setPriceRanges(array $categories): void
    {
        foreach ($categories as $category) {
            if (!empty($category['prices'])) {
                $this->price_ranges[ $category['id'] ] = $category['prices'];
            }    
        }
    }

    private function getPriceRanges(int $category_id): array
    {
        $ranges = [];
        if (!empty($this->price_ranges[ $category_id ])) {
            $ranges = $this->price_ranges[ $category_id ];
        }

        return $ranges;
    }

    protected function calculatePrice(float $price, int $category_id): string
    {
        $ranges = $this->getPriceRanges($category_id);
        if (empty($ranges)) {
            return $price;
        }

        $range = [];
        foreach ($ranges as $_range) {
            if ($_range['condition'] <= $price) {
                $range = $_range;
            }    
        }
        if (empty($range)) {
            return $price;
        }

        $price = match ($range['sign']) {
            '-' => $price -= (float) $range['margin'],
            '+' => $price += (float) $range['margin'],
            '%' => $price += round ($price * (float) $range['margin'] / 100),
        };

        return (string) number_format($price, 2, '.', '');
    }
}
