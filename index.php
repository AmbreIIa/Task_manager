<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskStorageInterface.php';
require_once __DIR__ . '/TskRepository.php';
require_once __DIR__ . '/Exceptions.php';

// Емуляція сесії користувача (якщо модуль автентифікації ще не налаштовано)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Студент';
}

$currentUserId = (int) $_SESSION['user_id'];
$pdo = Database::getConnection();
$repo = new TaskRepository($pdo);

$formErrors = [];
$feedbackMessage = $_SESSION['feedback'] ?? null;
unset($_SESSION['feedback']);

// Обробка POST-запитів
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $title    = $_POST['title'] ?? '';
            $deadline = trim($_POST['deadline'] ?? '') ?: null;

            $repo->add($currentUserId, $title, $deadline);
            $_SESSION['feedback'] = 'Завдання успішно збережено в базі даних!';
            header('Location: index.php');
            exit;
        }

        if ($action === 'complete') {
            $id = (int) ($_POST['id'] ?? 0);
            $repo->complete($id, $currentUserId);
            $_SESSION['feedback'] = 'Статус завдання змінено на "Виконано"!';
            header('Location: index.php');
            exit;
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $repo->delete($id, $currentUserId);
            $_SESSION['feedback'] = 'Завдання видалено з бази даних!';
            header('Location: index.php');
            exit;
        }
    } catch (ValidationException $e) {
        $formErrors = $e->getErrors();
    } catch (TaskNotFoundException $e) {
        $feedbackMessage = 'Помилка: ' . $e->getMessage();
    } catch (Throwable $e) {
        $feedbackMessage = 'Критична помилка: ' . $e->getMessage();
    }
}

// Розрахунок параметрів пагінації
$perPage = 5;
$page = max(1, (int) ($_GET['page'] ?? 1));
$totalTasks = $repo->countByUser($currentUserId);
$totalPages = max(1, (int) ceil($totalTasks / $perPage));

if ($page > $totalPages && $totalTasks > 0) {
    $page = $totalPages;
}

$tasks = $repo->findAllByUser($currentUserId, $page, $perPage);
$completedCount = $repo->countCompletedByUser($currentUserId);
$overdueCount   = $repo->countOverdueByUser($currentUserId);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Менеджер завдань — Лабораторна №13</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 30px auto; padding: 0 15px; }
        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .task-card { border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .task-completed { background-color: #f9f9f9; text-decoration: line-through; color: #888; }
        .pagination { display: flex; gap: 8px; margin-top: 20px; align-items: center; }
        .pagination a, .pagination span { padding: 6px 12px; border: 1px solid #ccc; text-decoration: none; border-radius: 4px; color: #333; }
        .pagination .active { background-color: #007bff; color: white; border-color: #007bff; font-weight: bold; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Менеджер завдань (MySQL + PDO)</h1>
    <p>Авторизований користувач: <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong> (ID: <?= $currentUserId ?>)</p>

    <div class="stats">
        <span>Всього завдань: <?= $totalTasks ?></span>
        <span>Виконано: <?= $completedCount ?></span>
        <span>Прострочено: <?= $overdueCount ?></span>
    </div>

    <?php if ($feedbackMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($feedbackMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php" style="margin-bottom: 30px;">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Що потрібно зробити?" style="padding: 8px; width: 60%;" required>
        <input type="date" name="deadline" style="padding: 8px;">
        <button type="submit" style="padding: 8px 16px;">Додати</button>
    </form>

    <h2>Список завдань (Сторінка <?= $page ?> з <?= $totalPages ?>)</h2>

    <?php if (empty($tasks)): ?>
        <p>На цій сторінці завдань немає.</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="task-card <?= $task->isCompleted() ? 'task-completed' : '' ?>">
                <div>
                    <strong>#<?= $task->id ?> <?= htmlspecialchars($task->getTitle()) ?></strong>
                    <br>
                    <small>Дедлайн: <?= htmlspecialchars($task->getDeadline() ?? 'Не встановлено') ?> | Статус: <?= $task->getStatusLabel() ?></small>
                </div>
                <div style="display: flex; gap: 6px;">
                    <?php if (!$task->isCompleted()): ?>
                        <form method="POST" action="index.php" style="display:inline;">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="id" value="<?= $task->id ?>">
                            <button type="submit">✓</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="index.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $task->id ?>">
                        <button type="submit" onclick="return confirm('Видалити?')">✕</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Блок пагінації -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">« Назад</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">Вперед »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
