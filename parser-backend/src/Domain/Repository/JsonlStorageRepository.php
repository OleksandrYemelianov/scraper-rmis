<?php

namespace App\Domain\Repository;

class JsonlStorageRepository
{
    private string $file_path;
    private string $storage_folder = __DIR__ . '/../../../storage';

    protected function setFileName(string $file_name): void
    {
        $this->file_path = $this->storage_folder . '/' . $file_name . '.jsonl';
    }

    public function save(array $data): void
    {
        foreach ($data as $line) {
            $this->saveLine($line);
        }
    }

    public function remove(): void
    {
        if (is_file($this->file_path)) {
            unlink($this->file_path);
        }
    }

    public function load(int $begin, int $length): array
    {
        if (!file_exists($this->file_path)) {
            throw new \Exception("Файл не найден: " . $this->file_path);
        }
        if ($begin < 0) {
            throw new \Exception("Начальная строка должна быть больше 0");
        }
        if ($length < 0) {
            throw new \Exception("Количество строк не может быть отрицательным");
        }

        $file = new \SplFileObject($this->file_path, 'r');
        $file->seek($begin);

        $result = [];
        $count = 0;

        while (!$file->eof() && $count < $length) {
            $line = trim($file->fgets());
            if ($line !== '') {
                $decoded = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Ошибка декодирования JSON в строке " . ($begin + $count - 1));
                }
                $result[] = $decoded;
                $count++;
            }
        }

        return $result;
    }

    public function getLineCount(): int
    {
        if (!is_file($this->file_path)) {
            return 0;
        }    
        $file = new \SplFileObject($this->file_path, 'r');
        $file->seek(PHP_INT_MAX);
        return $file->key() + 1;
    }

    private function saveLine(array $line): void
    {
        $this->checkFilePath();
        $file = new \SplFileObject($this->file_path, 'a');
        $data = json_encode($line, JSON_UNESCAPED_UNICODE);
        $data = str_replace("\n", '', $data);
        $file->fwrite($data . "\n");
    }

    private function checkFilePath(): void
    {
        if (empty($this->file_path)) {
            throw new \RuntimeException('Storage file name is empty');
        }
    }
}
