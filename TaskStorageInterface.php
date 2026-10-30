<?php
declare(strict_types=1);

require_once __DIR__ . '/Task.php';

interface TaskStorageInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Task;
    public function add(string $title, ?string $deadline = null): Task;
    public function complete(int $id): bool;
    public function delete(int $id): bool;
    public function countCompleted(): int;
    public function countOverdue(): int;
}

?>
