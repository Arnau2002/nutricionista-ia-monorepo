<?php
// app/Views/planificador.php
?>
<style>
    /* Forzamos que todo el contenedor use texto oscuro */
    .planificador-container {
        color: #111;
    }

    .chef-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .chef-header h2 {
        color: #2c3e50;
        font-weight: bold;
    }

    .chef-input-group {
        display: flex;
        gap: 10px;
        max-width: 600px;
        margin: 0 auto;
    }

    .chef-input {
        flex: 1;
        padding: 12px;
        border: 2px solid #0984e3;
        border-radius: 6px;
        font-size: 1.1em;
        color: #000;
        background: #fff;
    }

    .btn-chef {
        background: #0984e3;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-chef:hover {
        background: #0769b5;
    }

    /* Botón Guardar */
    .btn-guardar {
        background: #27ae60;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        font-size: 1.1em;
        margin-top: 10px;
    }

    .btn-guardar:hover {
        background: #219150;
    }

    /* Loader de cocina */
    #loader {
        display: none;
        text-align: center;
        margin: 40px 0;
        color: #333;
    }

    .spinner {
        border: 4px solid #e0e0e0;
        border-top: 4px solid #0984e3;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Resultados */
    #resultados {
        display: none;
        margin-top: 30px;
        animation: fadeIn 0.5s;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .dia-card {
        background: #fdfbf7;
        border-left: 4px solid #f39c12;
        padding: 15px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        color: #000;
    }

    .dia-titulo {
        color: #d68910;
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 1.1em;
    }

    .plato-nombre {
        font-weight: bold;
        color: #000;
        margin-bottom: 8px;
        font-size: 1.15em;
    }

    .plato-desc {
        color: #333;
        font-size: 0.95em;
        line-height: 1.4;
    }

    /* Tarjetas del super */
    .comparison-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .super-card {
        flex: 1;
        min-width: 300px;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ccc;
        color: #000;
    }

    .card-mercadona {
        background: #f4fbf7;
        border-top: 5px solid #009432;
    }

    .card-mercadona h3 {
        color: #009432;
        border-bottom: 2px solid #009432;
        padding-bottom: 5px;
        margin-top: 0;
    }

    .card-dia {
        background: #fff5f6;
        border-top: 5px solid #EA2027;
    }

    .card-dia h3 {
        color: #EA2027;
        border-bottom: 2px solid #EA2027;
        padding-bottom: 5px;
        margin-top: 0;
    }

    .price-tag {
        font-size: 1.6em;
        margin: 15px 0;
        font-weight: bold;
        color: #111;
    }

    .prod-item {
        border-bottom: 1px solid #ddd;
        padding: 10px 0;
        font-size: 0.95em;
        color: #000;
    }

    .prod-name {
        font-weight: 700;
        color: #000;
        margin-bottom: 3px;
        display: block;
    }

    .prod-meta {
        color: #444;
        font-size: 0.9em;
        font-weight: 500;
    }

    .missing-box {
        margin-top: 15px;
        padding: 15px;
        background: #fdedec;
        border: 1px solid #e6b0aa;
        border-radius: 6px;
        color: #c0392b;
        font-size: 0.9em;
    }

    .winner-banner {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        border: 1px solid #c3e6cb;
        font-size: 1.2em;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .ingredientes-meta {
        margin-top: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
    }

    .ingredientes-meta h4 {
        margin: 0 0 8px 0;
        color: #2c3e50;
    }

    .ingredientes-meta ul {
        margin: 0;
        padding-left: 18px;
        color: #222;
    }

    .checklist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 8px;
        margin-top: 10px;
    }

    .check-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 10px;
        color: #111;
    }

    .check-item input {
        width: 16px;
        height: 16px;
        accent-color: #0984e3;
        cursor: pointer;
    }

    .check-item label {
        cursor: pointer;
        flex: 1;
    }

    .check-cantidad {
        color: #555;
        font-size: 0.9em;
        white-space: nowrap;
    }

    .checklist-actions {
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .checklist-count {
        font-weight: bold;
        color: #2c3e50;
        margin-right: 6px;
    }

    .btn-checklist {
        background: #f1f5f9;
        color: #111;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-checklist:hover {
        background: #e2e8f0;
    }

    .btn-buscar {
        background: #0984e3;
        color: #fff;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 700;
    }

    .btn-buscar:hover {
        background: #0769b5;
    }

    .btn-buscar:disabled {
        background: #93c5fd;
        cursor: not-allowed;
    }

    #loader-busqueda {
        display: none;
        text-align: center;
        margin: 18px 0;
        color: #333;
    }
</style>

<div class="card planificador-container"
    style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="chef-header">
        <h2>👨‍🍳 Planificador de Menús Inteligente</h2>
        <p style="color:#555; font-size: 1.1em;">Dime qué te apetece comer esta semana y la IA creará el menú y buscará
            los precios.</p>
    </div>

    <!-- SECCIÓN DE PREFERENCIAS -->
    <div
        style="background: #fdfefe; border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
        <p style="margin-top:0; font-weight:bold; color: #2ecc71; text-align: center;">⚙️ Personalización del Menú</p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 100px;">
                <label><strong>Personas:</strong></label>
                <input type="number" id="numPersonasInput" min="1" max="12" value="2"
                    style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label><strong>Días:</strong></label>
                <input type="number" id="numDiasInput" min="1" max="14" value="7"
                    style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label><strong>Dieta:</strong></label>
                <select id="dietaSelect" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
                    <option value="Equilibrada">🥗 Equilibrada</option>
                    <option value="Vegana">🌱 Vegana</option>
                    <option value="Vegetariana">🥚 Vegetariana</option>
                    <option value="Sin Gluten">🌾 Sin Gluten</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label><strong>Objetivo:</strong></label>
                <select id="objetivoSelect" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
                    <option value="Ahorro">💰 Máximo Ahorro</option>
                    <option value="Ganar músculo">💪 Ganar Músculo</option>
                    <option value="Perder peso">🏃 Perder Peso</option>
                </select>
            </div>
            <div style="flex: 2; min-width: 250px;">
                <label><strong>Ciudad:</strong></label>
                <select id="ciudadSelect" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
                    <option value="Valencia" selected>🏙️ Valencia</option>
                    <option value="Madrid">🏙️ Madrid</option>
                    <option value="Barcelona">🏙️ Barcelona</option>
                    <option value="Sevilla">🏙️ Sevilla</option>
                    <option value="Malaga">🏙️ Málaga</option>
                    <option value="Zaragoza">🏙️ Zaragoza</option>
                    <option value="Bilbao">🏙️ Bilbao</option>
                </select>
            </div>
            <div style="flex: 2; min-width: 250px;">
                <label><strong>Alergias (separadas por comas):</strong></label>
                <input type="text" id="alergiasInput" placeholder="Ej: Nueces, Marisco, Lactosa"
                    style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="flex: 2; min-width: 250px;">
                <label><strong>Ingredientes que ya tienes:</strong></label>
                <input type="text" id="despensaInput" placeholder="Ej: sal, aceite, arroz"
                    style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
        </div>
    </div>

    <div class="chef-input-group">
        <input type="text" id="prompt-chef" class="chef-input" placeholder="Ej: Quiero cenar vegetariano 3 días..."
            onkeypress="manejarEnter(event)">
        <button class="btn-chef" onclick="pedirMenu()">Planificar</button>
    </div>

    <div id="loader">
        <div class="spinner"></div>
        <h3 style="color: #2c3e50;">El Chef está pensando...</h3>
        <p style="color: #555;">Inventando recetas y buscando precios (puede tardar unos 10-15 segundos).</p>
    </div>

    <div id="resultados">

        <div style="text-align: center; margin-bottom: 25px;">
            <button id="btn-guardar" class="btn-guardar" onclick="guardarMenuEnHistorial()">💾 Guardar Menú y Lista en
                Historial</button>
            <span id="mensaje-guardado" style="color: #27ae60; font-weight: bold; font-size: 1.1em; display: none;">✅
                ¡Guardado en tu Dashboard!</span>
        </div>

        <h3 style="color: #2c3e50; margin-bottom: 15px;">🍽️ Tu Menú Personalizado</h3>
        <div id="menu-container" class="menu-grid"></div>
        <div id="ingredientes-meta" class="ingredientes-meta" style="display:none;"></div>

        <h3 style="color: #2c3e50; margin-bottom: 15px; margin-top: 30px;">🛒 Tu Lista de la Compra</h3>

        <div id="checklist-wrapper" class="ingredientes-meta" style="display:none;">
            <h4>Selecciona los ingredientes que quieres comprar</h4>
            <div id="checklist-grid" class="checklist-grid"></div>
            <div class="checklist-actions">
                <span class="checklist-count">Seleccionados: <span id="checklist-count">0</span></span>
                <button type="button" class="btn-checklist" onclick="seleccionarTodoChecklist()">Seleccionar
                    todo</button>
                <button type="button" class="btn-checklist" onclick="limpiarChecklist()">Quitar todo</button>
                <button type="button" id="btn-buscar-checklist" class="btn-buscar" onclick="buscarConChecklist()"
                    disabled>Buscar precios con selección</button>
            </div>
            <div id="checklist-excluidos" style="margin-top:10px; color:#2d6a4f;"></div>
        </div>

        <div id="loader-busqueda">
            <div class="spinner"></div>
            <p style="margin:0;">Buscando precios para tu selección...</p>
        </div>

        <div id="comparativa-wrapper" style="display:none;">
            <div id="winner-banner" class="winner-banner"></div>

            <div id="comparison-grid"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; margin-top: 20px;">
                <!-- Mercadona Header -->
                <div
                    style="background: #f4fbf7; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #009432;">
                    <h3 style="color: #009432; margin: 0; border-bottom: 2px solid #009432; padding-bottom: 8px;">
                        Mercadona</h3>
                    <div id="m-price" class="price-tag" style="margin-top: 10px; font-size: 1.5em;">0.00 €</div>
                </div>
                <!-- Dia Header -->
                <div
                    style="background: #fff5f6; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #EA2027;">
                    <h3 style="color: #EA2027; margin: 0; border-bottom: 2px solid #EA2027; padding-bottom: 8px;">Dia
                    </h3>
                    <div id="d-price" class="price-tag" style="margin-top: 10px; font-size: 1.5em;">0.00 €</div>
                </div>

                <!-- Filas de productos -->
                <div id="list-container"
                    style="grid-column: 1 / span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
                </div>

                <!-- Footers (No encontrados) -->
                <div id="m-missing"
                    style="background: #f4fbf7; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;">
                </div>
                <div id="d-missing"
                    style="background: #fff5f6; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;">
                </div>
            </div>
        </div>

        <!-- ===== SECCIÓN MAPA ===== -->
        <div id="mapa-wrapper" style="display:none; margin-top: 35px;">
            <h3 style="color: #2c3e50; margin-bottom: 8px;">🗺️ Supermercados cerca de ti en Valencia</h3>
            <p style="color: #555; margin-bottom: 12px; font-size: 0.95em;">
                El precio mostrado en cada pin es el coste total de tu lista de la compra en esa cadena.
                Haz clic en un supermercado para trazar la ruta desde tu ubicación.
            </p>
            <div id="mapa-supermercados" style="height: 480px; border-radius: 10px; border: 2px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.12);"></div>
            <p style="font-size: 0.8em; color: #aaa; margin-top: 6px;">
                📍 Ubicaciones de tiendas orientativas. Ruta calculada con OpenStreetMap/OSRM.
            </p>
        </div>
        <!-- ===== FIN MAPA ===== -->

    </div>
</div>

<script>
    // Variable global para guardar los datos de la IA temporalmente
    let currentMenuData = null;
    let ingredientesChecklist = [];

    // PERSISTENCIA: Cargar al iniciar
    window.addEventListener('load', () => {
        const savedMenu = localStorage.getItem('ultimoMenuPlanificado');
        const savedPrompt = localStorage.getItem('ultimoPromptChef');

        if (savedPrompt) document.getElementById('prompt-chef').value = savedPrompt;

        if (savedMenu) {
            try {
                const data = JSON.parse(savedMenu);
                currentMenuData = data;
                renderizarMenu(data);
                document.getElementById('resultados').style.display = 'block';
            } catch (e) { }
        }
    });

    function manejarEnter(e) {
        if (e.key === 'Enter') pedirMenu();
    }

    async function pedirMenu() {
        const prompt = document.getElementById('prompt-chef').value.trim();
        if (!prompt) return alert('Por favor, escribe lo que te apetece comer.');

        localStorage.setItem('ultimoPromptChef', prompt);

        const numPersonas = parseInt(document.getElementById('numPersonasInput').value, 10);
        const numDias = parseInt(document.getElementById('numDiasInput').value, 10);
        const dieta = document.getElementById('dietaSelect').value;
        const objetivo = document.getElementById('objetivoSelect').value;
        const ciudad = document.getElementById('ciudadSelect').value;
        const alergias = document.getElementById('alergiasInput').value.split(',').map(a => a.trim()).filter(a => a.length > 0);
        const ingredientesEnCasa = document.getElementById('despensaInput').value.split(',').map(i => i.trim()).filter(i => i.length > 0);

        document.getElementById('resultados').style.display = 'none';
        document.getElementById('loader').style.display = 'block';

        // Reiniciar el botón de guardar
        document.getElementById('btn-guardar').style.display = 'inline-block';
        document.getElementById('mensaje-guardado').style.display = 'none';

        try {
            const response = await fetch('http://localhost:8001/planificar-menu', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    prompt_usuario: prompt,
                    num_personas: Number.isFinite(numPersonas) && numPersonas > 0 ? numPersonas : 2,
                    num_dias: Number.isFinite(numDias) && numDias > 0 ? numDias : 7,
                    tipo_dieta: dieta,
                    alergias: alergias,
                    objetivo: objetivo,
                    ciudad: ciudad,
                    ingredientes_en_casa: ingredientesEnCasa
                })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            // Guardar para persistencia
            localStorage.setItem('ultimoMenuPlanificado', JSON.stringify(data));

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

        const metaDiv = document.getElementById('ingredientes-meta');
        const ingredientes = Array.isArray(data.ingredientes_limpios) ? data.ingredientes_limpios : [];
        const excluidos = Array.isArray(data.ingredientes_excluidos_despensa) ? data.ingredientes_excluidos_despensa : [];
        const personas = data.num_personas || 2;

        if (ingredientes.length > 0 || excluidos.length > 0) {
            const topIngredientes = ingredientes.map(i => {
                if (typeof i === 'string') return `<li>${i}</li>`;
                const frecuenciaTxt = i.frecuencia_menu ? ` (${i.frecuencia_menu} usos)` : '';
                return `<li><strong>${i.nombre}</strong>: ${i.cantidad ?? '-'}${i.unidad ?? ''}${frecuenciaTxt}</li>`;
            }).join('');

            metaDiv.innerHTML = `
            <h4>🧾 Resumen de Compra (${personas} persona${personas > 1 ? 's' : ''})</h4>
            ${ingredientes.length > 0 ? `<ul>${topIngredientes}</ul>` : '<p style="margin:0;">No hay ingredientes calculados.</p>'}
            ${excluidos.length > 0 ? `<p style="margin:10px 0 0 0; color:#2d6a4f;"><strong>✅ Excluidos por despensa:</strong> ${excluidos.join(', ')}</p>` : ''}
        `;
            metaDiv.style.display = 'block';
        } else {
            metaDiv.style.display = 'none';
            metaDiv.innerHTML = '';
        }

        ingredientesChecklist = ingredientes;
        renderizarChecklist(ingredientes, excluidos);

        document.getElementById('comparativa-wrapper').style.display = 'none';
    }

    function renderizarChecklist(ingredientes, excluidos) {
        const wrapper = document.getElementById('checklist-wrapper');
        const grid = document.getElementById('checklist-grid');
        const excluidosDiv = document.getElementById('checklist-excluidos');

        if (!ingredientes || ingredientes.length === 0) {
            wrapper.style.display = 'none';
            grid.innerHTML = '';
            excluidosDiv.innerHTML = '';
            actualizarContadorChecklist();
            return;
        }

        wrapper.style.display = 'block';
        grid.innerHTML = ingredientes.map((item, idx) => {
            const nombre = typeof item === 'string' ? item : (item.nombre || 'Ingrediente');
            const cantidad = typeof item === 'string' ? '' : `${item.cantidad ?? ''}${item.unidad ?? ''}`;
            const frecuencia = typeof item === 'string' ? '' : (item.frecuencia_menu ? ` (${item.frecuencia_menu} usos)` : '');
            const checkboxId = `ingrediente-check-${idx}`;

            return `
            <div class="check-item">
                <input type="checkbox" id="${checkboxId}" class="ingrediente-check" data-index="${idx}" checked onchange="actualizarContadorChecklist()">
                <label for="${checkboxId}">${nombre}${frecuencia}</label>
                ${cantidad ? `<span class="check-cantidad">${cantidad}</span>` : ''}
            </div>
        `;
        }).join('');

        excluidosDiv.innerHTML = excluidos.length > 0
            ? `<strong>✅ Excluidos por despensa:</strong> ${excluidos.join(', ')}`
            : '';

        actualizarContadorChecklist();
    }

    function actualizarContadorChecklist() {
        const checks = Array.from(document.querySelectorAll('.ingrediente-check'));
        const seleccionados = checks.filter(ch => ch.checked).length;
        document.getElementById('checklist-count').textContent = String(seleccionados);
        document.getElementById('btn-buscar-checklist').disabled = seleccionados === 0;
    }

    function seleccionarTodoChecklist() {
        document.querySelectorAll('.ingrediente-check').forEach(ch => { ch.checked = true; });
        actualizarContadorChecklist();
    }

    function limpiarChecklist() {
        document.querySelectorAll('.ingrediente-check').forEach(ch => { ch.checked = false; });
        actualizarContadorChecklist();
    }

    function obtenerIngredientesSeleccionados() {
        const checks = Array.from(document.querySelectorAll('.ingrediente-check'));
        const seleccionados = [];

        checks.forEach(ch => {
            if (!ch.checked) return;
            const idx = Number(ch.getAttribute('data-index'));
            if (Number.isInteger(idx) && idx >= 0 && idx < ingredientesChecklist.length) {
                seleccionados.push(ingredientesChecklist[idx]);
            }
        });

        return seleccionados;
    }

    async function buscarConChecklist() {
        const ingredientesSeleccionados = obtenerIngredientesSeleccionados();

        if (!ingredientesSeleccionados.length) {
            alert('Selecciona al menos un ingrediente para buscar precios.');
            return;
        }

        const alergias = document.getElementById('alergiasInput').value
            .split(',')
            .map(a => a.trim())
            .filter(a => a.length > 0);

        const ciudad = document.getElementById('ciudadSelect').value;

        const ingredientesEnCasa = document.getElementById('despensaInput').value
            .split(',')
            .map(i => i.trim())
            .filter(i => i.length > 0);

        document.getElementById('loader-busqueda').style.display = 'block';

        try {
            const payload = {
                ingredientes: ingredientesSeleccionados,
                alergias: alergias,
                ciudad: ciudad,
                ingredientes_en_casa: ingredientesEnCasa
            };

            let response = null;
            let lastNetworkError = null;

            // Reintento corto para evitar fallos transitorios al reiniciar contenedores
            for (let intento = 1; intento <= 2; intento++) {
                try {
                    response = await fetch('http://localhost:8001/comparar-lista-compra', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    break;
                } catch (networkErr) {
                    lastNetworkError = networkErr;
                    if (intento === 1) {
                        await new Promise(resolve => setTimeout(resolve, 800));
                    }
                }
            }

            if (!response) {
                throw new Error(lastNetworkError?.message || 'No se pudo conectar con el servidor.');
            }

            if (!response.ok) {
                const detalle = await response.text();
                throw new Error(detalle || `Error HTTP ${response.status}`);
            }

            const comp = await response.json();
            if (comp.error) throw new Error(comp.error);

            currentMenuData.comparativa = comp;
            renderizarComparativa(comp);
            document.getElementById('comparativa-wrapper').style.display = 'block';
        } catch (error) {
            alert('Error al buscar precios: ' + error.message);
        } finally {
            document.getElementById('loader-busqueda').style.display = 'none';
        }
    }

    function renderizarComparativa(comp) {
        const banner = document.getElementById('winner-banner');

        let warningHtml = "";
        if (!comp.comparativa_completa) {
            warningHtml = `
            <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 0.9em; border: 1px solid #ffeeba; font-weight: normal;">
                ⚠️ <strong>Nota:</strong> Algunos productos no se encontraron en ambos supermercados. El ahorro total podría no ser exacto.
            </div>`;
        }

        let savingsText = `Ahorrarás <strong>${comp.ahorro_total}€</strong> comprando todo en ${comp.mejor_supermercado}.`;

        // --- NUEVO: Cesta Mixta ---
        if (comp.cesta_mixta && comp.cesta_mixta.total > 0) {
            savingsText += `<div style="margin-top: 10px; font-size: 1.1em; color: #8e44ad;">
            🔀 <strong>Compra Mixta Óptima:</strong> Si compras lo más barato de cada tienda, pagarás solo <strong>${comp.cesta_mixta.total}€</strong> (ahorro potencial de ${comp.cesta_mixta.ahorro_potencial}€).
        </div>`;
        }

        if (comp.mensaje_ahorro) {
            savingsText += `<div style="background: rgba(255,255,255,0.5); padding: 8px; border-radius: 6px; margin-top: 10px; font-size: 0.85em; color: #111; line-height: 1.3;">💡 <strong>Info:</strong> ${comp.mensaje_ahorro}</div>`;
        }

        banner.innerHTML = `
        ${warningHtml}
        <div style="font-size: 1.3em;">🏆 Supermercado recomendado: <strong>${comp.mejor_supermercado}</strong></div>
        <div style="font-size: 0.9em; font-weight: normal; margin-top: 5px;">
            ${savingsText}
        </div>
    `;

        banner.style.background = (comp.mejor_supermercado === 'Dia') ? '#fadbd8' : '#d4edda';
        banner.style.color = (comp.mejor_supermercado === 'Dia') ? '#721c24' : '#155724';
        banner.style.borderColor = (comp.mejor_supermercado === 'Dia') ? '#f5c6cb' : '#c3e6cb';

        // Poner Totales
        document.getElementById('m-price').innerHTML = `
        <div style="font-weight: bold;">${comp.cesta_mercadona.total.toFixed(2)} €</div>
        <div style="font-size: 0.5em; color: #0984e3;">🚀 Eficiencia: ${comp.cesta_mercadona.total_normalizado.toFixed(2)}€/kg-L</div>
    `;
        document.getElementById('d-price').innerHTML = `
        <div style="font-weight: bold;">${comp.cesta_dia.total.toFixed(2)} €</div>
        <div style="font-size: 0.5em; color: #0984e3;">🚀 Eficiencia: ${comp.cesta_dia.total_normalizado.toFixed(2)}€/kg-L</div>
    `;

        // Renderizar Filas Alineadas
        const listContainer = document.getElementById('list-container');
        listContainer.innerHTML = "";

        if (comp.filas && comp.filas.length > 0) {
            comp.filas.forEach(fila => {
                const isMixM = fila.recomendado_mixto === 'Mercadona';
                const isMixD = fila.recomendado_mixto === 'Dia';

                // Mercadona Cell
                const divM = document.createElement('div');
                divM.style.background = isMixM ? "#e8f8f5" : "#f4fbf7"; // Highlight if mixed choice
                divM.style.padding = "0 20px 12px 20px";
                divM.style.borderLeft = "1px solid #ddd";
                divM.style.borderRight = "1px solid #ddd";
                divM.innerHTML = crearHtmlElemento(fila.mercadona, 'Mercadona', isMixM);

                // Dia Cell
                const divD = document.createElement('div');
                divD.style.background = isMixD ? "#fdf2e9" : "#fff5f6"; // Highlight if mixed choice
                divD.style.padding = "0 20px 12px 20px";
                divD.style.borderLeft = "1px solid #ddd";
                divD.style.borderRight = "1px solid #ddd";
                divD.innerHTML = crearHtmlElemento(fila.dia, 'Dia', isMixD);

                listContainer.appendChild(divM);
                listContainer.appendChild(divD);
            });
        }

        // Missing Footers
        const mMiss = comp.cesta_mercadona.productos_no_encontrados;
        const dMiss = comp.cesta_dia.productos_no_encontrados;
        document.getElementById('m-missing').innerHTML = mMiss.length ? `❌ No disponible: ${mMiss.join(", ")}` : "";
        document.getElementById('d-missing').innerHTML = dMiss.length ? `❌ No disponible: ${dMiss.join(", ")}` : "";
    }

<<<<<<< Updated upstream
    function crearHtmlElemento(p, tienda, isMix = false) {
        if (!p) {
            return `
=======
    // Missing Footers
    const mMiss = comp.cesta_mercadona.productos_no_encontrados;
    const dMiss = comp.cesta_dia.productos_no_encontrados;
    document.getElementById('m-missing').innerHTML = mMiss.length ? `❌ No disponible: ${mMiss.join(", ")}` : "";
    document.getElementById('d-missing').innerHTML = dMiss.length ? `❌ No disponible: ${dMiss.join(", ")}` : "";

    // Inicializar (o actualizar) el mapa con los precios actuales
    inicializarMapa(comp.cesta_mercadona.total, comp.cesta_dia.total);
}

function crearHtmlElemento(p, tienda, isMix = false) {
    if (!p) {
        return `
>>>>>>> Stashed changes
        <div class="prod-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; color: #999; font-style: italic; height: 100%;">
            <div style="width: 50px; height: 50px; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2em;">❓</div>
            <div style="flex: 1;">No disponible en ${tienda}</div>
        </div>`;
        }

        const imgUrl = (p.imagen && p.imagen !== '')
            ? p.imagen
            : 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';

        const badgeHtml = isMix ? '<div style="font-size: 0.7em; background: #8e44ad; color: white; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 3px; font-weight: bold;">⭐ Lo más barato</div>' : '';

        const multiHtml = (p.multiplicador && p.multiplicador > 1)
            ? `<span style="color:#e67e22; font-weight:900; font-size:1.15em; margin-right:4px;">${p.multiplicador}x</span>`
            : '';

        const precioTotal = (p.multiplicador && p.multiplicador > 1) ? (p.precio * p.multiplicador).toFixed(2) : p.precio.toFixed(2);
        const precioUnitarioMeta = (p.multiplicador && p.multiplicador > 1) ? ` <span style="font-size:0.85em; color:#999; margin-left:4px; font-weight:normal;">(${p.precio.toFixed(2)}€/ud)</span>` : '';

        return `
    <div class="prod-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; height: 100%;">
        <img src="${imgUrl}" alt="${p.nombre}" 
             style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: white;">
        
        <div style="flex: 1;">
            ${badgeHtml}
            <span class="prod-name" style="display: block; font-weight: bold; font-size: 0.95em; color: #000; line-height:1.2;">${multiHtml}${p.nombre}</span>
            <span class="prod-meta" style="color: #666; font-size: 0.85em; display: flex; justify-content: space-between;">
                <span style="${p.es_formato_grande ? 'color: #d35400; font-weight: bold;' : 'font-weight: bold;'}">${precioTotal}€${precioUnitarioMeta}</span>
                <span style="color: #0984e3; font-weight: bold;">${p.precio_ref > 0 ? p.precio_ref.toFixed(2) + '€/' + p.unidad : ''}</span>
            </span>
            ${p.es_formato_grande ? '<div style="font-size: 0.7em; color: #d35400; font-weight: bold; margin-top: 2px;">⚠️ Formato Ahorro</div>' : ''}
        </div>
    </div>
    `;
    }

    // --- NUEVA FUNCIÓN PARA GUARDAR ---
    async function guardarMenuEnHistorial() {
        if (!currentMenuData || !currentMenuData.comparativa) {
            alert('Primero busca precios con tu selección para poder guardar.');
            return;
        }

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
<<<<<<< Updated upstream
=======
}

// =====================================================================
// MAPA DE SUPERMERCADOS
// =====================================================================
let mapaLeaflet = null;
let routingControl = null;

// Tiendas reales (coordenadas orientativas) de Valencia ciudad
const SUPERMERCADOS_VALENCIA = [
    // --- MERCADONA ---
    { cadena: 'mercadona', nombre: 'Mercadona Gran Vía',       lat: 39.4697, lng: -0.3755, direccion: 'Av. Marqués de Sotelo, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Sagunto',         lat: 39.4804, lng: -0.3766, direccion: 'C/ Sagunto, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Ruzafa',          lat: 39.4586, lng: -0.3737, direccion: 'Av. Giorgeta, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Campanar',        lat: 39.4847, lng: -0.3942, direccion: 'Av. Constitución, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Benimaclet',      lat: 39.4820, lng: -0.3618, direccion: 'C/ Eduardo Boscá, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Patraix',         lat: 39.4550, lng: -0.3932, direccion: 'C/ Tres Forques, Valencia' },
    { cadena: 'mercadona', nombre: 'Mercadona Quatre Carreres', lat: 39.4611, lng: -0.4015, direccion: 'Av. del Cid, Valencia' },
    // --- DÍA ---
    { cadena: 'dia', nombre: 'Día San Vicente',  lat: 39.4630, lng: -0.3810, direccion: 'C/ San Vicente Mártir, Valencia' },
    { cadena: 'dia', nombre: 'Día Russafa',      lat: 39.4599, lng: -0.3757, direccion: 'Av. de la Plata, Valencia' },
    { cadena: 'dia', nombre: 'Día Torrefiel',    lat: 39.4898, lng: -0.3770, direccion: 'C/ Torrefiel, Valencia' },
    { cadena: 'dia', nombre: 'Día Cabanyal',     lat: 39.4673, lng: -0.3546, direccion: 'Av. del Puerto, Valencia' },
    { cadena: 'dia', nombre: 'Día Campanar',     lat: 39.4861, lng: -0.3913, direccion: 'C/ Burjassot, Valencia' },
    { cadena: 'dia', nombre: 'Día Patraix',      lat: 39.4540, lng: -0.3954, direccion: 'C/ Fontanars dels Alforins, Valencia' },
];

function inicializarMapa(precioMercadona, precioDia) {
    // Guard: si Leaflet no está disponible aún, reintentar tras 300ms
    if (typeof L === 'undefined') {
        setTimeout(() => inicializarMapa(precioMercadona, precioDia), 300);
        return;
    }

    const wrapper = document.getElementById('mapa-wrapper');
    wrapper.style.display = 'block';

    // Si el mapa ya existe, solo actualizar popups y forzar redibujado
    if (mapaLeaflet) {
        setTimeout(() => mapaLeaflet.invalidateSize(), 100);
        mapaLeaflet.eachLayer(layer => {
            if (layer instanceof L.Marker) {
                const store = layer._storeData;
                if (store) {
                    layer.setPopupContent(construirPopup(store, precioMercadona, precioDia));
                }
            }
        });
        return;
    }

    // Esperar a que el navegador renderice el contenedor antes de inicializar Leaflet
    requestAnimationFrame(() => {
    setTimeout(() => {

    // Inicializar mapa centrado en Valencia
    mapaLeaflet = L.map('mapa-supermercados').setView([39.4699, -0.3763], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(mapaLeaflet);

    // Iconos personalizados
    const iconoMercadona = L.divIcon({
        className: '',
        html: `<div style="background:#009432;color:white;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid white;">🟢</div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -38]
    });

    const iconoDia = L.divIcon({
        className: '',
        html: `<div style="background:#EA2027;color:white;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid white;">🔴</div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -38]
    });

    SUPERMERCADOS_VALENCIA.forEach(store => {
        const icono = store.cadena === 'mercadona' ? iconoMercadona : iconoDia;
        const marker = L.marker([store.lat, store.lng], { icon: icono })
            .addTo(mapaLeaflet)
            .bindPopup(construirPopup(store, precioMercadona, precioDia), { maxWidth: 280 });
        marker._storeData = store;

        marker.on('click', function() {
            marker.openPopup();
        });
    });

    // Leyenda
    const leyenda = L.control({ position: 'bottomright' });
    leyenda.onAdd = () => {
        const div = L.DomUtil.create('div');
        div.innerHTML = `
            <div style="background:white;padding:10px 14px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);font-size:0.85em;line-height:1.8;">
                <div><span style="color:#009432;font-weight:bold;">●</span> Mercadona</div>
                <div><span style="color:#EA2027;font-weight:bold;">●</span> Día</div>
            </div>`;
        return div;
    };
    leyenda.addTo(mapaLeaflet);

    }); // fin setTimeout
    }); // fin requestAnimationFrame
}

function construirPopup(store, precioMercadona, precioDia) {
    const esMercadona = store.cadena === 'mercadona';
    const color = esMercadona ? '#009432' : '#EA2027';
    const precio = esMercadona ? precioMercadona : precioDia;
    const precioHtml = precio != null
        ? `<div style="font-size:1.4em;font-weight:bold;color:${color};margin:8px 0;">${precio.toFixed(2)} €</div><div style="font-size:0.8em;color:#777;">coste de tu lista de la compra</div>`
        : `<div style="color:#aaa;font-size:0.85em;margin:8px 0;">Busca precios primero</div>`;

    const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${store.lat},${store.lng}&travelmode=walking`;

    return `
        <div style="font-family:sans-serif;min-width:200px;">
            <div style="font-weight:bold;font-size:1em;color:${color};border-bottom:2px solid ${color};padding-bottom:4px;margin-bottom:6px;">${store.nombre}</div>
            <div style="color:#555;font-size:0.85em;margin-bottom:4px;">📍 ${store.direccion}</div>
            ${precioHtml}
            <div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">
                <button onclick="trazarRuta(${store.lat}, ${store.lng})"
                    style="background:#0984e3;color:white;border:none;padding:7px 12px;border-radius:5px;cursor:pointer;font-size:0.85em;font-weight:bold;">
                    🗺️ Ruta aquí
                </button>
                <a href="${gmapsUrl}" target="_blank" rel="noopener noreferrer"
                    style="background:#34495e;color:white;padding:7px 12px;border-radius:5px;font-size:0.85em;font-weight:bold;text-decoration:none;display:inline-block;">
                    📱 Google Maps
                </a>
            </div>
        </div>`;
}

function trazarRuta(destLat, destLng) {
    if (!mapaLeaflet || typeof L === 'undefined') return;

    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización. Usa el botón de Google Maps.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            const origen = [pos.coords.latitude, pos.coords.longitude];

            // Eliminar ruta anterior si existe
            if (routingControl) {
                mapaLeaflet.removeControl(routingControl);
                routingControl = null;
            }

            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(origen[0], origen[1]),
                    L.latLng(destLat, destLng)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: true,
                lineOptions: {
                    styles: [{ color: '#0984e3', weight: 5, opacity: 0.8 }]
                },
                createMarker: (i, wp) => {
                    const label = i === 0 ? '📍 Tú' : '🛒 Destino';
                    return L.marker(wp.latLng).bindPopup(label);
                }
            }).addTo(mapaLeaflet);
        },
        err => {
            alert('No se pudo obtener tu ubicación. Asegúrate de dar permiso de localización al navegador.');
            console.warn('Geolocation error:', err);
        },
        { timeout: 8000 }
    );
}
>>>>>>> Stashed changes
</script>