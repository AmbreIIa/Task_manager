<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    <header>
        <a href="index.php">Повернутись до списку</a>
    </header>
    
    <form action="create.php" method="GET">
        <p> Назва завдання: </p>
        <input name="title">

        <p>Опис завдання:</p>
        <textarea name="description"></textarea>

        <p>Пріорітет:</p>
        <select name="priority">
            <option>Low</option>
            <option>Medium</option>
            <option>High</option>
        </select><br>

        <button type="submit">Зберегти</button>
    </form>
</body>
</html>
