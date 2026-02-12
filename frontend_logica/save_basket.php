<?php
// frontend_logica/save_basket.php

session_start();
header('Content-Type: application/json');

// 1. Configuración de Base de Datos (Tus credenciales reales)
$host = 'nutricionista-mysql'; 
$db   = 'precios_comparados';
$user = 'root';
$pass = 'password_segura';

// 2. Comprobar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no hay ID en sesión, intentamos usar el username o ponemos 1 (usuario demo)
    // Para el MVP, si no tienes sistema de login completo, usaremos 1.
    $user_id = $_SESSION['user_id'] ?? 1; 
} else {
    $user_id = $_SESSION['user_id'];
}

// 3. Recibir el JSON de la web
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos válidos']);
    exit;
}

try {
    // 4. Conectar a MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5. Preparar los datos
    $winner = $data['mejor_supermercado'] ?? 'Desconocido';
    
    // Calcular total dependiendo del ganador
    $total = 0.0;
    if ($winner === 'Mercadona') {
        $total = $data['cesta_mercadona']['total'];
    } elseif ($winner === 'Dia') {
        $total = $data['cesta_dia']['total'];
    } else {
        // En caso de empate, cogemos Mercadona por defecto
        $total = $data['cesta_mercadona']['total'];
    }

    // 6. Insertar en la tabla
    $stmt = $pdo->prepare("INSERT INTO saved_baskets (user_id, total_price, winner_store, json_data) VALUES (?, ?, ?, ?)");
    
    $stmt->execute([
        $user_id,
        $total,
        $winner,
        json_encode($data) // Guardamos todo el objeto JSON para poder releerlo después
    ]);

    // 7. Responder éxito
    echo json_encode([
        'status' => 'success', 
        'message' => 'Cesta guardada correctamente',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en Base de Datos: ' . $e->getMessage()]);
}
?>