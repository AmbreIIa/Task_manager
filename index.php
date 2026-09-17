<?php

    $appName = "Task Manager";
    $taskTitle = "Вивчи основи PHP";
    $taskTimeEstimate = 2;

    $IsComplete = false;
    

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

        <ul>
            <li class="<?= $IsComplete ? 'task-done' : 'task-pending' ?>">
                Завдання: <?= $taskTitle ?>
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
