<?php
/**
 * Proxy para Pollinations AI que inyecta la API Key de forma segura
 * desarrollado por Antigravity
 */

header('Content-Type: image/jpeg');

// 1. Obtener la API Key desde las variables de entorno de Docker
$apiKey = getenv('POLLINATIONS_API_KEY');

// DEBUG: Descomenta esto solo si quieres ver si la clave llega (¡cuidado con la privacidad!)
// file_put_contents('proxy_debug.log', date('Y-m-d H:i:s') . " - Key detectada: " . (empty($apiKey) ? 'NO' : 'SI') . "\n", FILE_APPEND);

// 2. Parámetros
$prompt = isset($_GET['prompt']) ? $_GET['prompt'] : 'healthy food';
$seed = isset($_GET['seed']) ? (int)$_GET['seed'] : rand(1, 100000);
$model = isset($_GET['model']) ? $_GET['model'] : 'flux';

// 3. Construir URL oficial de Pollinations (Endpoint para usuarios AUTENTICADOS)
$targetUrl = "https://gen.pollinations.ai/image/" . urlencode($prompt) . "?width=600&height=400&model=" . $model . "&seed=" . $seed . "&nologo=true";

// 4. Ejecución con Reintentos
$attempts = 0;
$maxAttempts = 2;
$success = false;

do {
    $attempts++;
    $ch = curl_init($targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "User-Agent: NutricionistaIA/1.0"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $success = true;
    } elseif ($attempts < $maxAttempts) {
        sleep(1); // Esperar un segundo antes de reintentar
    }
} while (!$success && $attempts < $maxAttempts);

// 5. Devolver resultado o fallback
if ($success) {
    header("Content-Type: image/jpeg");
    echo $response;
} else {
    // FALLBACK: Si todo falla, enviamos a Unsplash
    header('Location: https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop');
}
exit;
