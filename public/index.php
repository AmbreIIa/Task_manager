<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/../src/',
        __DIR__ . '/../src/Controllers/',
        __DIR__ . '/../src/Models/',
        __DIR__ . '/../src/Repositories/',
        __DIR__ . '/../src/Exceptions/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Старт сесії користувача
session_start();

// Ініціалізація маршрутизатора
$router = new Router();

// Реєстрація маршрутів для завдань
$router->get('/',                 [TaskController::class, 'index']);
$router->get('/tasks/create',     [TaskController::class, 'create']);
$router->post('/tasks/create',    [TaskController::class, 'store']);
$router->post('/tasks/complete',  [TaskController::class, 'complete']);
$router->post('/tasks/delete',    [TaskController::class, 'delete']);

// Реєстрація маршрутів автентифікації
$router->get('/login',            [AuthController::class, 'showLogin']);
$router->post('/login',           [AuthController::class, 'login']);
$router->get('/logout',           [AuthController::class, 'logout']);

// Запуск обробки запиту
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
?>