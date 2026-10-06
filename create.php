<?php

$allowedExt = ['jpg', 'png'];
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
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK){
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];

        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($fileSize > (2*1024*1024)) {
        $errors[] = "Файл завеликий!";
        }
        if (!in_array($fileExt, $allowedExt)){
            $errors[] = "Недозволений формат файлу!";
        }
    }else {
        $errors[] = "Виберіть файл зображення!";
    }
    
    if (empty($errors)) {
        $upload = 'uploads/';

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/' . basename($_FILES['avatar']['name']))){
            $SuccMEsage = "Файл успішно завантажено!";
        } else {
            $errors[] = "Помилка при збереженні фалу!"; 
    }
    }

  


    echo "<pre>";
    var_dump($_POST);
    var_dump($_FILES);
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

    <form action="create.php" method="POST" enctype="multipart/form-data">
                
        <p> Назва завдання: </p>
        <input name="title" value="<?= $title ?? ''?>">

        <p>Опис завдання:</p>
        <textarea name="description"><?= $description ?? ''?></textarea>

        <p>Пріорітет:</p>
        <select name="priority">
            <option <?= (isset($priority) && $priority === 'Low') ? 'selected' : '' ?>>Low</option>
            <option <?= (isset($priority) && $priority === 'Medium') ? 'selected' : '' ?>>Medium</option>
            <option <?= (isset($priority) && $priority === 'High') ? 'selected' : '' ?>>High</option>
        </select><br>

        <p>Зображення:</p>
        <input type="file" name="avatar" accept="image/png, image/jpeg"><br>

        <br><button type="submit">Зберегти</button>
    </form>
</body>
</html>
