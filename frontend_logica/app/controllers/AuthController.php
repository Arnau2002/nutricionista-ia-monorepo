<?php
namespace App\Controllers;

use App\Models\AuthModel;

class AuthController 
{
    private AuthModel $model;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->model = new AuthModel();
    }

    public function showLogin(): void
    {
        $this->view('login', [
            'title' => 'Iniciar sesión',
        ]);
    }

    public function showRegister(): void
    {
        $this->view('register', [
            'title' => 'Registro',
        ]);
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->setFlash('Introduce usuario y contraseña.');
            header('Location: /?r=login');
            exit;
        }

        // Si la BBDD falla o el usuario/contraseña no coinciden,
        // verifyLogin devuelve null → tratamos todo igual.
        $user = $this->model->verifyLogin($email, $password);
        if (!$user) {
            $this->setFlash('Usuario o contraseña incorrecta.');
            header('Location: /?r=login');
            exit;
        }

        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['full_name'] ?: $user['email'];

        $this->setFlash('Bienvenido, ' . $_SESSION['username'] . ' 👋');
        header('Location: /?r=home');
        exit;
    }

    public function register(): void
    {
        $name      = trim($_POST['name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        // Validaciones
        if ($name === '' || $email === '' || $password === '' || $password2 === '') {
            $this->setFlash('Todos los campos son obligatorios.');
            header('Location: /?r=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('Email no válido.');
            header('Location: /?r=register');
            exit;
        }

        if ($password !== $password2) {
            $this->setFlash('Las contraseñas no coinciden.');
            header('Location: /?r=register');
            exit;
        }

        if (strlen($password) < 6) {
            $this->setFlash('La contraseña debe tener al menos 6 caracteres.');
            header('Location: /?r=register');
            exit;
        }

        // Crear usuario
        $result = $this->model->createUser($name, $email, $password);

        if (!$result['ok']) {
            if ($result['code'] === 'duplicate_email') {
                $this->setFlash('Este correo ya está registrado.');
            } else {
                $this->setFlash('Error al registrar el usuario.');
            }
            header('Location: /?r=register');
            exit;
        }


        $user = $this->model->findByEmail($email);

        if ($user) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['full_name'] ?: $user['email'];
        }

        $this->setFlash('¡Registro completado! Sesión iniciada automáticamente.');
        header('Location: /?r=home');
        exit;
    }


    public function logout(): void
    {
        session_destroy();
        header('Location: /?r=login');
        exit;
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $appName = (require __DIR__ . '/../../config/config.php')['app_name'];
        $flash   = $_SESSION['flash'] ?? null;
        if (isset($_SESSION['flash'])) unset($_SESSION['flash']);

        include __DIR__ . '/../Views/layout.php';
    }

    protected function setFlash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }
}
