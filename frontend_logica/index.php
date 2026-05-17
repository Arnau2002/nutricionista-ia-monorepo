<?php
    declare(strict_types=1);

    use App\Controllers\AuthController;

    autoload();
    if (session_status() !== PHP_SESSION_ACTIVE) 
        session_start();

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (preg_match('#^/api/(planificar-menu|comparar-lista-compra|chat-receta)$#', $requestPath, $matches)) {
        proxyToBackend('/' . $matches[1]);
        exit;
    }

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

        // ==========================================
        // RECUPERACIÓN DE CONTRASEÑA
        // ==========================================
        case 'forgot_password':
            (new AuthController())->showForgotPassword();
        break;

        case 'process_forgot_password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                (new AuthController())->processForgotPassword();
            } else {
                header('Location: /?r=forgot_password');
            }
        break;

        case 'reset_password':
            (new AuthController())->showResetPassword();
        break;

        case 'process_reset_password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                (new AuthController())->processResetPassword();
            } else {
                header('Location: /?r=login');
            }
        break;
        // ==========================================

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

        case 'planificador':
            if (!isset($_SESSION['username'])) {
                (new AuthController())->showLogin();
            } else {
                render('planificador', ['title' => 'Planificador IA']);
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

    function proxyToBackend(string $endpoint): void
    {
        $backendBase = getenv('BACKEND_API_URL') ?: 'http://backend-logica:8000';
        $targetUrl = rtrim($backendBase, '/') . $endpoint;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $rawBody = file_get_contents('php://input') ?: '';

        $headers = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if ($contentType !== '') {
            $headers[] = 'Content-Type: ' . $contentType;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if ($accept !== '') {
            $headers[] = 'Accept: ' . $accept;
        }

        $responseContentType = 'application/json; charset=utf-8';
        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($method !== 'GET' && $method !== 'HEAD') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
        }
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $headerLine) use (&$responseContentType) {
            $length = strlen($headerLine);
            if (stripos($headerLine, 'Content-Type:') === 0) {
                $responseContentType = trim(substr($headerLine, strlen('Content-Type:')));
            }
            return $length;
        });

        $response = curl_exec($ch);
        if ($response === false) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'No se pudo conectar con el backend',
                'detail' => curl_error($ch)
            ]);
            curl_close($ch);
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        http_response_code($httpCode > 0 ? $httpCode : 502);
        header('Content-Type: ' . $responseContentType);
        echo $response;
    }
?>