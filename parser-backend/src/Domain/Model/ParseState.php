<?php

namespace App\Domain\Model;

class ParseState extends AppModel
{
    private function __construct(
        public string $profile_id,
        public int $id = 0,
        public int $last = 0,
        public int $all = 0,
        public int $current = 0,
        public int $complete = 0
    )
    {}

    public static function getInstance(array $data): self
    {
        if (empty($data['profile_id'])) {
            throw new \RuntimeException('ParseState. Profile_id is required');
        }

        return new self(
            profile_id: $data['profile_id'],
            id: $data['id'] ?? 0,
            last: $data['last'] ?? 0,
            all: $data['all'] ?? 0,
            current: $data['current'] ?? 0,
            complete : $data['complete'] ?? 0
        );
    }

    // Сеттеры для изменения состояния
    public function setLast(int $last): void
    {
        $this->last = $last;
    }

    public function setAll(int $all): void
    {
        $this->all = $all;
    }

    public function setCurrent(int $current): void
    {
        $this->current = $current;
    }

    public function setComplete(int $complete): void
    {
        $this->complete = $complete;
    }

    public function updateState(int $last, int $current, int $all, int $complete): void
    {
        $this->last = $last;
        $this->all = $all;
        $this->current = $current;
        $this->complete = $complete;
    }
}