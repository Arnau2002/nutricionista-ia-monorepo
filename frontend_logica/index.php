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
        
        case 'logout':
            session_destroy();
            (new AuthController())->showLogin();
        break;

        default:
            if (!isset($_SESSION['username'])){
                (new AuthController())->showLogin();
            } else {
                render('home', ['title' => 'Inicio']);      
            }
    }
    function render(string $view, array $data = []): void
    {
        $base = __DIR__ . '/app';
        $layout = $base . '/Views/layout.php';
        extract($data);
        $appName = (require __DIR__ . '/config/config.php')['app_name'];
        // Set var esperadas por el layout
        include $layout;
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
