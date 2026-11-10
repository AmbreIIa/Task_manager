<?php
declare(strict_types=1);

require_once __DIR__ . '/Task.php';

interface TaskStorageInterface
{
    public function add(int $userId, string $title, ?string $deadline = null): Task;
    public function findAllByUser(int $userId, int $page = 1, int $perPage = 5): array;
    public function countByUser(int $userId): int;
    public function countCompletedByUser(int $userId): int;
    public function countOverdueByUser(int $userId): int;
    public function findByIdAndUser(int $id, int $userId): ?Task;
    public function complete(int $id, int $userId): bool;
    public function delete(int $id, int $userId): bool;
}

?>
