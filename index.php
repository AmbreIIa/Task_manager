<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TskRepository.php'; 

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$repo = new TaskRepository(__DIR__ . '/task.json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title    = trim($_POST['title'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '') ?: null;
        if (!empty($title)) {
            $repo->add($title, $deadline);
        }
    }

    if ($action === 'complete') {
        $repo->complete((int) ($_POST['id'] ?? 0));
    }

    if ($action === 'delete') {
        $repo->delete((int) ($_POST['id'] ?? 0));
    }

    header('Location: index.php');
    exit;
}


$tasks = $repo->findAll();

$appName = "Task Manager";

function formatTitle($text, $maxLength = 20) {
    if (strlen($text) > $maxLength) {
        return substr($text, 0, $maxLength) . '...';
    } else {
        return $text;  
    } 
}

function getCurrentGreeting() {
    $hour = (int) date('H');
    if ($hour >= 6 && $hour < 12) {
        return "Доброго ранку";
    } elseif ($hour >= 12 && $hour < 18) {
        return "Добрий день";
    } elseif ($hour >= 18 && $hour < 24) {
        return "Добрий вечір";
    } else {
        return "Доброї ночі";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= $appName ?></title>
    <style>
        .task-done { color: green; }
        .task-pending { color: gray; }
        .task-overdue { color: red; }
        .stats { margin: 15px 0; font-weight: bold; }
    </style>
</head>
<body>

    <header>
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

        <ul>
            <?php foreach ($tasks as $task): ?>
                <?php 
                    
                    $statusClass = 'task-pending';
                    if ($task->isCompleted()) {
                        $statusClass = 'task-done';
                    } elseif ($task->isOverdue()) {
                        $statusClass = 'task-overdue';
                    }
                ?>
                <li class="<?= $statusClass ?>">
                    Завдання: <?= formatTitle($task->getTitle(), 100) ?> 
                    <?php if ($task->getDeadline()): ?>
                        (Дедлайн: <?= $task->getDeadline() ?>)
                    <?php endif; ?>
                    — <strong><?= $task->getStatusLabel() ?></strong>

                   
                    <?php if (!$task->isCompleted()): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="id" value="<?= $task->getId() ?>">
                            <button type="submit">Виконати</button>
                        </form>
                    <?php endif; ?>

                    
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $task->getId() ?>">
                        <button type="submit" onclick="return confirm('Ви впевнені?')">Видалити</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

    </header>

    <main>
    </main>

</body>
</html>
