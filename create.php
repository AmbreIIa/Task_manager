<?php

$errors = [];
$title = '';
$description = '';
$priority = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $priority = htmlspecialchars(trim($_POST['priority']));

    if (empty($title)){
        $errors[] = "Поле Назва є обов'язковим для заповнення!";
    }

    if (empty($description)){
        $errors[] = "Поле Опис є обов'язковим для заповнення!";
    }
    
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
</head>
<body>
    <style>
        .alert-danger {
            color: red;
        }
    </style>

    <header>
        <a href="index.php">Повернутись до списку</a>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif;?>

    <form action="create.php" method="POST">
        <p> Назва завдання: </p>
        <input name="title" value="<?= $title ?? ''?>">

        <p>Опис завдання:</p>
        <textarea name="description"><?= $descriotion ?? ''?></textarea>

        <p>Пріорітет:</p>
        <select name="priority">
            <option <?= (isset($priority) && $priority === 'Low') ? 'selected' : '' ?>>Low</option>
            <option <?= (isset($priority) && $priority === 'Medium') ? 'selected' : '' ?>>Medium</option>
            <option <?= (isset($priority) && $priority === 'High') ? 'selected' : '' ?>>High</option>
        </select><br>

        <button type="submit">Зберегти</button>
    </form>
</body>
</html>
