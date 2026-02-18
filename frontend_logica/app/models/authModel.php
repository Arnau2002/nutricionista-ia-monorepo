<?php
namespace App\Models;

use PDO;
use PDOException;

class AuthModel
{
    private PDO $pdo;

    public function __construct()
    {
        // CONFIGURACIÓN DIRECTA PARA DOCKER
        // Usamos las mismas credenciales que en dashboard.php y save_basket.php
        $host = 'nutricionista-mysql'; 
        $db   = 'precios_comparados';
        $user = 'root';
        $pass = 'password_segura';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Si falla la conexión, paramos todo y avisamos
            die("Error crítico de conexión a Base de Datos (AuthModel): " . $e->getMessage());
        }
    }

    

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            $this->logPdoError('findByEmail', $e);
            return null;
        }
    }

    /**
     * Crea un usuario.
     *
     * @return array [ 'ok' => bool, 'code' => string|null ]
     *         code puede ser:
     *           - 'duplicate_email'
     *           - 'db_error'
     */
    public function createUser(string $name, string $email, string $password): array
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (full_name, email, password_hash, role, created_at)
                VALUES (?, ?, ?, "user", NOW())';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$name, $email, $hash]);

            return [
                'ok'   => true,
                'code' => null,
            ];
        } catch (PDOException $e) {
            $this->logPdoError('createUser', $e);

            // 23000 = violación de constraint (UNIQUE, FK, etc.)
            if ((int) $e->getCode() === 23000) {
                return [
                    'ok'   => false,
                    'code' => 'duplicate_email',
                ];
            }

            return [
                'ok'   => false,
                'code' => 'db_error',
            ];
        }
    }

    public function verifyLogin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE user_id = ?');
            $stmt->execute([$user['user_id']]);
        } catch (PDOException $e) {
            $this->logPdoError('verifyLogin.updateLastLogin', $e);
            // no rompemos el login si falla esto
        }

        return $user;
    }

    private function logPdoError(string $context, PDOException $e): void
    {
        // Por si quieres ver qué está pasando exactamente
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $msg = sprintf(
            "[%s] [%s] SQLSTATE %s: %s\n",
            date('Y-m-d H:i:s'),
            $context,
            $e->getCode(),
            $e->getMessage()
        );

        @file_put_contents($logDir . '/db.log', $msg, FILE_APPEND);
    }
}
