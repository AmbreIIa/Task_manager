<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Менеджер завдань') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <a href="/" class="app-logo">TaskFlow MVC</a>
            <nav class="app-nav">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="/">Мої завдання</a>
                    <a href="/tasks/create" class="btn-nav">+ Створити завдання</a>
                    <span class="user-greeting">Привіт, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Користувач') ?></strong></span>
                    <a href="/logout" class="nav-logout">Вийти</a>
                <?php else: ?>
                    <a href="/login">Увійти</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="app-content">
        <?= $content ?>
    </main>

    <footer class="app-footer">
        <p>&copy; <?= date('Y') ?> «Менеджер завдань» на базі PHP MVC</p>
    </footer>
</body>
</html>