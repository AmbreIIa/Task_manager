<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TskRepository.php'; 
require_once __DIR__ . '/TaskRepository.php';
require_once __DIR__ . '/Exceptions.php';

// Перевірка авторизації
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Залежність оголошена через інтерфейс
$repo = new TaskRepository(__DIR__ . '/task.json');

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

    }
}

$tasks = $repo->findAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= $appName ?></title>
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
        <div class='alert-success'><?= htmlspecialchars($feedbackMessage) ?></div>
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

    <div class='stats'>
        Всього завдань: <?= count($tasks) ?> |
        Виконано: <?= $repo->countCompleted() ?> |
        Прострочено: <?= $repo->countOverdue() ?>
    </div

        <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true): ?>
            Привіт <?= htmlspecialchars($_SESSION['user_name'] ?? 'Користувач') ?>!
            <a href="logout.php">Вийти</a>
        <?php else: ?>
            Ви увійшли як Гість.
            <a href="login.php">Увійти</a>
        <?php endif; ?>
        
        <br>
        <a href="create.php">Додати нове завдання</a>

        <h1><?= $appName ?></h1>
        <p><?= getCurrentGreeting() ?></p>

      
        <div class="stats">
            Всього: <?= count($tasks) ?> |
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
