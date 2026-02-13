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

    /* ZONA DE DETALLES */
    #detalle-cesta {
        margin-top: 30px; display: none; 
        border-top: 3px solid #0984e3; padding-top: 20px;
        animation: fadeIn 0.5s;
    }

    /*  ESTILOS DEL MENÚ GUARDADO */
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 25px; margin-top: 15px; }
    .dia-card { background: #fdfbf7; border-left: 4px solid #f39c12; padding: 15px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .dia-titulo { color: #d68910; font-weight: bold; margin-bottom: 5px; font-size: 1.1em; }
    .plato-nombre { font-weight: bold; color: #000; margin-bottom: 8px; font-size: 1.15em; }
    .plato-desc { color: #333; font-size: 0.95em; line-height: 1.4; }

    /* TARJETAS DE SUPERMERCADO */
    .comparison-row { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
    .super-card { flex: 1; min-width: 300px; padding: 20px; border-radius: 8px; border: 1px solid #ccc; color: #000; background: white;}
    
    .card-mercadona { background: #f4fbf7; border-top: 5px solid #009432; }
    .card-mercadona h3 { color: #009432; border-bottom: 2px solid #009432; padding-bottom: 5px; margin-top: 0;}
    
    .card-dia { background: #fff5f6; border-top: 5px solid #EA2027; }
    .card-dia h3 { color: #EA2027; border-bottom: 2px solid #EA2027; padding-bottom: 5px; margin-top: 0;}

    .price-tag { font-size: 1.6em; margin: 15px 0; font-weight: bold; color: #111; }
    .prod-item { border-bottom: 1px solid #ddd; padding: 10px 0; font-size: 0.95em; color: #000; }
    .prod-name { font-weight: 700; color: #000; margin-bottom: 3px; display: block; }
    .prod-meta { color: #444; font-size: 0.9em; font-weight: 500; }
    
    .missing-box { margin-top: 15px; padding: 15px; background: #fdedec; border: 1px solid #e6b0aa; border-radius: 6px; color: #c0392b; font-size: 0.9em; }
    .winner-banner { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; margin-bottom: 20px; border: 1px solid #c3e6cb; font-size: 1.2em; font-weight: bold; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="dashboard-container">
    <h2 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">📊 Mi Historial de Compras</h2>
    
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        
        <?php if (empty($cestas)): ?>
            <p style="text-align: center; color: #555; padding: 20px;">
                Aún no tienes historial. <a href="/?r=planificador">¡Planifica tu primer menú!</a>
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
        <h3 style="color: #333; margin-bottom: 20px;">📝 Detalles de la Búsqueda Guardada</h3>
        
        <div id="dashboard-menu-container" style="display: none;">
            <h4 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 5px;">🍽️ Menú Planificado</h4>
            <div id="dashboard-menu-grid" class="menu-grid"></div>
        </div>

        <h4 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 20px;">🛒 Lista de la Compra</h4>
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

    // --- 1. RENDERIZAR MENÚ (Si existe) ---
    const menuContainer = document.getElementById('dashboard-menu-container');
    const menuGrid = document.getElementById('dashboard-menu-grid');
    
    // Comprobamos si el JSON tiene "menu_planificado" y no está vacío
    if (data.menu_planificado && data.menu_planificado.length > 0) {
        menuContainer.style.display = 'block';
        menuGrid.innerHTML = data.menu_planificado.map(dia => `
            <div class="dia-card">
                <div class="dia-titulo">${dia.dia}</div>
                <div class="plato-nombre">${dia.plato}</div>
                <div class="plato-desc">${dia.descripcion}</div>
            </div>
        `).join('');
    } else {
        // Si no tiene menú (ej: listas manuales), lo ocultamos
        menuContainer.style.display = 'none';
        menuGrid.innerHTML = '';
    }

    // --- 2. BANNER GANADOR ---
    const banner = document.getElementById('detail-banner');
    banner.innerHTML = `🏆 Ganador: <strong>${data.mejor_supermercado}</strong> | Ahorras: <strong>${data.ahorro_total} €</strong>`;
    
    if (data.mejor_supermercado === 'Dia') {
        banner.style.background = "#fadbd8"; banner.style.color = "#721c24"; banner.style.borderColor = "#f5c6cb";
    } else {
        banner.style.background = "#d4edda"; banner.style.color = "#155724"; banner.style.borderColor = "#c3e6cb";
    }

    // --- 3. RENDERIZAR COLUMNAS ---
    renderColumna(data.cesta_mercadona, 'm-price', 'm-list', 'm-missing');
    renderColumna(data.cesta_dia, 'd-price', 'd-list', 'd-missing');

    visor.scrollIntoView({behavior: "smooth"});
}

function renderColumna(cesta, idPrice, idList, idMissing) {
    document.getElementById(idPrice).innerText = cesta.total.toFixed(2) + " €";
    
    document.getElementById(idList).innerHTML = cesta.productos_encontrados.map(p => `
        <div class="prod-item">
            <span class="prod-name">${p.nombre}</span>
            <span class="prod-meta">${p.precio}€ / ${p.unidad}</span>
        </div>
    `).join('');

    const missingContainer = document.getElementById(idMissing);
    if (cesta.productos_no_encontrados && cesta.productos_no_encontrados.length > 0) {
        missingContainer.innerHTML = `
            <div class="missing-box">
                <strong style="color: #900C3F;">❌ No encontrados:</strong><br>
                <span style="color: #000; font-weight: 500;">${cesta.productos_no_encontrados.join(", ")}</span>
            </div>`;
    } else {
        missingContainer.innerHTML = "";
    }
}
</script>