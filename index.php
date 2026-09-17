<?php

    $appName = "Task Manager";
    $taskTitle = "Вивчи мову PHP";
    $taskTimeEstimate = 2;

    $IsComplete = false;
    
    function formatTitle($text, $maxLength = 20) {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength) . '...';
        } else {
            return $text;
        }
    }

    function getCurrentGreeting() {
        if (date('H') >= 6 && date('H') < 12) {
            return "Доброго ранку";
        } elseif (date('H') >= 12 && date('H') < 18) {
            return "Добрий день";
        } elseif (date('H') >= 18 && date('H') < 24) {
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
    
    <style>
        .task-done {
            color: green;
        }

        .task-pending {
            color: gray;
        }
    </style>

</head>
<body>

    <header>

        <h1><?= $appName ?></h1>
        <p><?= getCurrentGreeting() ?></p>

        <ul>
            <li class="<?= $IsComplete ? 'task-done' : 'task-pending' ?>">
                Завдання: <?= formatTitle($taskTitle) ?>
                <?php if ($IsComplete == true): ?>
                    ✔️ Виконано
                <?php else: ?>
                    🕒 В процесі
                <?php endif; ?>
            </li>

            <li>Кількість годин на виконання: <?= $taskTimeEstimate ?></li>
        </ul>

    </header>

    <main>


    </main>

</body>
</html>
