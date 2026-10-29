<?php
declare(strict_types=1);

require_once __DIR__ . '/TimestampableTrait.php';

class Task
{
    public function __construct(
        public readonly int    $id,
        private string         $title,
        private bool           $isCompleted = false,
        private ?string        $deadline    = null,
        ?string                $createdAt = null,
        ?string                $updatedAt = null,
    ) {

        $this->initTimestamps($createdAt, $updatedAt);

        if (empty(trim($title))) {
            throw new InvalidArgumentException('Назва завдання не може бути пустою!');
        }
    }

    // --- Геттери ---

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function isCompleted(): bool { return $this->isCompleted; }
    public function getDeadline(): ?string { return $this->deadline; }


    // --- Бізнес-методи ---

    public function isOverdue(): bool
    {
        if ($this->isCompleted || $this->deadline === null) {
            return false;
        }
        return strtotime($this->deadline) < time();
    }
    
    public function complete(): void { $this->isCompleted = true; $this->touch(); }

    public function getStatusLabel(): string
    {
        if ($this->isCompleted) return 'Виконано';
        if ($this->isOverdue()) return 'Прострочено';
        return 'В процесі';
    }
    
    // --- Серіалізація / Десеріалізація ---

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'isCompleted' => $this->isCompleted,
            'deadline'    => $this->deadline,
            'createdAt'   => $this->getCreatedAt(),
            'udatedAt'    => $this->getUpdatedAt(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:          (int) $data['id'],
            title:       (string) $data['title'],
            isCompleted: (bool) $data['isCompleted'] ?? false,
            deadline:    $data['deadline']    ?? null,
            createdAt:   $data['cratedAt']    ?? null,
            updatedAt:   $data['updatedAt']   ?? null,
        );
    }
}
