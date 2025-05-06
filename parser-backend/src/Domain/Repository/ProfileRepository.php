<?php
namespace App\Domain\Repository;

use App\Domain\Model\Profile;
use App\Domain\Model\Proxy;
use App\Domain\Model\Sku;
use PDO;

class ProfileRepository extends AppRepository
{
    public function __construct(
        private PDO $pdo,
        private readonly CategoryRepository $categoryRepository,
        private readonly ProxyRepository $proxyRepository,
        private readonly SkuRepository $skuRepository
    ) {
        parent::__construct($this->pdo, '`profiles`');
    }

    public function getShortProfiles(): array
    {
        return $this->getRows(Profile::getInstance([]));
    }

    public function findById(int $id): ?Profile
    {
        $data = $this->getById(Profile::getInstance(['id' => $id]));
        return $data ? $this->hydrateProfile($data) : null;
    }

    public function save(Profile $profile): Profile
    {
        $this->pdo->beginTransaction();

        try {
            $profileId = $this->create($profile);
            $profile->proxy['profile_id'] = $profileId;
            $profile->sku['profile_id'] = $profileId;

            // Сохранение связанных данных
            $this->categoryRepository->save($profileId, $profile->categories);
            $this->proxyRepository->save(Proxy::getInstance($profile->proxy));
            $this->skuRepository->save(Sku::getInstance($profile->sku));

            $this->pdo->commit();
            $profile->id = $profileId;
            return $profile;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new \RuntimeException("Error profile save: " . $e->getMessage(), 0, $e);
        }
    }

    public function update(Profile $profile): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->updateById($profile);

            // Обновление связанных данных
            $this->categoryRepository->update($profile->id, $profile->categories);

            $profile->proxy['profile_id'] = $profile->id;
            $profile->sku['profile_id'] = $profile->id;
            $this->proxyRepository->update(Proxy::getInstance($profile->proxy));
            $this->skuRepository->update(Sku::getInstance($profile->sku));

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new \RuntimeException("Error profile update: " . $e->getMessage(), 0, $e);
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->deleteById(Profile::getInstance(['id' => $id]));
        } catch (\Exception $e) {
            throw new \RuntimeException("Error profile delete: " . $e->getMessage(), 0, $e);
        }
    }

    private function hydrateProfile(array $data): Profile
    {
        $profile = Profile::getInstance($data);

        // Загружаем категории
        $categories = $this->categoryRepository->getAll($profile->id);
        foreach ($categories as $category) {
            $profile->addCategory($category);
        }

        // Загружаем прокси
        $proxy = $this->proxyRepository->get($profile->id);
        $profile->addProxy($proxy);

        // Загружаем SKU
        $sku = $this->skuRepository->get($profile->id);
        $profile->addSku($sku);

        return $profile;
    }
}

