<?php
// frontend_logica/delete_basket.php

session_start();
header('Content-Type: application/json');

// 1. Configuración de Base de Datos
$host = 'nutricionista-mysql'; 
$db   = 'precios_comparados';
$user = 'root';
$pass = 'password_segura';

// 2. Comprobar si el usuario está logueado
$user_id = $_SESSION['user_id'] ?? 1; 

// 3. Recibir el ID de la cesta
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$baseket_id = $data['id'] ?? null;

if (!$baseket_id) {
    http_response_code(400);
    echo json_encode(['error' => 'No se proporcionó el ID de la cesta']);
    exit;
}

try {
    // 4. Conectar a MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5. Eliminar la cesta (asegurando que pertenece al usuario)
    $stmt = $pdo->prepare("DELETE FROM saved_baskets WHERE id = ? AND user_id = ?");
    $stmt->execute([$baseket_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Cesta eliminada correctamente']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cesta no encontrada o no tienes permiso']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en Base de Datos: ' . $e->getMessage()]);
}
?>
