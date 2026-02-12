<?php
    declare(strict_types=1);

    use App\Controllers\AuthController;

    autoload();
    if (session_status() !== PHP_SESSION_ACTIVE) 
        session_start();

    $route = $_GET['r'] ?? 'home';

    switch ($route) {
        // Páginas públicas
        case 'home':
            render('home', ['title' => 'Inicio']);
        break;

        case 'login':
            (new AuthController())->showLogin();
        break;

        case 'register':
            (new AuthController())->showRegister();
        break;

        // Acciones de autenticación (POST)
        case 'auth.login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                (new AuthController())->login();
            } else {
                header('Location: /?r=login');
            }
        break;

        case 'auth.register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                (new AuthController())->register();
            } else {
                header('Location: /?r=register');
            }
        break;

        case 'logout':
            session_destroy();
            (new AuthController())->showLogin();
        break;

        // Guardar preferencias
        case 'preferences.save':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                (new AuthController())->savePreferences();
            } else {
                http_response_code(405);
                echo 'Método no permitido';
            }
        break;

        
        case 'dashboard':
            if (!isset($_SESSION['username'])) {
                (new AuthController())->showLogin();
            } else {
                render('dashboard', ['title' => 'Mi Historial']);
            }
        break;
        // ---------------------------------------

        default:
            if (!isset($_SESSION['username'])){
                (new AuthController())->showLogin();
            } else {
                render('home', ['title' => 'Inicio']);      
            }
    }

    // --- FUNCIONES DEL SISTEMA ---

    function render(string $view, array $data = []): void
    {
        $base = __DIR__ . '/app';
        $layout = $base . '/Views/layout.php';
        $configPath = __DIR__ . '/config/config.php';

        // Manejo de errores si falta configuración
        $appName = 'Nutricionista IA';
        if (file_exists($configPath)) {
            $config = require $configPath;
            $appName = $config['app_name'] ?? 'Nutricionista IA';
        }

        // Recuperar flash de sesión si existe
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        extract($data);
        
        // Importante: El layout debe saber qué vista cargar.
        // Normalmente el layout.php tiene un "include" dentro.
        if (file_exists($layout)) {
            include $layout;
        } else {
            // Fallback por si no hay layout (útil para depurar)
            $viewPath = $base . "/Views/$view.php";
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "Error: Vista '$view' no encontrada.";
            }
        }
    }

    function autoload(): void
    {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $base_dir = __DIR__ . '/app/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) require $file;
        });
    }
?>