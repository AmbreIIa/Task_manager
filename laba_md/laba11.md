### Код TaskStorageInterface ('TaskStorageInterface.php')

```php
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
```

### Код TimestampableTrait ('TimestampableTrait.php')

```php
<?php
declare(strict_types=1);

trait TimestampableTrait
{
    private string $createdAt;
    private ?string $updatedAt = null;

    public function initTimestamps(?string $createdAt = null, ?string $updatedAt = null): void
    {
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updateAt = $updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}

?>
```

### Код Exceptions ('Exceptions.php')

```php
<?php
declare(strict_types=1);

class TaskManagerExeption extends RuntimeException {}

class TaskNotFoundException extends TaskManagerExeption
{
    public function __construct(int $taskId)
    {
        parent::__construct("Завдання з ідентифікатором #{$taskId} не знайдено.", 404);
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
```

#### Оновлені

### Код Task ('Task.php')
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/TimestampableTrait.php';

class Task
{
    use TimestampableTrait;

    public function __construct(
        public readonly int $id,
        private string $title,
        private bool $isCompleted = false,
        private ?string $deadline = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
    ) {
        $this->initTimestamps($createdAt, $updatedAt);
    }

    // --- Геттери ---

    public function getId(): int
    {
        return $this->id;
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

    // --- Бізнес-методи ---

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
        $this->touch(); // Оновлюємо мітку часу модифікації
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

    // --- Серіалізація / Десеріалізація ---

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'isCompleted' => $this->isCompleted,
            'deadline'    => $this->deadline,
            'createdAt'   => $this->getCreatedAt(),
            'updatedAt'   => $this->getUpdatedAt(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:          (int) $data['id'],
            title:       (string) $data['title'],
            isCompleted: (bool) ($data['isCompleted'] ?? false),
            deadline:    $data['deadline'] ?? null,
            createdAt:   $data['createdAt'] ?? null,
            updatedAt:   $data['updatedAt'] ?? null,
        );
    }
}
```
### Код TaskRepository ('TaskRepository.php')
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskStorageInterface.php';
require_once __DIR__ . '/Exceptions.php';

class TaskRepository implements TaskStorageInterface
{
    /** @var Task[] */
    private array $tasks = [];

    public function __construct(private string $storageFile)
    {
        $this->load();
    }

    public function findAll(): array
    {
        return $this->tasks;
    }

    public function findById(int $id): ?Task
    {
        foreach ($this->tasks as $task) {
            if ($task->getId() === $id) {
                return $task;
            }
        }
        return null;
    }

    public function add(string $title, ?string $deadline = null): Task
    {
        $errors = [];
        $cleanTitle = trim($title);

        if ($cleanTitle === '') {
            $errors[] = 'Назва завдання є обов\'язковою для заповнення.';
        } elseif (mb_strlen($cleanTitle) < 3) {
            $errors[] = 'Назва завдання повинна містити щонайменше 3 символи.';
        }

        if ($deadline !== null && $deadline !== '') {
            $timestamp = strtotime($deadline);
            if ($timestamp === false) {
                $errors[] = 'Вказано некоректну дату дедлайну.';
            }
        } else {
            $deadline = null;
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $task = new Task(
            id:          $this->generateNextId(),
            title:       $cleanTitle,
            isCompleted: false,
            deadline:    $deadline,
        );

        $this->tasks[] = $task;
        $this->save();

        return $task;
    }

    public function complete(int $id): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            throw new TaskNotFoundException($id);
        }

        $task->complete();
        $this->save();
        return true;
    }

    public function delete(int $id): bool
    {
        foreach ($this->tasks as $key => $task) {
            if ($task->getId() === $id) {
                unset($this->tasks[$key]);
                $this->tasks = array_values($this->tasks);
                $this->save();
                return true;
            }
        }

        throw new TaskNotFoundException($id);
    }

    public function countCompleted(): int
    {
        return count(array_filter($this->tasks, fn(Task $t) => $t->isCompleted()));
    }

    public function countOverdue(): int
    {
        return count(array_filter($this->tasks, fn(Task $t) => $t->isOverdue()));
    }

    private function generateNextId(): int
    {
        if (empty($this->tasks)) {
            return 1;
        }
        return max(array_map(fn(Task $t) => $t->getId(), $this->tasks)) + 1;
    }

    private function load(): void
    {
        if (!file_exists($this->storageFile)) {
            $this->tasks = [];
            return;
        }

        $json = file_get_contents($this->storageFile);
        $data = json_decode($json, true) ?? [];
        $this->tasks = array_map(fn(array $item) => Task::fromArray($item), $data);
    }

    private function save(): void
    {
        $data = array_map(fn(Task $t) => $t->toArray(), $this->tasks);
        file_put_contents(
            $this->storageFile,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
```

### Код index ('index.php')
```php
?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskStorageInterface.php';
require_once __DIR__ . '/TaskRepository.php';
require_once __DIR__ . '/Exceptions.php';

// Перевірка авторизації
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Залежність оголошена через інтерфейс
$repo = new TaskRepository(__DIR__ . '/tasks.json');

$formErrors = [];
$feedbackMessage = $_SESSION['feedback'] ?? null;
unset($_SESSION['feedback']);

// Обробка дій користувача
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $title    = $_POST['title'] ?? '';
            $deadline = trim($_POST['deadline'] ?? '') ?: null;

            $repo->add($title, $deadline);
            $_SESSION['feedback'] = 'Завдання успішно додано!';
            header('Location: index.php');
            exit;
        }

        if ($action === 'complete') {
            $id = (int) ($_POST['id'] ?? 0);
            $repo->complete($id);
            $_SESSION['feedback'] = 'Статус завдання оновлено!';
            header('Location: index.php');
            exit;
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $repo->delete($id);
            $_SESSION['feedback'] = 'Завдання видалено!';
            header('Location: index.php');
            exit;
        }
    } catch (ValidationException $e) {
        // Перехоплюємо помилки валідації форми
        $formErrors = $e->getErrors();
    } catch (TaskNotFoundException $e) {
        // Перехоплюємо спробу маніпуляції неіснуючим завданням
        $formErrors = [$e->getMessage()];
    } catch (TaskManagerException $e) {
        // Загальний доменний виняток
        $formErrors = ['Помилка програми: ' . $e->getMessage()];
    } catch (Throwable $e) {
        // Непередбачена системна помилка
        $formErrors = ['Критичний збій обробки запиту. Зверніться до техпідтримки.'];
    } finally {
        // Код у finally виконується незалежно від наявності чи відсутності винятку
        // Наприклад, запис системного аудиту або закриття додаткових ресурсів
    }
}

$tasks = $repo->findAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Менеджер завдань (ООП: Інтерфейси та Винятки)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 20px; color: #222; }
        .stats { margin-bottom: 20px; padding: 12px; background: #f1f3f5; border-radius: 6px; font-weight: 500; }
        .alert-error { background: #ffe3e3; border: 1px solid #ffa8a8; color: #c92a2a; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .alert-error ul { margin: 6px 0 0 20px; padding: 0; }
        .alert-success { background: #d3f9d8; border: 1px solid #8ce99a; color: #2b8a3e; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .task { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #eee; }
        .task-meta { font-size: 0.85em; color: #868e96; margin-top: 4px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 0.8em; color: #fff; font-weight: bold; }
        .badge-success { background: #40c057; }
        .badge-danger  { background: #fa5252; }
        .badge-primary { background: #228be6; }
        .form-add { display: flex; gap: 8px; margin-bottom: 25px; }
        .form-add input[type="text"] { flex-grow: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; }
        .form-add input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .form-add button { padding: 8px 16px; background: #228be6; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .actions button { padding: 4px 8px; margin-left: 4px; cursor: pointer; border-radius: 4px; border: 1px solid #ced4da; }
    </style>
</head>
<body>

<h1>Менеджер завдань</h1>
<p>Користувач: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Адміністратор') ?></strong> | <a href="login.php">Вийти</a></p>

<?php if ($feedbackMessage): ?>
    <div class="alert-success"><?= htmlspecialchars($feedbackMessage) ?></div>
<?php endif; ?>

<?php if (!empty($formErrors)): ?>
    <div class="alert-error">
        <strong>Увага! Виявлено помилки під час виконання операції:</strong>
        <ul>
            <?php foreach ($formErrors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="index.php" class="form-add">
    <input type="hidden" name="action" value="add">
    <input type="text" name="title" placeholder="Що потрібно зробити (мінімум 3 символи)?" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    <input type="date" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
    <button type="submit">Додати</button>
</form>

<div class="stats">
    Всього завдань: <?= count($tasks) ?> |
    Виконано: <?= $repo->countCompleted() ?> |
    Прострочено: <?= $repo->countOverdue() ?>
</div>

<div class="tasks-list">
    <?php if (empty($tasks)): ?>
        <p>Список завдань порожній.</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="task">
                <div>
                    <div>
                        <strong><?= htmlspecialchars($task->getTitle()) ?></strong>
                        <?php if ($task->getDeadline()): ?>
                            <small>(Дедлайн: <?= htmlspecialchars($task->getDeadline()) ?>)</small>
                        <?php endif; ?>
                    </div>
                    <div class="task-meta">
                        Створено: <?= htmlspecialchars($task->getCreatedAt()) ?>
                        <?php if ($task->getUpdatedAt()): ?>
                            | Оновлено: <?= htmlspecialchars($task->getUpdatedAt()) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="actions">
                    <?php
                    $badgeClass = match ($task->getStatusLabel()) {
                        'Виконано'   => 'badge-success',
                        'Прострочено' => 'badge-danger',
                        default       => 'badge-primary',
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $task->getStatusLabel() ?></span>

                    <?php if (!$task->isCompleted()): ?>
                        <form method="POST" action="index.php" style="display:inline;">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="id" value="<?= $task->getId() ?>">
                            <button type="submit" title="Позначити виконаним">✓</button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" action="index.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $task->getId() ?>">
                        <button type="submit" title="Видалити">✕</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
```

## Скріншоти

![](Screnshots/laba11_1.png)
![](Screnshots/laba11_2.png)

## Те шо знаходиться у tasl.json
```json
[
    {
        "id": 1,
        "title": "123123",
        "isCompleted": true,
        "deadline": null,
        "createdAt": "2026-10-30 19:49:00",
        "udatedAt": null
    },
    {
        "id": 2,
        "title": "rrrr",
        "isCompleted": false,
        "deadline": "2000-02-22",
        "createdAt": "2026-10-30 19:49:00",
        "udatedAt": null
    },
    {
        "id": 3,
        "title": "45623",
        "isCompleted": true,
        "deadline": "2027-05-22",
        "createdAt": "2026-10-30 19:49:00",
        "udatedAt": null
    },
    {
        "id": 4,
        "title": "weqweqe",
        "isCompleted": false,
        "deadline": "2029-02-22",
        "createdAt": "2026-10-30 19:49:00",
        "udatedAt": null
    },
    {
        "id": 5,
        "title": "sgfsdh",
        "isCompleted": false,
        "deadline": "1945-02-01",
        "createdAt": "2026-10-30 19:49:00",
        "udatedAt": null
    }
]
```

## Відповіді на контрольні запитання


# 1. Інтерфейс vs Абстрактний клас
Абстрактний клас може містити реалізацію методів і стан, а інтерфейс — це лише суворий контракт. Для `TaskStorageInterface` обрано інтерфейс, оскільки різним сховищам потрібні лише спільні правила, а не спільний код.

# 2. Переваги слабого зв’язку (Loose Coupling)
Програма залежить від контракту, а не від конкретного класу. Для переходу на MySQL (PDO) достатньо створити клас `PdoTaskRepository` (який імплементує інтерфейс) і змінити лише один рядок ініціалізації в `index.php`.

# 3. Горизонтальне перевикористання (Трейт)
PHP не підтримує множинне наслідування. `TimestampableTrait` дозволяє вбудувати готовий функціонал дат у будь-який клас без дублювання коду та створення штучного предка.

# 4. Черговість у блоках catch
Перевірка йде зверху вниз. Спочатку треба ловити конкретні помилки (`ValidationException`). Якщо загальний `Throwable` поставити першим, він перехопить усе, і до конкретних помилок черга не дійде.

# 5. Блок finally
Виконується завжди, незалежно від успіху чи виникнення помилок у `try/catch`. Використовується для гарантованого звільнення ресурсів: закриття файлів, з'єднань з БД або зняття блокувань.
