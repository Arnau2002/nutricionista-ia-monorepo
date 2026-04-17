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

    .ingredientes-meta { margin-top: 12px; background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; }
    .ingredientes-meta h4 { margin: 0 0 15px 0; color: #2c3e50; font-size: 1.1em; }
    
    /* Checklist de ingredientes */
    .ingredientes-checklist { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px; margin-bottom: 20px; }
    .ingrediente-item { display: flex; align-items: center; gap: 10px; padding: 12px; background: white; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
    .ingrediente-item:hover { background: #f0f0f0; border-color: #0984e3; }
    .ingrediente-item input[type="checkbox"] { cursor: pointer; width: 18px; height: 18px; accent-color: #0984e3; }
    .ingrediente-item.checked { background: #e8f4f8; border-color: #0984e3; }
    .ingrediente-label { flex: 1; cursor: pointer; color: #000; font-weight: 500; }
    .ingrediente-cant { color: #666; font-size: 0.85em; margin-left: auto; }
    
    /* Barra de selección */
    .selection-bar { background: #e3f2fd; border: 1px solid #0984e3; border-radius: 6px; padding: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .selection-count { font-weight: bold; color: #0984e3; }
    .selection-actions { display: flex; gap: 10px; }
    
    /* Botón Buscar Precios */
    .btn-buscar-precios { background: #0984e3; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1em; transition: all 0.2s; }
    .btn-buscar-precios:hover { background: #0769b5; }
    .btn-buscar-precios:disabled { background: #ccc; cursor: not-allowed; }
    .btn-seleccionar-todo { background: #27ae60; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.9em; }
    .btn-seleccionar-todo:hover { background: #219150; }
    .btn-desseleccionar-todo { background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.9em; }
    .btn-desseleccionar-todo:hover { background: #c0392b; }
    
    /* Área de comparativa (inicialmente oculta) */
    .comparativa-section { display: none; margin-top: 30px; }
    .comparativa-section.visible { display: block; }
    
    .ingredientes-meta ul { margin: 0; padding-left: 18px; color: #222; }
</style>

<div class="card planificador-container" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="chef-header">
        <h2>👨‍🍳 Planificador de Menús Inteligente</h2>
        <p style="color:#555; font-size: 1.1em;">Dime qué te apetece comer esta semana y la IA creará el menú y buscará los precios.</p>
    </div>

    <!-- SECCIÓN DE PREFERENCIAS -->
    <div style="background: #fdfefe; border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
        <p style="margin-top:0; font-weight:bold; color: #2ecc71; text-align: center;">⚙️ Personalización del Menú</p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 100px;">
                <label><strong>Personas:</strong></label>
                <input type="number" id="numPersonasInput" min="1" max="12" value="2" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label><strong>Días:</strong></label>
                <input type="number" id="numDiasInput" min="1" max="14" value="7" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
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
                <label><strong>Alergias (separadas por comas):</strong></label>
                <input type="text" id="alergiasInput" placeholder="Ej: Nueces, Marisco, Lactosa" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="flex: 2; min-width: 250px;">
                <label><strong>Ingredientes que ya tienes:</strong></label>
                <input type="text" id="despensaInput" placeholder="Ej: sal, aceite, arroz" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ccc;">
            </div>
        </div>
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
        <div id="ingredientes-section" style="display:none;">
            <div id="ingredientes-checklist" class="ingredientes-meta"></div>
            
            <div class="selection-bar">
                <div class="selection-count">
                    Seleccionados: <span id="selected-count">0</span> ingredientes
                </div>
                <div class="selection-actions">
                    <button class="btn-seleccionar-todo" onclick="seleccionarTodos()">✅ Todos</button>
                    <button class="btn-desseleccionar-todo" onclick="deseleccionarTodos()">❌ Ninguno</button>
                    <button class="btn-buscar-precios" id="btn-buscar-precios" onclick="buscarPreciosSeleccionados()" disabled>
                        🔍 Buscar Precios
                    </button>
                </div>
            </div>
        </div>

        <div id="loader-busqueda" style="display: none; text-align: center; margin: 40px 0; color: #333;">
            <div class="spinner"></div>
            <h3 style="color: #2c3e50;">Buscando precios en los supermercados...</h3>
            <p style="color: #555;">Esto puede tardar unos segundos.</p>
        </div>

        <div id="comparativa-section" class="comparativa-section">
            <div id="winner-banner" class="winner-banner"></div>

            <div id="comparison-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; margin-top: 20px;">
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

                <!-- Filas de productos -->
                <div id="list-container" style="grid-column: 1 / span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
                </div>

                <!-- Footers (No encontrados) -->
                <div id="m-missing" style="background: #f4fbf7; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
                <div id="d-missing" style="background: #fff5f6; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
            </div>
        </div>

    </div>
</div>

<script>
// Variable global para guardar los datos de la IA temporalmente
let currentMenuData = null;

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
        } catch(e) {}
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

    const checklistDiv = document.getElementById('ingredientes-checklist');
    const ingredientes = Array.isArray(data.ingredientes_limpios) ? data.ingredientes_limpios : [];
    const excluidos = Array.isArray(data.ingredientes_excluidos_despensa) ? data.ingredientes_excluidos_despensa : [];
    const personas = data.num_personas || 2;

<<<<<<< Updated upstream
    if (ingredientes.length > 0 || excluidos.length > 0) {
        const topIngredientes = ingredientes.map(i => {
            if (typeof i === 'string') return `<li>${i}</li>`;
            const frecuenciaTxt = i.frecuencia_menu ? ` (${i.frecuencia_menu} usos)` : '';
            return `<li><strong>${i.nombre}</strong>: ${i.cantidad ?? '-'}${i.unidad ?? ''}${frecuenciaTxt}</li>`;
=======
    if (ingredientes.length > 0) {
        // Crear HTML con checkboxes
        const ingredientesHtml = ingredientes.map((i, idx) => {
            const nombre = typeof i === 'string' ? i : i.nombre;
            const cantidad = typeof i === 'string' ? '' : (i.cantidad ?? '');
            const frecuencia = typeof i === 'string' ? '' : (i.frecuencia_menu ? ` (${i.frecuencia_menu} usos)` : '');
            
            return `
                <label class="ingrediente-item" onclick="event.stopPropagation()">
                    <input type="checkbox" class="ingrediente-checkbox" data-idx="${idx}" onchange="actualizarContador()" checked>
                    <span class="ingrediente-label">${nombre}${frecuencia}</span>
                    ${cantidad ? `<span class="ingrediente-cant">${cantidad}</span>` : ''}
                </label>
            `;
>>>>>>> Stashed changes
        }).join('');

        checklistDiv.innerHTML = `
            <h4>🧾 Selecciona los ingredientes que quieres comprar (${personas} persona${personas > 1 ? 's' : ''})</h4>
            <div class="ingredientes-checklist">${ingredientesHtml}</div>
            ${excluidos.length > 0 ? `<p style="margin: 10px 0 0 0; color: #2d6a4f;"><strong>✅ Ya tienes en casa:</strong> ${excluidos.join(', ')}</p>` : ''}
        `;

        document.getElementById('ingredientes-section').style.display = 'block';
        
        // Inicializar contador
        actualizarContador();
    } else {
        checklistDiv.style.display = 'none';
        document.getElementById('ingredientes-section').style.display = 'none';
    }

    // Ocultar la comparativa inicialmente
    document.getElementById('comparativa-section').classList.remove('visible');
}

function actualizarContador() {
    const checkboxes = document.querySelectorAll('.ingrediente-checkbox');
    const seleccionados = Array.from(checkboxes).filter(cb => cb.checked).length;
    document.getElementById('selected-count').textContent = seleccionados;
    document.getElementById('btn-buscar-precios').disabled = seleccionados === 0;
}

function seleccionarTodos() {
    document.querySelectorAll('.ingrediente-checkbox').forEach(cb => cb.checked = true);
    actualizarContador();
}

function deseleccionarTodos() {
    document.querySelectorAll('.ingrediente-checkbox').forEach(cb => cb.checked = false);
    actualizarContador();
}

async function buscarPreciosSeleccionados() {
    const checkboxes = document.querySelectorAll('.ingrediente-checkbox');
    const ingredientesSeleccionados = [];
    
    checkboxes.forEach((cb, idx) => {
        if (cb.checked && currentMenuData) {
            const ing = currentMenuData.ingredientes_limpios[idx];
            ingredientesSeleccionados.push(ing);
        }
    });

    if (ingredientesSeleccionados.length === 0) {
        alert('Por favor selecciona al menos un ingrediente.');
        return;
    }

    document.getElementById('loader-busqueda').style.display = 'block';
    document.getElementById('comparativa-section').classList.remove('visible');

    try {
        const response = await fetch('http://localhost:8001/comparar-lista-compra', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ingredientes: ingredientesSeleccionados,
                alergias: currentMenuData.alergias || [],
                objetivo: currentMenuData.objetivo || 'Ahorro',
                ingredientes_en_casa: currentMenuData.ingredientes_en_casa || []
            })
        });

        const comparativa = await response.json();
        
        // Guardar la comparativa en currentMenuData para poder usarla después
        currentMenuData.comparativa = comparativa;

        // Renderizar resultados
        renderizarComparativa(comparativa);
        
        document.getElementById('loader-busqueda').style.display = 'none';
        document.getElementById('comparativa-section').classList.add('visible');

    } catch (error) {
        document.getElementById('loader-busqueda').style.display = 'none';
        alert('Error al buscar precios: ' + error.message);
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

            const divM = document.createElement('div');
            divM.style.background = isMixM ? "#e8f8f5" : "#f4fbf7";
            divM.style.padding = "0 20px 12px 20px";
            divM.style.borderLeft = "1px solid #ddd";
            divM.style.borderRight = "1px solid #ddd";
            divM.innerHTML = crearHtmlElemento(fila.mercadona, 'Mercadona', isMixM);
            
            const divD = document.createElement('div');
            divD.style.background = isMixD ? "#fdf2e9" : "#fff5f6";
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

function crearHtmlElemento(p, tienda, isMix = false) {
    if (!p) {
        return `
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
        alert('Por favor genera una búsqueda de precios primero.');
        return;
    }

    const comparativa = currentMenuData.comparativa;
    const payload = {
        mejor_supermercado: comparativa.mejor_supermercado,
        ahorro_total: comparativa.ahorro_total,
        cesta_mercadona: comparativa.cesta_mercadona,
        cesta_dia: comparativa.cesta_dia,
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