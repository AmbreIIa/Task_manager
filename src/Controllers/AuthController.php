<?php
declare(strict_types=1);

class AuthController extends BaseController
{

    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Вхід у систему — TaskFlow',
            'error'     => null,
        ]);
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $this->render('auth/login', [
                'pageTitle'   => 'Вхід у систему — TaskFlow',
                'error'       => 'Будь ласка, заповніть усі поля форми.',
                'oldUsername' => $username,
            ]);
            return;
        }

        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = htmlspecialchars($username);

        $this->redirect('/');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->redirect('/login');
    }
}