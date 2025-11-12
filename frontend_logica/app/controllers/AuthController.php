<?php
    namespace App\Controllers;

    class AuthController 
    {

        public function showLogin(): void
        {
            $this->view('login', ['title' => 'Iniciar sesión']);
        }
        public function showRegister(): void
        {
            $this->view('register', ['title' => 'Iniciar sesión']);
        }
        protected function view(string $view, array $data = []): void
        {
            extract($data);
            $appName = (require __DIR__ . '/../../config/config.php')['app_name'];
            include __DIR__ . '/../Views/layout.php';
        }
    }

?>