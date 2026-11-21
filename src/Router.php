<?php
declare(strict_types=1);

class Router {
    private array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $method = strtoupper($method);

        if(!isset($this->routes[$method][$path])) {
            $this->handleNotFound($method, $path);
            return;
        }

        [$controllerClass, $actionMethod] = $this->routes[$method][$path];

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Клас контролера'{$controllerClass}' не знайдено.");
        }

        $controller = new $controllerClass();

        if(!method_exists($controller, $actionMethod)) {
            throw new RuntimeException("Метод'{$actionMethod}' не знайдено в класі'{$controllerClass}'.");
        }

        $controller->$actionMethod();
    }

    private function handleNotFound(string $method, string $path): void
    {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="uk">
        <head>
            <meta charset="UTF-8">
            <title>404 — Сторінку не знайдено</title>
            <style>
                body { font-family: system-ui, sans-serif; background: #f8fafc; color: #1e293b; display: grid; place-content: center; min-height: 100vh; margin: 0; text-align: center; }
                h1 { font-size: 3rem; margin: 0 0 10px; color: #ef4444; }
                p { font-size: 1.1rem; color: #64748b; margin-bottom: 20px; }
                a { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; }
                a:hover { background: #2563eb; }
                code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <h1>404</h1>
            <p>Маршрут <code><?= htmlspecialchars($method) ?> <?= htmlspecialchars($path) ?></code> не зареєстровано в системі.</p>
            <a href="/">Повернутися на головну</a>
        </body>
        </html>
        <?php
    }
}
?>