<?php
declare(strict_types=1);

require_once __DIR__ . '/TimestampableTrait.php';

class Task
{
    use TimestampableTrait;

    public function __construct(
        public readonly int    $id,
        public readonly int    $userId,
        private string         $title,
        private bool           $isCompleted = false,
        private ?string        $deadline    = null,
    ) {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Назва завдання не може бути порожньою.');
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted || $this->deadline === null) {
            return false;
        }
        return strtotime($this->deadline) < time();
    }

    public function complete(): void
    {
        $this->isCompleted = true;
        $this->touch();
    }

    public function getStatusLabel(): string
    {
        if ($this->isCompleted) {
            return 'Виконано';
        }
        if ($this->isOverdue()) {
            return 'Прострочено';
        }
        return 'В процесі';
    }

    public static function fromArray(array $data): self
    {
        $task = new self(
            id:          (int) $data['id'],
            userId:      (int) $data['user_id'],
            title:       $data['title'],
            isCompleted: (bool) $data['is_completed'],
            deadline:    $data['deadline'] ?? null,
        );

        $task->initTimestamps(
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );

        return $task;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->userId,
            'title'        => $this->title,
            'is_completed' => $this->isCompleted,
            'deadline'     => $this->deadline,
            'created_at'   => $this->getCreatedAt(),
            'updated_at'   => $this->getUpdatedAt(),
        ];
    }
}
