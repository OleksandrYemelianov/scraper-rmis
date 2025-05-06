<?php
namespace App\Domain\Repository;

use App\Domain\Model\Category;

class CategoryRepository extends AppRepository
{
    public function __construct(protected \PDO $pdo, private readonly PriceRepository $priceRepository)
    {
        parent::__construct($this->pdo, '`profiles_categories`');
    }

    public function getAll(int $profileId): array
    {
        $categoriesData = $this->getRowsByField(
            Category::getInstance(['profile_id' => $profileId]),
            'profile_id'
        );
        
        if (empty($categoriesData)) {
            return [];
        }

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = Category::getInstance($data);
            $prices = $this->priceRepository->getAll($category->id);
            foreach ($prices as $price) {
                $category->addPrices($price);
            }
            $categories[] = (array)$category;
        }
        return $categories;
    }

    public function save(int $profileId, array $categories): void
    {
        if (empty($profileId)) {
            throw new \RuntimeException('CategoryRepository. profile_id is required');
        }

        if (empty($categories)) {
            return;
        }

        foreach ($categories as $_category) {
            $_category['profile_id'] = $profileId;
            $category = Category::getInstance($_category);
            $categoryId = $this->create($category);
            if (!empty($category->prices)) {
                $this->priceRepository->save($categoryId, $category->prices);
            }
        }
    }

    public function update(int $profileId, array $categories): void
    {
        if (empty($categories)) {
            return;
        }

        // Удаляем старые категории для этого профиля
        $this->deleteByField(Category::getInstance(['profile_id' => $profileId]), 'profile_id');

        // Вставляем новые категории
        $this->save($profileId, $categories);
    }
}
