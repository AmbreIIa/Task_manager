<?php

    $tasks = [
        [
            'id' => 1,
            'title' => 'Виконати лабораторну роботу №5',
            'priority' => 'High',
            'is_complite' => false,
            'task_estimate' => 2
        ],
        [
            'id' => 2,
            'title' => 'Підготувати звіт з практики',
            'priority' => 'Medium',
            'is_complite' => true,
            'task_estimate' => 4
        ],
        [
            'id' => 3,
            'title' => 'Виконати сиксевен сиксевен разів',
            'priority' => 'Low',
            'is_complite' => false,
            'task_estimate' => 1
        ]
    ];


    $appName = "Task Manager";


    
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
        <a href="create.php">Додати нове завдання</a>

        <h1><?= $appName ?></h1>
        <p><?= getCurrentGreeting() ?></p>

        <ul>
            <?php foreach ($tasks as $task): ?>
                <li class=" <?=$task['is_complite'] ? 'task-done': 'task-pending' ?>">
                    Завдання: <?= formatTitle($task['title'],100) ?>
                    <?php if ($task['is_complite']): ?>
                        ✔️ Виконано
                    <?php else: ?>
                        🕒 В процесі
                    <?php endif; ?>
                </li>

                <li>Кількість годин на виконання: <?= $task['task_estimate'] ?></li>
            <?php endforeach; ?>
        </ul>

    </header>

    <main>


    </main>

</body>
</html>
