<?php
declare(strict_types=1);

class TaskManagerExeption extends RuntimeException {}

class TaskNotFoundException extends TaskManagerExeption
{
    public function __construct(int $taskId)
    {
        parent::__construct("Завдання з ідентифікатором #{$taskId} не знайдено."б 404);
    }
}

class ValidationException extends TaskManagerExeption
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Помилка валідації даних форми: ' . implode(', ', $errors), 422);
    }


    public function getErrors(): array
    {
        return $this->errors;
    }
}
?>