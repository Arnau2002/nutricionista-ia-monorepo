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
                            <td style="display: flex; gap: 8px;">
                                <button class="btn-ver" data-json='<?php echo $jsonSafe; ?>' onclick="cargarDetalle(this)">
                                    👁️ Ver
                                </button>
                                <button class="btn-borrar" onclick="borrarCesta(<?php echo $cesta['id']; ?>, this)" style="background: #e74c3c; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                    🗑️ Borrar
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
        <div id="detail-banner" class="winner-banner">
            <div id="winner-msg"></div>
        </div>

        <div id="comparison-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
            <!-- Mercadona Header -->
            <div style="background: #f4fbf7; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #009432;">
                <h3 style="color: #009432; margin: 0; border-bottom: 2px solid #009432; padding-bottom: 8px;">Mercadona</h3>
                <div id="m-price" class="price-tag" style="margin-top: 10px; font-size: 1.5em;">0.00 €</div>
            </div>
            <!-- Dia Header -->
            <div style="background: #fff5f6; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #EA2027;">
                <h3 style="color: #EA2027; margin: 0; border-bottom: 2px solid #EA2027; padding-bottom: 8px;">Dia</h3>
                <div id="d-price" class="price-tag" style="margin-top: 10px; font-size: 1.5em;">0.00 €</div>
            </div>

            <!-- Filas de productos inyectadas por JS -->
            <div id="list-container" style="grid-column: 1 / span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
            </div>

            <!-- Footers (No encontrados) -->
            <div id="m-missing" style="background: #f4fbf7; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
            <div id="d-missing" style="background: #fff5f6; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
        </div>
    </div>
</div>

<script>
async function borrarCesta(id, btn) {
    if (!confirm("¿Estás seguro de que quieres borrar esta cesta del historial?")) return;

    btn.disabled = true;
    btn.innerText = "⏳...";

    try {
        const response = await fetch('/delete_basket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        const res = await response.json();

        if (response.ok) {
            // Eliminar la fila de la tabla visualmente
            const fila = btn.closest('tr');
            fila.style.opacity = '0';
            setTimeout(() => {
                fila.remove();
                // Si la tabla se queda vacía, recargar para mostrar mensaje de "no hay datos"
                if (document.querySelectorAll('.history-table tbody tr').length === 0) {
                    location.reload();
                }
            }, 300);
            
            // Ocultar el detalle si es la cesta que borramos
            document.getElementById('detalle-cesta').style.display = 'none';
        } else {
            alert("Error: " + res.error);
            btn.disabled = false;
            btn.innerText = "🗑️ Borrar";
        }
    } catch (e) {
        alert("Error de conexión al borrar.");
        btn.disabled = false;
        btn.innerText = "🗑️ Borrar";
    }
}

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
    let savingsText = `En esta compra ahorraste <strong>${data.ahorro_total}€</strong>.`;
    if (data.mensaje_ahorro) {
        savingsText += `<div style="background: rgba(0,0,0,0.05); padding: 8px; border-radius: 6px; margin-top: 10px; font-size: 0.85em; color: #333; line-height: 1.3;">💡 <strong>Info:</strong> ${data.mensaje_ahorro}</div>`;
    }

    document.getElementById('winner-msg').innerHTML = `
        <div style="font-size: 1.25em; margin-bottom: 5px;">🏆 Supermercado recomendado: <strong>${data.mejor_supermercado}</strong></div>
        <div style="font-size: 1.05em;">
            ${savingsText}
        </div>
    `;
    
    if (data.mejor_supermercado === 'Dia') {
        banner.style.background = "#fadbd8"; banner.style.color = "#721c24"; banner.style.borderColor = "#f5c6cb";
    } else {
        banner.style.background = "#d4edda"; banner.style.color = "#155724"; banner.style.borderColor = "#c3e6cb";
    }

    // --- 3. RENDERIZAR COLUMNAS ---
    const container = document.getElementById('list-container');
    const missingM = document.getElementById('m-missing');
    const missingD = document.getElementById('d-missing');

    document.getElementById('m-price').innerHTML = `
        <div style="font-weight: bold;">${data.cesta_mercadona.total.toFixed(2)} €</div>
        <div style="font-size: 0.5em; color: #0984e3;">🚀 Eficiencia: ${data.cesta_mercadona.total_normalizado.toFixed(2)}€/kg-L</div>
    `;
    document.getElementById('d-price').innerHTML = `
        <div style="font-weight: bold;">${data.cesta_dia.total.toFixed(2)} €</div>
        <div style="font-size: 0.5em; color: #0984e3;">🚀 Eficiencia: ${data.cesta_dia.total_normalizado.toFixed(2)}€/kg-L</div>
    `;

    container.innerHTML = "";
    missingM.innerHTML = "";
    missingD.innerHTML = "";

    if (data.filas && data.filas.length > 0) {
        // VISTA ALINEADA (MODERNA)
        data.filas.forEach(fila => {
            // Mercadona Cell
            const divM = document.createElement('div');
            divM.style.background = "#f4fbf7";
            divM.style.padding = "0 20px 12px 20px";
            divM.style.borderLeft = "1px solid #ddd";
            divM.style.borderRight = "1px solid #ddd";
            divM.innerHTML = crearHtmlDashboard(fila.mercadona, 'Mercadona');
            
            // Dia Cell
            const divD = document.createElement('div');
            divD.style.background = "#fff5f6";
            divD.style.padding = "0 20px 12px 20px";
            divD.style.borderLeft = "1px solid #ddd";
            divD.style.borderRight = "1px solid #ddd";
            divD.innerHTML = crearHtmlDashboard(fila.dia, 'Dia');

            container.appendChild(divM);
            container.appendChild(divD);
        });
    } else {
        // VISTA ANTIGUA (FALLBACK)
        container.innerHTML = `<div style="grid-column: 1 / span 2; padding: 20px; text-align: center; color: #666;">
            Esta cesta se guardó con una versión antigua. Los productos se muestran por columnas.
        </div>`;
        renderColumnaLegacy(data.cesta_mercadona, container, true);
        renderColumnaLegacy(data.cesta_dia, container, false);
    }

    // Footers
    const mMiss = data.cesta_mercadona.productos_no_encontrados;
    const dMiss = data.cesta_dia.productos_no_encontrados;
    missingM.innerHTML = mMiss.length ? `❌ No disponible: ${mMiss.join(", ")}` : "";
    missingD.innerHTML = dMiss.length ? `❌ No disponible: ${dMiss.join(", ")}` : "";

    visor.scrollIntoView({behavior: "smooth"});
}

function crearHtmlDashboard(p, tienda) {
    if (!p) {
        return `
        <div class="prod-item" style="display: flex; align-items: center; gap: 12px; height: 100%; color: #999; font-style: italic; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <div style="width: 45px; height: 45px; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center;">❓</div>
            <div style="flex: 1;">No disponible</div>
        </div>`;
    }

    const imgUrl = (p.imagen && p.imagen !== '') 
        ? p.imagen 
        : 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';

    const multiHtml = (p.multiplicador && p.multiplicador > 1) 
        ? `<span style="color:#e67e22; font-weight:900; font-size:1.1em; margin-right:4px;">${p.multiplicador}x</span>` 
        : '';
        
    const precioTotal = (p.multiplicador && p.multiplicador > 1) ? (p.precio * p.multiplicador).toFixed(2) : p.precio.toFixed(2);
    const precioUnitarioMeta = (p.multiplicador && p.multiplicador > 1) ? ` <span style="font-size:0.85em; color:#999; margin-left:4px; font-weight:normal;">(${p.precio.toFixed(2)}€/ud)</span>` : '';

    return `
    <div class="prod-item" style="display: flex; align-items: center; gap: 12px; height: 100%; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <img src="${imgUrl}" alt="${p.nombre}" 
             style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: white;">
        <div style="flex: 1;">
            <span class="prod-name" style="font-size: 0.9em; line-height: 1.2; font-weight: bold;">${multiHtml}${p.nombre}</span>
            <span class="prod-meta" style="font-size: 0.8em;"><strong>${precioTotal}€</strong>${precioUnitarioMeta}</span>
        </div>
    </div>
    `;
}

function renderColumnaLegacy(cesta, container, isMercadona) {
    const bgColor = isMercadona ? "#f4fbf7" : "#fff5f6";
    const sideDiv = document.createElement('div');
    sideDiv.style.background = bgColor;
    sideDiv.style.padding = "20px";
    sideDiv.style.border = "1px solid #ddd";

    let html = cesta.productos_encontrados.map(p => {
        const imgUrl = (p.imagen && p.imagen !== '') 
            ? p.imagen 
            : 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';

        const multiHtml = (p.multiplicador && p.multiplicador > 1) 
            ? `<span style="color:#e67e22; font-weight:900; font-size:1.1em; margin-right:4px;">${p.multiplicador}x</span>` 
            : '';

        const precioTotal = (p.multiplicador && p.multiplicador > 1) ? (p.precio * p.multiplicador).toFixed(2) : p.precio.toFixed(2);

        return `
        <div class="prod-item" style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <img src="${imgUrl}" alt="${p.nombre}" 
                 style="width: 45px; height: 45px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: white;">
            <div style="flex: 1;">
                <span class="prod-name" style="font-size: 0.9em; font-weight: bold;">${multiHtml}${p.nombre}</span>
                <span class="prod-meta" style="font-size: 0.8em; color: #666; display: block;">${precioTotal}€</span>
            </div>
        </div>
        `;
    }).join('');

    sideDiv.innerHTML = html;
    container.appendChild(sideDiv);
}
</script>