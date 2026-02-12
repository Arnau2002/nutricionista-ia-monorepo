<?php
// app/Views/dashboard.php

// 1. Conexión a BD
$host = 'nutricionista-mysql';
$db   = 'precios_comparados';
$user = 'root';
$pass = 'password_segura';

$cestas = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'] ?? 1;

    // Consulta: ordenamos por fecha descendente (lo más nuevo arriba)
    $stmt = $pdo->prepare("SELECT * FROM saved_baskets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $cestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold;'>Error de conexión: " . $e->getMessage() . "</div>";
}
?>

<style>
    .dashboard-container {
        color: #333; /* Texto gris muy oscuro (casi negro) */
    }
    
    .history-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-family: sans-serif;
    }
    
    .history-table th {
        background-color: #2c3e50; /* Cabecera oscura */
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: bold;
    }
    
    .history-table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        color: #000; /* NEGRO PURO para los datos */
        font-size: 0.95rem;
    }
    
    .history-table tr:hover {
        background-color: #f1f2f6; /* Efecto hover */
    }

    /* Estilo para el visor de JSON (estilo código) */
    .json-box {
        background-color: #2d3436; /* Fondo oscuro tipo editor de código */
        color: #81ecec;            /* Texto claro fosforito */
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.9em;
        white-space: pre-wrap;     /* Mantiene los saltos de línea */
        max-height: 400px;
        border: 1px solid #000;
    }

    .btn-ver {
        background-color: #0984e3;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-ver:hover { background-color: #0769b5; }
</style>

<div class="dashboard-container">
    <h2 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">📊 Mi Historial de Compras</h2>
    
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        
        <?php if (empty($cestas)): ?>
            <p style="text-align: center; color: #555; font-size: 1.1em;">
                Aún no has guardado ninguna cesta. <br><br>
                <a href="/?r=home" style="color: #0984e3; font-weight: bold;">¡Haz tu primera búsqueda aquí!</a>
            </p>
        <?php else: ?>

            <table class="history-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Ganador</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cestas as $cesta): ?>
                        <?php 
                            $winner = htmlspecialchars($cesta['winner_store']);
                            // Color específico según el ganador
                            $colorGanador = ($winner == 'Mercadona') ? '#009432' : '#EA2027'; 
                        ?>
                        <tr>
                            <td>
                                <?php echo date('d/m/Y', strtotime($cesta['created_at'])); ?> 
                                <small style="color:#666; margin-left:5px;">(<?php echo date('H:i', strtotime($cesta['created_at'])); ?>)</small>
                            </td>
                            <td style="font-weight: bold; color: <?php echo $colorGanador; ?>;">
                                <?php echo $winner; ?>
                            </td>
                            <td style="font-weight: bold;">
                                <?php echo number_format($cesta['total_price'], 2); ?> €
                            </td>
                            <td>
                                <button class="btn-ver" onclick='verDetalles(<?php echo $cesta["json_data"]; ?>)'>
                                    👁️ Ver Detalles
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>

    <div id="detalle-cesta" style="margin-top: 30px; display:none; border-top: 3px solid #0984e3; padding-top: 20px;">
        <h3 style="color: #333;">📝 Detalles de la Cesta Seleccionada</h3>
        <p style="color: #666; margin-bottom: 10px;">Datos técnicos guardados por la IA:</p>
        <pre id="json-viewer" class="json-box"></pre>
    </div>
</div>

<script>
function verDetalles(json) {
    const visor = document.getElementById('detalle-cesta');
    const contenido = document.getElementById('json-viewer');
    
    visor.style.display = 'block';
    
    // Convertimos el JSON a texto bonito con indentación de 4 espacios
    contenido.textContent = JSON.stringify(json, null, 4);
    
    // Scroll suave hacia los detalles
    visor.scrollIntoView({behavior: "smooth"});
}
</script>