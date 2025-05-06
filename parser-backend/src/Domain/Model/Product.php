<?php

namespace App\Domain\Model;

class Product extends AppModel
{
    public function __construct(
        protected string $title,
        protected string $descriptionHtml,
        protected array $productOptions,
        protected array $files,
        protected array $variants,
        protected array $metafields
    )
    {}

    public static function getInstance(array $data): self
    {
        //TODO: сделать определение полей для variants и images
        return new self(
            title: $data['title'] ?? '',
            descriptionHtml: $data['descriptionHtml'] ?? '',
            productOptions: $data['productOptions'] ?? [],
            files: $data['files'] ?? [],
            variants: $data['variants'] ?? [],
            metafields: $data['metafields'] ?? []
        );
    }
}