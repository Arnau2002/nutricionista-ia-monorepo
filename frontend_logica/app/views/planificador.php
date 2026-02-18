<?php
// app/Views/planificador.php
?>
<style>
    /* Forzamos que todo el contenedor use texto oscuro */
    .planificador-container { color: #111; }

    .chef-header { text-align: center; margin-bottom: 30px; }
    .chef-header h2 { color: #2c3e50; font-weight: bold; }
    
    .chef-input-group { display: flex; gap: 10px; max-width: 600px; margin: 0 auto; }
    .chef-input { flex: 1; padding: 12px; border: 2px solid #0984e3; border-radius: 6px; font-size: 1.1em; color: #000; background: #fff; }
    .btn-chef { background: #0984e3; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .btn-chef:hover { background: #0769b5; }
    
    /* Botón Guardar */
    .btn-guardar { background: #27ae60; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1.1em; margin-top: 10px; }
    .btn-guardar:hover { background: #219150; }

    /* Loader de cocina */
    #loader { display: none; text-align: center; margin: 40px 0; color: #333; }
    .spinner { border: 4px solid #e0e0e0; border-top: 4px solid #0984e3; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 15px; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    /* Resultados */
    #resultados { display: none; margin-top: 30px; animation: fadeIn 0.5s; }
    
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .dia-card { background: #fdfbf7; border-left: 4px solid #f39c12; padding: 15px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #000; }
    .dia-titulo { color: #d68910; font-weight: bold; margin-bottom: 5px; font-size: 1.1em; }
    .plato-nombre { font-weight: bold; color: #000; margin-bottom: 8px; font-size: 1.15em; }
    .plato-desc { color: #333; font-size: 0.95em; line-height: 1.4; }

    /* Tarjetas del super */
    .comparison-row { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
    .super-card { flex: 1; min-width: 300px; padding: 20px; border-radius: 8px; border: 1px solid #ccc; color: #000; }
    
    .card-mercadona { background: #f4fbf7; border-top: 5px solid #009432; }
    .card-mercadona h3 { color: #009432; border-bottom: 2px solid #009432; padding-bottom: 5px; margin-top: 0;}
    
    .card-dia { background: #fff5f6; border-top: 5px solid #EA2027; }
    .card-dia h3 { color: #EA2027; border-bottom: 2px solid #EA2027; padding-bottom: 5px; margin-top: 0;}
    
    .price-tag { font-size: 1.6em; margin: 15px 0; font-weight: bold; color: #111; }
    
    .prod-item { border-bottom: 1px solid #ddd; padding: 10px 0; font-size: 0.95em; color: #000; }
    .prod-name { font-weight: 700; color: #000; margin-bottom: 3px; display: block; }
    .prod-meta { color: #444; font-size: 0.9em; font-weight: 500; }
    
    .missing-box { margin-top: 15px; padding: 15px; background: #fdedec; border: 1px solid #e6b0aa; border-radius: 6px; color: #c0392b; font-size: 0.9em; }
    .winner-banner { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; font-size: 1.2em; font-weight: bold; margin-bottom: 20px;}
</style>

<div class="card planificador-container" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="chef-header">
        <h2>👨‍🍳 Planificador de Menús Inteligente</h2>
        <p style="color:#555; font-size: 1.1em;">Dime qué te apetece comer esta semana y la IA creará el menú y buscará los precios.</p>
    </div>

    <div class="chef-input-group">
        <input type="text" id="prompt-chef" class="chef-input" placeholder="Ej: Quiero cenar vegetariano 3 días..." onkeypress="manejarEnter(event)">
        <button class="btn-chef" onclick="pedirMenu()">Planificar</button>
    </div>

    <div id="loader">
        <div class="spinner"></div>
        <h3 style="color: #2c3e50;">El Chef está pensando...</h3>
        <p style="color: #555;">Inventando recetas y buscando precios (puede tardar unos 10-15 segundos).</p>
    </div>

    <div id="resultados">
        
        <div style="text-align: center; margin-bottom: 25px;">
            <button id="btn-guardar" class="btn-guardar" onclick="guardarMenuEnHistorial()">💾 Guardar Menú y Lista en Historial</button>
            <span id="mensaje-guardado" style="color: #27ae60; font-weight: bold; font-size: 1.1em; display: none;">✅ ¡Guardado en tu Dashboard!</span>
        </div>

        <h3 style="color: #2c3e50; margin-bottom: 15px;">🍽️ Tu Menú Personalizado</h3>
        <div id="menu-container" class="menu-grid"></div>

        <h3 style="color: #2c3e50; margin-bottom: 15px; margin-top: 30px;">🛒 Tu Lista de la Compra</h3>
        <div id="winner-banner" class="winner-banner"></div>

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
// Variable global para guardar los datos de la IA temporalmente
let currentMenuData = null;

function manejarEnter(e) {
    if (e.key === 'Enter') pedirMenu();
}

async function pedirMenu() {
    const prompt = document.getElementById('prompt-chef').value.trim();
    if (!prompt) return alert('Por favor, escribe lo que te apetece comer.');

    document.getElementById('resultados').style.display = 'none';
    document.getElementById('loader').style.display = 'block';
    
    // Reiniciar el botón de guardar
    document.getElementById('btn-guardar').style.display = 'inline-block';
    document.getElementById('mensaje-guardado').style.display = 'none';

    try {
        const response = await fetch('http://localhost:8001/planificar-menu', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: prompt })
        });

        const data = await response.json();
        if (data.error) throw new Error(data.error);

        // Guardamos los datos en la variable global para poder enviarlos luego
        currentMenuData = data;

        renderizarMenu(data);
        
        document.getElementById('loader').style.display = 'none';
        document.getElementById('resultados').style.display = 'block';

    } catch (error) {
        document.getElementById('loader').style.display = 'none';
        alert('Error del Chef: ' + error.message);
    }
}

function renderizarMenu(data) {
    const menuDiv = document.getElementById('menu-container');
    menuDiv.innerHTML = data.menu.map(dia => `
        <div class="dia-card">
            <div class="dia-titulo">${dia.dia}</div>
            <div class="plato-nombre">${dia.plato}</div>
            <div class="plato-desc">${dia.descripcion}</div>
        </div>
    `).join('');

    const comp = data.comparativa;
    const banner = document.getElementById('winner-banner');
    banner.innerHTML = `🏆 Mejor opción: ${comp.mejor_supermercado} | Ahorras: ${comp.ahorro_total} €`;
    banner.style.background = (comp.mejor_supermercado === 'Dia') ? '#fadbd8' : '#d4edda';
    banner.style.color = (comp.mejor_supermercado === 'Dia') ? '#721c24' : '#155724';
    banner.style.borderColor = (comp.mejor_supermercado === 'Dia') ? '#f5c6cb' : '#c3e6cb';

    renderCol(comp.cesta_mercadona, 'm-price', 'm-list', 'm-missing');
    renderCol(comp.cesta_dia, 'd-price', 'd-list', 'd-missing');
}

function renderCol(cesta, idPrice, idList, idMissing) {
    // 1. Poner el precio total
    document.getElementById(idPrice).innerText = cesta.total.toFixed(2) + ' €';
    
    // 2. Generar la lista de productos con FOTOS
    document.getElementById(idList).innerHTML = cesta.productos_encontrados.map(p => {
        // Truco: Si no hay imagen, ponemos un icono genérico
        const imgUrl = (p.imagen && p.imagen !== '') 
            ? p.imagen 
            : 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png'; 

        return `
        <div class="prod-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee;">
            <img src="${imgUrl}" alt="${p.nombre}" 
                 style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: white;">
            
            <div style="flex: 1;">
                <span class="prod-name" style="display: block; font-weight: bold; font-size: 0.95em; color: #000;">${p.nombre}</span>
                <span class="prod-meta" style="color: #666; font-size: 0.85em;">${p.precio}€ / ${p.unidad}</span>
            </div>
        </div>
        `;
    }).join('');

    // 3. Mostrar productos no encontrados (si los hay)
    const missingDiv = document.getElementById(idMissing);
    if (cesta.productos_no_encontrados && cesta.productos_no_encontrados.length > 0) {
        missingDiv.innerHTML = `
            <div class="missing-box">
                <strong style="color: #900C3F;">❌ No encontrados:</strong><br>
                <span style="color: #000; font-weight: 500;">${cesta.productos_no_encontrados.join(", ")}</span>
            </div>`;
    } else {
        missingDiv.innerHTML = "";
    }
}

// --- NUEVA FUNCIÓN PARA GUARDAR ---
async function guardarMenuEnHistorial() {
    if (!currentMenuData) return;

    // "Engañamos" un poco al save_basket.php enviándole los datos con la misma estructura que espera
    // Pero además, le colamos nuestro menú generado por si en el futuro queremos mostrarlo en el dashboard.
    const payload = {
        mejor_supermercado: currentMenuData.comparativa.mejor_supermercado,
        ahorro_total: currentMenuData.comparativa.ahorro_total,
        cesta_mercadona: currentMenuData.comparativa.cesta_mercadona,
        cesta_dia: currentMenuData.comparativa.cesta_dia,
        menu_planificado: currentMenuData.menu 
    };

    try {
        const btn = document.getElementById('btn-guardar');
        btn.innerText = "⏳ Guardando...";
        btn.disabled = true;

        const response = await fetch('/save_basket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.status === 'success') {
            btn.style.display = 'none';
            document.getElementById('mensaje-guardado').style.display = 'inline-block';
        } else {
            alert('Error al guardar: ' + result.error);
            btn.innerText = "💾 Guardar Menú y Lista en Historial";
            btn.disabled = false;
        }
    } catch (error) {
        alert('Error de conexión al guardar.');
        const btn = document.getElementById('btn-guardar');
        btn.innerText = "💾 Guardar Menú y Lista en Historial";
        btn.disabled = false;
    }
}
</script>