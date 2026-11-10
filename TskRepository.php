<?php

declare(strict_types=1);

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskStorageInterface.php';
require_once __DIR__ . '/Exceptions.php';

class TaskRepository implements TaskStorageInterface
{
    public function __construct(private PDO $pdo) {}

    public function findAllByUser(int $userId, int $page = 1, int $perPage = 5): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = '
            SELECT id, user_id, title, is_completed, deadline, created_at, updated_at
            FROM tasks
            WHERE user_id = :user_id
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);

        // При вимкненій емуляції параметри LIMIT та OFFSET мають бути строго цілими числами
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);

        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Task::fromArray($row), $rows);
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countCompletedByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND is_completed = 1');
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countOverdueByUser(int $userId): int
    {
        $sql = '
            SELECT COUNT(*) 
            FROM tasks 
            WHERE user_id = :user_id 
              AND is_completed = 0 
              AND deadline IS NOT NULL 
              AND deadline < NOW()
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function findByIdAndUser(int $id, int $userId): ?Task
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        $row = $stmt->fetch();
        return $row ? Task::fromArray($row) : null;
    }

    public function add(int $userId, string $title, ?string $deadline = null): Task
    {
        $errors = [];
        $trimmedTitle = trim($title);

        if (empty($trimmedTitle)) {
            $errors['title'] = 'Назва завдання є обов\'язковою для заповнення.';
        } elseif (mb_strlen($trimmedTitle) < 3) {
            $errors['title'] = 'Назва завдання має містити щонайменше 3 символи.';
        }

        if (!empty($deadline) && strtotime($deadline) === false) {
            $errors['deadline'] = 'Вказано некоректний формат дати дедлайну.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $sql = '
            INSERT INTO tasks (user_id, title, is_completed, deadline, created_at)
            VALUES (:user_id, :title, 0, :deadline, NOW())
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'  => $userId,
            ':title'    => $trimmedTitle,
            ':deadline' => !empty($deadline) ? $deadline : null,
        ]);

        $newId = (int) $this->pdo->lastInsertId();

        $createdTask = $this->findByIdAndUser($newId, $userId);
        if ($createdTask === null) {
            throw new RuntimeException('Не вдалося отримати створене завдання з бази даних.');
        }

        return $createdTask;
    }

    public function complete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE tasks 
            SET is_completed = 1, updated_at = NOW() 
            WHERE id = :id AND user_id = :user_id
        ');

        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        if ($stmt->rowCount() === 0) {
            // Перевіряємо, чи взагалі існує завдання
            if ($this->findByIdAndUser($id, $userId) === null) {
                throw new TaskNotFoundException("Завдання з ID {$id} не знайдено або воно належить іншому користувачу.");
            }
        }

        return true;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new TaskNotFoundException("Неможливо видалити: завдання з ID {$id} не знайдено.");
        }

        return true;
    }
}
