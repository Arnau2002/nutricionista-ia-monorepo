<?php
// app/Views/dashboard.php

// 1. Conexión a BD
$host = 'nutricionista-mysql';
$db   = 'precios_comparados';
$user = 'root';
$pass = 'password_segura';

$cestas = [];
$stats = ['ahorro_total' => 0, 'total_cestas' => 0, 'favorito' => '-'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'] ?? 1;

    // Consulta: ordenamos por fecha descendente
    $stmt = $pdo->prepare("SELECT * FROM saved_baskets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $cestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular estadísticas rápidas
    if (!empty($cestas)) {
        $stats['total_cestas'] = count($cestas);
        $merca_count = 0;
        $dia_count = 0;
        foreach ($cestas as $c) {
            $data = json_decode($c['json_data'], true);
            $stats['ahorro_total'] += floatval($data['ahorro_total'] ?? 0);
            if ($c['winner_store'] == 'Mercadona') $merca_count++;
            else $dia_count++;
        }
        $stats['favorito'] = ($merca_count >= $dia_count) ? 'Mercadona' : 'Dia';
    }

} catch (PDOException $e) {
    echo "<div class='alert-error'>Error de conexión: " . $e->getMessage() . "</div>";
}
?>

<style>
    :root {
        --card-bg: rgba(255, 255, 255, 0.7);
        --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="dark"] {
        --card-bg: rgba(30, 41, 59, 0.5);
        --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .dashboard-wrapper {
        animation: fadeIn 0.6s ease-out;
        color: var(--text);
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--border);
        padding: 24px;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover { transform: translateY(-5px); }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .icon-ahorro { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .icon-cestas { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .icon-fav { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

    .stat-info .label { font-size: 0.9rem; color: var(--muted); font-weight: 500; }
    .stat-info .value { font-size: 1.5rem; font-weight: 800; color: var(--text); }

    /* HISTORY LIST */
    .history-card {
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 30px;
        box-shadow: var(--card-shadow);
    }

    .history-item {
        display: grid;
        grid-template-columns: 1fr 120px 120px 180px;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border);
        transition: all 0.2s ease;
        border-radius: 12px;
        margin-bottom: 8px;
    }

    .history-item:hover {
        background: rgba(var(--pri-rgb), 0.03);
        transform: scale(1.01);
    }

    .history-item:last-child { border-bottom: none; }

    .item-date { font-weight: 600; color: var(--text); }
    .item-date span { display: block; font-size: 0.8rem; color: var(--muted); font-weight: 400; }

    .badge-winner {
        padding: 6px 12px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
    }

    .badge-mercadona { background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); }
    .badge-dia { background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); }

    .item-price { font-weight: 800; font-size: 1.1rem; }

    .actions-flex { display: flex; gap: 8px; justify-content: flex-end; }

    .btn-action {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view { background: #eff6ff; color: #3b82f6; }
    .btn-view:hover { background: #3b82f6; color: white; }
    .btn-del { background: #fef2f2; color: #ef4444; }
    .btn-del:hover { background: #ef4444; color: white; }

    /* DETALLES */
    #detalle-cesta {
        margin-top: 40px;
        display: none;
        animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .glass-panel {
        background: var(--card-bg);
        backdrop-filter: blur(16px);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 32px;
        box-shadow: var(--card-shadow);
    }

    .comparison-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .store-column {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: rgba(255,255,255,0.02);
    }

    .store-header {
        padding: 24px;
        text-align: center;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border);
    }

    .merca-header { border-top: 5px solid #10b981; }
    .dia-header { border-top: 5px solid #ef4444; }

    .big-price { font-size: 2rem; font-weight: 900; margin-top: 8px; }

    .products-list { padding: 16px; }

    .product-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    .product-row:hover { background: rgba(var(--pri-rgb), 0.02); }
    .product-row:last-child { border-bottom: none; }

    .product-row img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: contain;
        background: white;
        border: 1px solid var(--border);
    }

    .product-info { flex: 1; }
    .product-info .name { font-size: 0.9rem; font-weight: 600; display: block; line-height: 1.2; }
    .product-info .price { font-size: 0.85rem; color: var(--muted); font-weight: 700; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 768px) {
        .history-item { grid-template-columns: 1fr 100px; gap: 10px; }
        .item-price, .badge-winner { display: none; }
        .comparison-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
        <div>
            <h1 style="margin: 0; font-weight: 900; letter-spacing: -1px;">Dashboard</h1>
            <p style="color: var(--muted); margin: 5px 0 0 0;">Gestiona tus ahorros y planificación inteligente.</p>
        </div>
        <a href="/?r=planificador" class="btn btn-pri" style="padding: 12px 24px; border-radius: 12px; font-weight: 700;">+ Nueva Planificación</a>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-ahorro">💰</div>
            <div class="stat-info">
                <span class="label">Ahorro Total</span>
                <div class="value"><?php echo number_format($stats['ahorro_total'], 2); ?> €</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-cestas">🛒</div>
            <div class="stat-info">
                <span class="label">Cestas Guardadas</span>
                <div class="value"><?php echo $stats['total_cestas']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-fav">🏆</div>
            <div class="stat-info">
                <span class="label">Súper Favorito</span>
                <div class="value"><?php echo $stats['favorito']; ?></div>
            </div>
        </div>
    </div>

    <!-- HISTORY LIST -->
    <div class="history-card">
        <h3 style="margin-top: 0; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">🕒</span> Historial Reciente
        </h3>

        <?php if (empty($cestas)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 4rem; margin-bottom: 20px;">🏜️</div>
                <h3 style="margin: 0; color: var(--muted);">Tu historial está vacío</h3>
                <p style="color: var(--muted);">Empieza a comparar para ver tus ahorros aquí.</p>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($cestas as $cesta): ?>
                    <?php 
                        $winner = htmlspecialchars($cesta['winner_store']);
                        $badgeClass = ($winner == 'Mercadona') ? 'badge-mercadona' : 'badge-dia';
                        $jsonSafe = htmlspecialchars($cesta['json_data'], ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="history-item">
                        <div class="item-date">
                            <?php echo date('d M, Y', strtotime($cesta['created_at'])); ?>
                            <span><?php echo date('H:i', strtotime($cesta['created_at'])); ?></span>
                        </div>
                        <div>
                            <span class="badge-winner <?php echo $badgeClass; ?>"><?php echo $winner; ?></span>
                        </div>
                        <div class="item-price">
                            <?php echo number_format($cesta['total_price'], 2); ?> €
                        </div>
                        <div class="actions-flex">
                            <button class="btn-action btn-view" data-json='<?php echo $jsonSafe; ?>' onclick="cargarDetalle(this)" title="Ver Detalles">
                                👁️
                            </button>
                            <button class="btn-action btn-del" onclick="borrarCesta(<?php echo $cesta['id']; ?>, this)" title="Borrar">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- DETAILS VIEW -->
    <div id="detalle-cesta">
        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                <div>
                    <h2 style="margin: 0;">Detalles de Compra</h2>
                    <p id="detalle-meta" style="color: var(--muted); margin: 5px 0 0 0;"></p>
                </div>
                <button onclick="document.getElementById('detalle-cesta').style.display='none'" style="background:none; border:none; font-size: 1.5rem; cursor:pointer; color:var(--muted);">✕</button>
            </div>

            <!-- Menú si existe -->
            <div id="dashboard-menu-container" style="display: none; margin-bottom: 40px;">
                <h4 style="margin-bottom: 15px; color: var(--pri); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">🍽️ Menú Planificado</h4>
                <div id="dashboard-menu-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;"></div>
            </div>

            <div id="winner-announcement" style="padding: 20px; border-radius: 16px; margin-bottom: 30px; text-align: center; font-weight: 700;">
            </div>

            <div class="comparison-grid">
                <!-- MERCADONA -->
                <div class="store-column">
                    <div class="store-header merca-header">
                        <img src="/public/img/mercadona.png" style="height: 40px; margin-bottom: 5px;">
                        <div id="m-price" class="big-price">0.00 €</div>
                    </div>
                    <div id="m-list" class="products-list"></div>
                    <div id="m-missing" style="padding: 15px; font-size: 0.8rem; color: #ef4444; background: rgba(239, 68, 68, 0.05);"></div>
                </div>

                <!-- DIA -->
                <div class="store-column">
                    <div class="store-header dia-header">
                        <img src="/public/img/dia.png" style="height: 40px; margin-bottom: 5px;">
                        <div id="d-price" class="big-price">0.00 €</div>
                    </div>
                    <div id="d-list" class="products-list"></div>
                    <div id="d-missing" style="padding: 15px; font-size: 0.8rem; color: #ef4444; background: rgba(239, 68, 68, 0.05);"></div>
                </div>
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
        renderizarCesta(data);
    } catch (e) {
        console.error("Error JSON", e);
    }
}

function renderizarCesta(data) {
    const visor = document.getElementById('detalle-cesta');
    visor.style.display = 'block';

    // Banner Ganador
    const ann = document.getElementById('winner-announcement');
    const isMerca = data.mejor_supermercado === 'Mercadona';
    const accent = isMerca ? '#10b981' : '#ef4444';
    ann.style.background = isMerca ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
    ann.style.color = isMerca ? '#059669' : '#dc2626';
    ann.style.border = `1px solid ${accent}44`;
    
    let mixedInfo = "";
    if (data.cesta_mixta && data.cesta_mixta.total > 0) {
        mixedInfo = `<div style="margin-top: 10px; font-size: 0.9em; color: #8e44ad;">🔀 Compra Mixta Óptima: <strong>${data.cesta_mixta.total}€</strong></div>`;
    }

    ann.innerHTML = `
        <div style="font-size: 1.2rem;">🏆 Mejor opción: ${data.mejor_supermercado}</div>
        <div style="font-size: 0.95rem; font-weight: normal; margin-top: 5px;">Ahorro total: <strong>${data.ahorro_total}€</strong></div>
        ${mixedInfo}
    `;

    // Precios
    document.getElementById('m-price').innerText = `${data.cesta_mercadona.total.toFixed(2)} €`;
    document.getElementById('d-price').innerText = `${data.cesta_dia.total.toFixed(2)} €`;

    // Listas
    const mList = document.getElementById('m-list');
    const dList = document.getElementById('d-list');
    mList.innerHTML = ""; dList.innerHTML = "";

    if (data.filas) {
        data.filas.forEach(fila => {
            if (fila.mercadona) mList.innerHTML += crearFilaHtml(fila.mercadona);
            if (fila.dia) dList.innerHTML += crearFilaHtml(fila.dia);
        });
    }

    // Faltantes
    document.getElementById('m-missing').innerText = data.cesta_mercadona.productos_no_encontrados.length ? "❌ No encontrados: " + data.cesta_mercadona.productos_no_encontrados.join(", ") : "";
    document.getElementById('d-missing').innerText = data.cesta_dia.productos_no_encontrados.length ? "❌ No encontrados: " + data.cesta_dia.productos_no_encontrados.join(", ") : "";

    // Menú
    const menuCont = document.getElementById('dashboard-menu-container');
    const menuGrid = document.getElementById('dashboard-menu-grid');
    if (data.menu_planificado && data.menu_planificado.length > 0) {
        menuCont.style.display = 'block';
        menuGrid.innerHTML = data.menu_planificado.map(dia => `
            <div style="background: var(--card-bg); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                <div style="color: var(--pri); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">${dia.dia}</div>
                <div style="font-weight: 700; margin: 4px 0;">${dia.plato}</div>
            </div>
        `).join('');
    } else {
        menuCont.style.display = 'none';
    }

    visor.scrollIntoView({ behavior: 'smooth' });
}

function crearFilaHtml(p) {
    const img = p.imagen || 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';
    const multi = p.multiplicador > 1 ? `<span style="color:#f59e0b; margin-right:4px;">${p.multiplicador}x</span>` : '';
    return `
        <div class="product-row">
            <img src="${img}" loading="lazy">
            <div class="product-info">
                <span class="name">${multi}${p.nombre}</span>
                <span class="price">${p.precio.toFixed(2)}€</span>
            </div>
        </div>
    `;
}

async function borrarCesta(id, btn) {
    if (!confirm("¿Borrar esta cesta del historial?")) return;
    
    if(btn) btn.disabled = true;

    try {
        const response = await fetch('/delete_basket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        if (response.ok) {
            location.reload();
        } else {
            alert("No se pudo borrar.");
            if(btn) btn.disabled = false;
        }
    } catch (e) { 
        alert("Error al borrar"); 
        if(btn) btn.disabled = false;
    }
}
</script>