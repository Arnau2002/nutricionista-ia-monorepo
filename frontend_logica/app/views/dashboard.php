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

    // Consulta: ordenamos por fecha descendente
    $stmt = $pdo->prepare("SELECT * FROM saved_baskets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $cestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div style='color:red; font-weight:bold;'>Error de conexión: " . $e->getMessage() . "</div>";
}
?>

<style>
    .dashboard-container { color: #333; font-family: sans-serif; }
    
    /* TABLA DE HISTORIAL */
    .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .history-table th { background-color: #2c3e50; color: white; padding: 12px; text-align: left; }
    .history-table td { padding: 12px; border-bottom: 1px solid #ddd; color: #000; }
    .history-table tr:hover { background-color: #f1f2f6; }

    .btn-ver {
        background-color: #0984e3; color: white; border: none; padding: 6px 12px; 
        border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .btn-ver:hover { background-color: #0769b5; }

    /* ZONA DE DETALLES (Escondida por defecto) */
    #detalle-cesta {
        margin-top: 30px; display: none; 
        border-top: 3px solid #0984e3; padding-top: 20px;
        animation: fadeIn 0.5s;
    }

    /* TARJETAS DE SUPERMERCADO (Copiadas del Home) */
    .comparison-row { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
    
    .super-card {
        flex: 1; min-width: 300px; padding: 15px; border-radius: 8px; 
        border: 1px solid #ddd; position: relative;
    }

    .card-mercadona { background: #f4fbf7; border-top: 4px solid #007a3e; }
    .card-mercadona h3 { color: #007a3e; border-bottom: 2px solid #007a3e; }

    .card-dia { background: #fff5f6; border-top: 4px solid #d50032; }
    .card-dia h3 { color: #d50032; border-bottom: 2px solid #d50032; }

    .price-tag { font-size: 1.4em; margin: 10px 0; font-weight: bold; }

    .prod-item { border-bottom: 1px solid #eee; padding: 8px 0; }
    .prod-name { font-weight: bold; font-size: 0.95em; }
    .prod-meta { color: #555; font-size: 0.85em; display: flex; justify-content: space-between; }
    
    .missing-box { 
        margin-top: 10px; padding-top: 10px; border-top: 2px dashed #ecc; 
        color: #c0392b; font-size: 0.9em; 
    }

    .winner-banner {
        background: #d4edda; color: #155724; padding: 15px; 
        border-radius: 5px; text-align: center; margin-bottom: 10px; 
        border: 1px solid #c3e6cb; font-size: 1.1em;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="dashboard-container">
    <h2 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">📊 Mi Historial de Compras</h2>
    
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        
        <?php if (empty($cestas)): ?>
            <p style="text-align: center; color: #555; padding: 20px;">
                Aún no tienes historial. <a href="/?r=home">¡Haz tu primera búsqueda!</a>
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
                            $colorGanador = ($winner == 'Mercadona') ? '#009432' : '#EA2027'; 
                            // Escapamos el JSON para poder pasarlo a JS sin romper nada
                            $jsonSafe = htmlspecialchars($cesta['json_data'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($cesta['created_at'])); ?></td>
                            <td style="font-weight: bold; color: <?php echo $colorGanador; ?>;"><?php echo $winner; ?></td>
                            <td style="font-weight: bold;"><?php echo number_format($cesta['total_price'], 2); ?> €</td>
                            <td>
                                <button class="btn-ver" data-json='<?php echo $jsonSafe; ?>' onclick="cargarDetalle(this)">
                                    👁️ Ver Detalles
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>

    <div id="detalle-cesta">
        <h3 style="color: #333;">📝 Detalles de la Comparativa</h3>
        
        <div id="detail-banner" class="winner-banner"></div>

        <div class="comparison-row">
            <div class="super-card card-mercadona">
                <h3>Mercadona</h3>
                <div id="m-price" class="price-tag">0.00 €</div>
                <div id="m-list"></div>
                <div id="m-missing"></div>
            </div>

            <div class="super-card card-dia">
                <h3>Dia</h3>
                <div id="d-price" class="price-tag">0.00 €</div>
                <div id="d-list"></div>
                <div id="d-missing"></div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDetalle(btn) {
    // Recuperamos el JSON del atributo data-json
    const jsonStr = btn.getAttribute('data-json');
    if (!jsonStr) return;

    try {
        const data = JSON.parse(jsonStr);
        renderizarVista(data);
    } catch (e) {
        console.error("Error parseando JSON", e);
        alert("Error al leer los datos de la cesta.");
    }
}

function renderizarVista(data) {
    const visor = document.getElementById('detalle-cesta');
    visor.style.display = 'block';

    // 1. Banner Ganador
    const banner = document.getElementById('detail-banner');
    banner.innerHTML = `🏆 Ganador: <strong>${data.mejor_supermercado}</strong> | Ahorro: <strong>${data.ahorro_total} €</strong>`;
    
    // Cambiar color del banner según ganador
    if (data.mejor_supermercado === 'Dia') {
        banner.style.background = "#fadbd8";
        banner.style.color = "#721c24";
        banner.style.borderColor = "#f5c6cb";
    } else {
        banner.style.background = "#d4edda";
        banner.style.color = "#155724";
        banner.style.borderColor = "#c3e6cb";
    }

    // 2. Renderizar Columnas
    renderColumna(data.cesta_mercadona, 'm-price', 'm-list', 'm-missing');
    renderColumna(data.cesta_dia, 'd-price', 'd-list', 'd-missing');

    // 3. Scroll suave
    visor.scrollIntoView({behavior: "smooth"});
}

function renderColumna(cesta, idPrice, idList, idMissing) {
    // Precio Total
    document.getElementById(idPrice).innerText = cesta.total + " €";
    
    // Lista de Productos
    const listContainer = document.getElementById(idList);
    listContainer.innerHTML = cesta.productos_encontrados.map(p => `
        <div class="prod-item">
            <div class="prod-name">${p.nombre}</div>
            <div class="prod-meta">
                <span>${p.precio}€ / ${p.unidad}</span>
                ${p.final_score < 0.6 ? '<span title="Coincidencia baja">⚠️</span>' : ''}
            </div>
        </div>
    `).join('');

    if (!cesta.productos_encontrados.length) {
        listContainer.innerHTML = "<p style='color:#999; font-style:italic;'>Sin productos</p>";
    }

    // Faltantes
    const missingContainer = document.getElementById(idMissing);
    if (cesta.productos_no_encontrados && cesta.productos_no_encontrados.length > 0) {
        missingContainer.innerHTML = `
            <div class="missing-box">
                <strong>❌ No disponible:</strong> 
                ${cesta.productos_no_encontrados.join(", ")}
            </div>
        `;
    } else {
        missingContainer.innerHTML = "";
    }
}
</script>