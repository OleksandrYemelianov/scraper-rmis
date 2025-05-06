<?php

namespace App\Domain\Repository;

use App\Domain\Model\AppModel;
use \PDO;

class AppRepository
{
    protected string $table;
    public function __construct(private \PDO $pdo, $table)
    {
        $this->table = $table;
    }

    protected function create(AppModel $model): int
    {
        $stmt = $this->pdo->prepare("
                INSERT INTO " . $this->table . " (" . $model->getInsertKey() . ")
                VALUES (" . $model->getInsertValue() . ")
            ");
        $stmt->execute($model->toArrayInsert());

        return (int) $this->pdo->lastInsertId();
    }

    protected function getRows(AppModel $model): ?array
    {
        $stmt = $this->pdo->query("SELECT * FROM " . $this->table);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res ?: [];
    }

    protected function getById(AppModel $model): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $model->id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: [];
    }

    protected function getByField(AppModel $model, string $field_name): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$field_name} = :" . $field_name);
        $stmt->execute([$field_name => $model->$field_name]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: [];
    }

    protected function getRowsByField(AppModel $model, string $field_name): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$field_name} = :" . $field_name);
        $stmt->execute([$field_name => $model->$field_name]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res ?: [];
    }

    protected function deleteById(AppModel $model): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $model->id]);
    }

    protected function deleteByField(AppModel $model, string $field): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$field} = :{$field}");
        $stmt->execute([$field => $model->$field]);
    }

    protected function updateById(AppModel $model): void
    {
        $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET {$model->getUpdateSet()}
                WHERE id = :id
            ");
        $stmt->execute($model->toArray());
    }

    protected function updateByField(AppModel $model, string $field): void
    {
        $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET {$model->getUpdateSet([$field])}
                WHERE {$field} = :{$field}
            ");

        $data = $model->toArray();
        unset($data['id']);
        $stmt->execute($data);
    }
}
