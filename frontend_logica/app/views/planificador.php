<?php
// app/Views/planificador.php
?>
<style>
    /* Base (Modo Oscuro / Glassmorphism por defecto) */
    .planificador-container {
        color: var(--text);
        background: rgba(17, 24, 39, .6);
        border: 1px solid #1e293b;
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, .4);
        padding: 30px;
        border-radius: 16px;
    }

    /* ============================================================
       MODO CLARO (SOBREESCRITURA)
       ============================================================ */
    [data-theme="light"] .planificador-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    [data-theme="light"] .chef-header h2 {
        color: #1e293b;
    }

    [data-theme="light"] .chef-header p {
        color: #64748b;
    }

    [data-theme="light"] .config-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    [data-theme="light"] .config-panel-title {
        color: #1e293b;
        border-bottom-color: #e2e8f0;
    }

    [data-theme="light"] .config-item label {
        color: #475569;
    }

    [data-theme="light"] .config-item input,
    [data-theme="light"] .config-item select {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    [data-theme="light"] .chef-input {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    [data-theme="light"] .dia-grupo {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    [data-theme="light"] .dia-heading {
        color: #0369a1;
        border-bottom-color: #e2e8f0;
    }

    [data-theme="light"] .dia-card {
        background: #ffffff;
        border-color: #e2e8f0;
        border-left-color: #3b82f6;
        color: #1e293b;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    [data-theme="light"] .dia-card .plato-nombre {
        color: #0f172a;
        font-weight: 800;
        font-size: 1.2em;
    }

    [data-theme="light"] .dia-card .plato-desc {
        color: #2e4a70ff !important;
        font-weight: 600;
        line-height: 1.5;
        font-size: 0.95em;
    }

    [data-theme="light"] .momento-tag {
        color: #2563eb;
        font-weight: 800;
    }

    [data-theme="light"] .dia-heading {
        color: #1e3a8a;
        border-bottom: 2px solid #cbd5e1;
    }

    [data-theme="light"] .ingredientes-meta {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #1e293b;
    }

    [data-theme="light"] .ingredientes-meta h4 {
        color: #0f172a;
        font-weight: 800;
    }

    [data-theme="light"] .ingredientes-meta ul {
        color: #1e293b;
        font-weight: 500;
    }

    [data-theme="light"] .ingredientes-meta p {
        color: #1e293b;
    }

    [data-theme="light"] .check-item {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    [data-theme="light"] .check-item label {
        color: #0f172a;
        font-weight: 600;
    }

    [data-theme="light"] .check-item:hover {
        background: #f1f5f9;
        border-color: #2563eb;
    }

    [data-theme="light"] .check-cantidad {
        background: #e2e8f0;
        color: #1e3a8a;
        font-weight: 700;
    }

    [data-theme="light"] .checklist-actions {
        background: #f1f5f9;
    }

    [data-theme="light"] .checklist-count {
        color: #1e293b;
    }

    [data-theme="light"] .btn-checklist {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #475569;
    }

    [data-theme="light"] .btn-checklist:hover {
        background: #f8fafc;
        color: #000;
    }

    #mapa-wrapper {
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
    }

    #mapa-wrapper p { color: #94a3b8; }
    .mapa-leyenda { color: #94a3b8; }

    [data-theme="light"] #mapa-wrapper {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    [data-theme="light"] #mapa-wrapper h3 { color: #0f172a; }
    [data-theme="light"] #mapa-wrapper p { color: #334155; }
    [data-theme="light"] .mapa-leyenda { color: #1e293b; font-weight: 600; }

    [data-theme="light"] #loader h3 {
        color: #1e293b;
    }

    [data-theme="light"] #loader p {
        color: #64748b;
    }

    /* Fix para el dropdown de ciudad en modo claro */
    [data-theme="light"] #ciudadSelect option {
        background-color: #ffffff;
        color: #1e293b;
    }

    .chef-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .chef-header h2 {
        color: #fff;
        font-weight: 800;
        font-size: 2rem;
        letter-spacing: -0.5px;
    }

    .chef-header p {
        color: #94a3b8;
        font-size: 1.1em;
    }

    .chef-input-group {
        display: flex;
        gap: 12px;
        max-width: 700px;
        margin: 0 auto 10px;
    }

    .chef-input {
        flex: 1;
        padding: 14px 18px;
        border-radius: 12px;
        border: 1px solid #334155;
        background: #0f172a;
        color: #e2e8f0;
        font-size: 1.1em;
        transition: all 0.3s ease;
    }

    .chef-input:focus {
        border-color: var(--pri);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        outline: none;
    }

    .btn-chef {
        background: linear-gradient(135deg, var(--pri) 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        font-size: 1.1em;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-chef:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
    }

    .btn-guardar {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        font-size: 1.05em;
        margin-top: 10px;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }

    .btn-guardar:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3);
    }

    /* Loader de cocina */
    #loader,
    #loader-busqueda {
        display: none;
        text-align: center;
        margin: 40px 0;
        color: #cbd5e1;
    }

    .spinner {
        border: 4px solid #1e293b;
        border-top: 4px solid var(--pri-light);
        border-radius: 50%;
        width: 48px;
        height: 48px;
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

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -468px 0;
        }

        100% {
            background-position: 468px 0;
        }
    }

    .menu-container {
        display: flex;
        flex-direction: column;
        gap: 40px;
        margin-bottom: 50px;
    }

    .dia-grupo {
        background: rgba(15, 23, 42, 0.4);
        padding: 25px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .dia-heading {
        font-size: 1.5em;
        font-weight: 800;
        color: var(--pri-light);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid rgba(56, 189, 248, 0.2);
        padding-bottom: 10px;
    }

    .platos-fila {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    @media (max-width: 900px) {
        .platos-fila {
            grid-template-columns: 1fr;
        }
    }

    .dia-card {
        background: rgba(30, 41, 59, 0.7);
        border-left: 4px solid var(--pri-light);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        color: #f1f5f9;
        border: 1px solid #334155;
        border-left-width: 4px;
        overflow: hidden;
        position: relative;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .dia-card:hover {
        transform: translateY(-5px);
    }

    .plato-img {
        width: calc(100% + 40px);
        height: 160px;
        margin: -20px -20px 15px -20px;
        background-color: #1e293b;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .plato-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .plato-img.loaded img {
        opacity: 1;
    }

    .plato-img::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, transparent 8%, #334155 18%, transparent 33%);
        background-size: 800px 160px;
        animation: shimmer 2s infinite linear;
        z-index: 10;
        transition: opacity 0.5s ease;
    }

    .plato-img.loaded::before {
        opacity: 0;
        pointer-events: none;
    }

    .dia-card .momento-tag {
        color: var(--pri-light);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.75em;
        margin-bottom: 5px;
        opacity: 0.8;
    }

    .dia-card .plato-nombre {
        font-weight: 800;
        color: #fff;
        margin-bottom: 10px;
        font-size: 1.15em;
        line-height: 1.2;
    }

    .dia-card .plato-desc {
        color: #94a3b8;
        font-size: 0.9em;
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
        border-radius: 12px;
        border: 1px solid #334155;
        background: #0f172a;
        color: var(--text);
    }

    .card-mercadona {
        background: rgba(0, 148, 50, 0.05);
        border-top: 5px solid #00b894;
    }

    .card-dia {
        background: rgba(234, 32, 39, 0.05);
        border-top: 5px solid #ff7675;
    }

    .price-tag {
        font-size: 1.8em;
        margin: 15px 0;
        font-weight: 800;
        color: #fff;
    }

    .prod-item {
        border-bottom: 1px solid #1e293b;
        padding: 12px 0;
        font-size: 0.95em;
        color: #cbd5e1;
    }

    .prod-name {
        font-weight: 700;
        color: #f8fafc;
        margin-bottom: 4px;
        display: block;
    }

    .prod-meta {
        color: #64748b;
        font-size: 0.9em;
        font-weight: 500;
    }

    .missing-box {
        margin-top: 15px;
        padding: 15px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 8px;
        color: #fca5a5;
        font-size: 0.9em;
    }

    .winner-banner {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid rgba(16, 185, 129, 0.3);
        font-size: 1.25em;
        font-weight: bold;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .ingredientes-meta {
        margin-top: 20px;
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 20px;
    }

    .ingredientes-meta h4 {
        margin: 0 0 12px 0;
        color: #f1f5f9;
        font-size: 1.2em;
    }

    .ingredientes-meta ul {
        margin: 0;
        padding-left: 20px;
        color: #cbd5e1;
    }

    .checklist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
        margin-top: 15px;
    }

    .check-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 10px 14px;
        color: #e2e8f0;
        transition: all 0.2s;
    }

    .check-item:hover {
        border-color: var(--pri-light);
        background: #1e293b;
    }

    .check-item input {
        width: 18px;
        height: 18px;
        accent-color: var(--pri);
        cursor: pointer;
    }

    .check-item label {
        cursor: pointer;
        flex: 1;
        font-weight: 500;
    }

    .check-cantidad {
        color: #94a3b8;
        font-size: 0.9em;
        white-space: nowrap;
        background: #1e293b;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .checklist-actions {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        background: rgba(15, 23, 42, 0.4);
        padding: 12px;
        border-radius: 10px;
    }

    .checklist-count {
        font-weight: 700;
        color: #f1f5f9;
        margin-right: auto;
        /* push buttons to the right */
    }

    .btn-checklist {
        background: #1e293b;
        color: #cbd5e1;
        border: 1px solid #334155;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-checklist:hover {
        background: #334155;
        color: #fff;
    }

    .btn-buscar {
        background: linear-gradient(135deg, var(--pri) 0%, #2563eb 100%);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.2s;
    }

    .btn-buscar:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    }

    .btn-buscar:disabled {
        background: #334155;
        color: #64748b;
        cursor: not-allowed;
        box-shadow: none;
    }

    .seccion-titulo {
        color: #fff;
    }

    [data-theme="light"] .seccion-titulo {
        color: #0f172a;
    }

    /* Config panel */
    .config-panel {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid #1e293b;
        padding: 20px 24px;
        border-radius: 12px;
        margin-bottom: 25px;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }

    .config-panel-title {
        margin-top: 0;
        font-weight: 700;
        color: var(--pri-light);
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.1em;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .config-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }

    .config-item label {
        font-size: 0.9em;
        margin-bottom: 8px;
        color: #94a3b8;
    }

    /* Customize the comparison tables */
    .comp-header {
        padding: 20px;
        border-radius: 12px 12px 0 0;
        border: 1px solid #334155;
        border-bottom: none;
    }

    .comp-header-m {
        background: rgba(0, 184, 148, 0.1);
        border-top: 5px solid #00b894;
    }

    .comp-header-d {
        background: rgba(255, 118, 117, 0.1);
        border-top: 5px solid #ff7675;
    }

    /* Pequeño Tooltip informativo */
    .info-tooltip {
        position: relative;
        display: inline-block;
        cursor: help;
        margin-left: 5px;
        color: var(--pri);
        font-weight: bold;
        font-size: 1.1em;
    }

    .info-tooltip .tooltip-text {
        visibility: hidden;
        width: 320px;
        background-color: #1e293b;
        color: #f1f5f9;
        text-align: left;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.85rem;
        line-height: 1.4;
        font-weight: normal;
        position: absolute;
        z-index: 100;
        bottom: 125%;
        left: 50%;
        margin-left: -160px;
        opacity: 0;
        transition: opacity 0.3s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        border: 1px solid #334155;
    }

    .info-tooltip .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #334155 transparent transparent transparent;
    }

    .info-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>

<div class="planificador-container">
    <div class="chef-header">
        <h2>👨‍🍳 Planificador de Menús Inteligente</h2>
        <p>Dime qué te apetece comer esta semana y la IA creará tu menú óptimo.</p>
    </div>

    <!-- SECCIÓN DE PREFERENCIAS -->
    <div class="config-panel">
        <p class="config-panel-title">⚙️ Personalización del Menú</p>
        <div class="config-grid">
            <div class="config-item">
                <label><strong>Personas:</strong></label>
                <input type="number" id="numPersonasInput" min="1" max="12" value="2">
            </div>
            <div class="config-item">
                <label><strong>Días:</strong></label>
                <input type="number" id="numDiasInput" min="1" max="14" value="7">
            </div>
            <div class="config-item">
                <label><strong>Dieta:</strong></label>
                <select id="dietaSelect">
                    <option value="Equilibrada">🥗 Equilibrada</option>
                    <option value="Vegana">🌱 Vegana</option>
                    <option value="Vegetariana">🥚 Vegetariana</option>
                    <option value="Sin Gluten">🌾 Sin Gluten</option>
                </select>
            </div>
            <div class="config-item">
                <label><strong>Objetivo:</strong></label>
                <select id="objetivoSelect">
                    <option value="Ahorro">💰 Máximo Ahorro</option>
                    <option value="Ganar músculo">💪 Ganar Músculo</option>
                    <option value="Perder peso">🏃 Perder Peso</option>
                </select>
            </div>
            <div class="config-item">
                <label><strong>Ciudad:</strong></label>
                <select id="ciudadSelect" onchange="cambiarCiudadMapa(this.value)">
                    <option value="Valencia" selected>🏙️ Valencia</option>
                    <option value="Madrid">🏙️ Madrid</option>
                    <option value="Barcelona">🏙️ Barcelona</option>
                    <option value="Sevilla">🏙️ Sevilla</option>
                    <option value="Malaga">🏙️ Málaga</option>
                    <option value="Zaragoza">🏙️ Zaragoza</option>
                    <option value="Bilbao">🏙️ Bilbao</option>
                </select>
            </div>
            <div class="config-item" style="grid-column: span 2;">
                <label><strong>Alergias (separadas por comas):</strong></label>
                <input type="text" id="alergiasInput" placeholder="Ej: Nueces, Marisco, Lactosa">
            </div>
            <div class="config-item" style="grid-column: span 2;">
                <label><strong>Ingredientes que ya tienes:</strong></label>
                <input type="text" id="despensaInput" placeholder="Ej: sal, aceite, arroz">
            </div>
        </div>
    </div>

    <div class="chef-input-group">
        <input type="text" id="prompt-chef" class="chef-input" placeholder="Ej: Quiero cenar vegetariano 3 días..."
            onkeypress="manejarEnter(event)">
        <button class="btn-chef" onclick="pedirMenu()">Planificar ✨</button>
    </div>

    <div id="loader">
        <div class="spinner"></div>
        <h3 style="color: #f8fafc; margin-top: 10px;">El Chef está pensando...</h3>
        <p style="color: #94a3b8;">Inventando recetas y buscando precios (puede tardar unos 10-15 segundos).</p>
    </div>

    <div id="resultados">
        <div style="text-align: center; margin-bottom: 35px;">
            <button id="btn-guardar" class="btn-guardar" onclick="guardarMenuEnHistorial()">💾 Guardar Menú y Lista en
                Historial</button>
            <span id="mensaje-guardado"
                style="color: #10b981; font-weight: 700; font-size: 1.1em; display: none; margin-left: 15px;">✅
                ¡Guardado en tu Dashboard!</span>
        </div>

        <h3 class="seccion-titulo"
            style="margin-bottom: 20px; font-size: 1.5em; display:flex; align-items:center; gap: 8px;">🍽️ Tu Menú
            Personalizado</h3>
        <div id="menu-container" class="menu-grid"></div>
        <div id="ingredientes-meta" class="ingredientes-meta" style="display:none;"></div>

        <h3 class="seccion-titulo"
            style="margin-bottom: 20px; margin-top: 40px; font-size: 1.5em; display:flex; align-items:center; gap: 8px;">
            🛒 Tu Lista de la Compra</h3>

        <div id="checklist-wrapper" class="ingredientes-meta" style="display:none; margin-top: 25px;">
            <h4 style="margin-bottom: 15px;">Selecciona los ingredientes que quieres comprar</h4>
            <div id="checklist-grid" class="checklist-grid"></div>
            <div class="checklist-actions">
                <span class="checklist-count">Seleccionados: <span id="checklist-count">0</span></span>
                <button type="button" class="btn-checklist" onclick="seleccionarTodoChecklist()">Seleccionar
                    todo</button>
                <button type="button" class="btn-checklist" onclick="limpiarChecklist()">Quitar todo</button>
                <button type="button" id="btn-buscar-checklist" class="btn-buscar" onclick="buscarConChecklist()"
                    disabled>Buscar precios selecionados</button>
            </div>
            <div id="checklist-excluidos" style="margin-top:15px; color:#34d399; font-weight: 500;"></div>
        </div>

        <div id="loader-busqueda">
            <div class="spinner"></div>
            <p style="margin:10px 0 0; font-size: 1.1em; color: #f8fafc;">Buscando precios en los supermercados...</p>
        </div>

        <div id="comparativa-wrapper" style="display:none; margin-top: 30px;">
            <div id="winner-banner" class="winner-banner"></div>

            <div id="comparison-grid"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; margin-top: 20px;">
                <!-- Mercadona Header -->
                <div class="comp-header comp-header-m">
                    <h3 style="color: #00b894; margin: 0; padding-bottom: 8px; font-size: 1.4em;">Mercadona</h3>
                    <div id="m-price" class="price-tag">0.00 €</div>
                </div>
                <!-- Dia Header -->
                <div class="comp-header comp-header-d">
                    <h3 style="color: #ff7675; margin: 0; padding-bottom: 8px; font-size: 1.4em;">Dia</h3>
                    <div id="d-price" class="price-tag">0.00 €</div>
                </div>

                <!-- Filas de productos -->
                <div id="list-container"
                    style="grid-column: 1 / span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; background: rgba(15,23,42,0.4); padding: 15px; border-left: 1px solid #334155; border-right: 1px solid #334155;">
                </div>

                <!-- Footers (No encontrados) -->
                <div id="m-missing"
                    style="background: rgba(0, 184, 148, 0.05); padding: 20px; border-radius: 0 0 12px 12px; border: 1px solid #334155; border-top: none; color: #fca5a5; font-size: 0.95em;">
                </div>
                <div id="d-missing"
                    style="background: rgba(255, 118, 117, 0.05); padding: 20px; border-radius: 0 0 12px 12px; border: 1px solid #334155; border-top: none; color: #fca5a5; font-size: 0.95em;">
                </div>
            </div>
        </div>

    </div>
    <!-- FIN RESULTADOS -->

    <!-- ===== SECCIÓN MAPA: ABAJO DEL TODO Y SIEMPRE VISIBLE ===== -->
    <div id="mapa-wrapper" class="glass-card" style="margin-top: 45px; padding: 25px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h3 class="seccion-titulo" style="margin: 0; font-size: 1.4em; display:flex; align-items:center; gap: 8px;">🗺️ Supermercados Cerca de Ti</h3>
            <button id="btn-smart-search" class="btn-buscar" style="margin: 0; padding: 8px 16px; font-size: 0.9em;" onclick="sugerirTiendasCercanas()">
                ✨ Sugerir por mi ubicación real
            </button>
        </div>
        <p class="mapa-desc" style="margin-bottom: 20px; line-height: 1.6;">
            Encuentra tiendas de Mercadona y Dia cerca de tu zona. Haz clic en un marcador para ver precios (si ya has buscado precios antes) o trazar una ruta.
        </p>

        <div style="position: relative;">
            <div id="mapa-supermercados"
                style="height: 480px; border-radius: 12px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.3); background: #0f172a; overflow: hidden;">
            </div>
            <!-- Panel de información de ruta flotante -->
            <div id="info-ruta" style="position: absolute; top: 15px; right: 15px; z-index: 100; display: none;"></div>
        </div>

        <div id="mapa-leyenda" class="mapa-leyenda" style="margin-top: 15px; display: flex; gap: 20px; justify-content: center; font-size: 0.85em;">
            <span style="display: flex; align-items: center; gap: 6px;"><span style="background:#009432; width:12px; height:12px; border-radius:50%;"></span> Mercadona</span>
            <span style="display: flex; align-items: center; gap: 6px;"><span style="background:#EA2027; width:12px; height:12px; border-radius:50%;"></span> Dia</span>
            <span style="display: flex; align-items: center; gap: 6px;"><span style="background:#3b82f6; width:12px; height:12px; border-radius:50%;"></span> Tu ubicación</span>
        </div>
    </div>
    <!-- ===== FIN MAPA ===== -->

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

        // INICIALIZAR MAPA SIEMPRE AL CARGAR
        setTimeout(() => {
            inicializarMapa(null, null, document.getElementById('ciudadSelect').value || 'Valencia');
        }, 500);

        if (savedMenu) {
            try {
                const data = JSON.parse(savedMenu);
                currentMenuData = data;
                renderizarMenu(data);
                document.getElementById('resultados').style.display = 'block';

                // Si hay datos guardados, actualizamos los precios en el mapa
                if (data.comparativa) {
                    finalizarCargaMapa(data.comparativa.cesta_mercadona.total, data.comparativa.cesta_dia.total, data.ciudad);
                }
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
            document.getElementById('mapa-wrapper').style.display = 'none'; // ocultar mapa anterior

        } catch (error) {
            document.getElementById('loader').style.display = 'none';
            alert('Error del Chef: ' + error.message);
        }
    }

    function renderizarMenu(data) {
        const menuDiv = document.getElementById('menu-container');

        // Agrupar por día para visualización horizontal
        const diasAgrupados = {};
        data.menu.forEach(item => {
            const diaNombre = item.dia.split(' - ')[0] || item.dia;
            if (!diasAgrupados[diaNombre]) diasAgrupados[diaNombre] = [];
            diasAgrupados[diaNombre].push(item);
        });

        menuDiv.innerHTML = Object.keys(diasAgrupados).map(dia => {
            const platos = diasAgrupados[dia];
            // Normalizar el nombre del día para usarlo en IDs (elimina acentos y espacios)
            const diaId = dia.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\W/g, '');

            return `
            <div class="dia-grupo">
                <div class="dia-heading">
                    <i class="fas fa-calendar-alt"></i> ${dia}
                </div>
                <div class="platos-fila">
                    ${platos.map((p, idx) => {
                const cardId = `img-${diaId}-${idx}`;
                const momento = p.dia.split(' - ')[1] || '';
                return `
                        <div class="dia-card">
                            <div class="momento-tag">${momento}</div>
                            <div class="plato-img">
                                ${p.imagen ? `
                                    <img src="${p.imagen}" 
                                         alt="${p.plato}" 
                                         onload="this.parentElement.classList.add('loaded')"
                                         onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop'; this.parentElement.classList.add('loaded');">
                                ` : ''}
                            </div>
                            <div class="plato-nombre">${p.plato}</div>
                            <div class="plato-desc">${p.descripcion}</div>
                        </div>
                        `;
            }).join('')}
                </div>
            </div>
            `;
        }).join('');

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

            let response = await fetch('http://localhost:8001/comparar-lista-compra', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const detalle = await response.text();
                throw new Error(detalle || `Error HTTP ${response.status}`);
            }

            const comp = await response.json();
            if (comp.error) throw new Error(comp.error);

            currentMenuData.comparativa = comp;
            renderizarComparativa(comp, ciudad);
            document.getElementById('comparativa-wrapper').style.display = 'block';
        } catch (error) {
            alert('Error al buscar precios: ' + error.message);
        } finally {
            document.getElementById('loader-busqueda').style.display = 'none';
        }
    }

    function renderizarComparativa(comp, ciudad) {
        const banner = document.getElementById('winner-banner');

        let warningHtml = "";
        if (!comp.comparativa_completa) {
            warningHtml = `
            <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 0.9em; border: 1px solid #ffeeba; font-weight: normal;">
                ⚠️ <strong>Nota:</strong> Algunos productos no se encontraron en ambos supermercados. El ahorro total podría no ser exacto.
            </div>`;
        }

        let isMoreExpensiveTicket = false;
        if (comp.mejor_supermercado === 'Dia' && comp.cesta_dia.total > comp.cesta_mercadona.total) isMoreExpensiveTicket = true;
        if (comp.mejor_supermercado === 'Mercadona' && comp.cesta_mercadona.total > comp.cesta_dia.total) isMoreExpensiveTicket = true;

        let savingsText = "";
        if (isMoreExpensiveTicket) {
            savingsText = `Invertirás <strong>${comp.ahorro_total}€ más</strong> en caja, pero obtienes mucho más producto por kilo/litro.`;
        } else {
            savingsText = `Ahorrarás <strong>${comp.ahorro_total}€</strong> comprando todo en ${comp.mejor_supermercado}.`;
        }
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
        <div style="font-size: 0.5em; color: #0984e3; display: flex; align-items: center; justify-content: center; gap: 4px;">
            <span>⚖️ Coste Proporcional: ${comp.cesta_mercadona.total_normalizado.toFixed(2)}€</span>
            <span class="info-tooltip">ⓘ
                <span class="tooltip-text">El <b>Coste Proporcional</b> calcula matemáticamente lo que cuestan los gramos exactos que vas a consumir en tu menú. Sirve para comparar el precio real, ignorando si el supermercado te obliga a comprar envases de 5Kg o te permite comprar a granel.</span>
            </span>
        </div>
        `;
        document.getElementById('d-price').innerHTML = `
        <div style="font-weight: bold;">${comp.cesta_dia.total.toFixed(2)} €</div>
        <div style="font-size: 0.5em; color: #0984e3; display: flex; align-items: center; justify-content: center; gap: 4px;">
            <span>⚖️ Coste Proporcional: ${comp.cesta_dia.total_normalizado.toFixed(2)}€</span>
            <span class="info-tooltip">ⓘ
                <span class="tooltip-text">El <b>Coste Proporcional</b> calcula matemáticamente lo que cuestan los gramos exactos que vas a consumir en tu menú. Sirve para comparar el precio real, ignorando si el supermercado te obliga a comprar envases de 5Kg o te permite comprar a granel.</span>
            </span>
        </div>
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
        const mMiss = comp.cesta_mercadona.productos_no_encontrados || [];
        const dMiss = comp.cesta_dia.productos_no_encontrados || [];
        document.getElementById('m-missing').innerHTML = mMiss.length ? `❌ No disponible: ${mMiss.join(", ")}` : "";
        document.getElementById('d-missing').innerHTML = dMiss.length ? `❌ No disponible: ${dMiss.join(", ")}` : "";

        // Inicializar (o actualizar) el mapa con los precios actuales y la ciudad seleccionada
        inicializarMapa(comp.cesta_mercadona.total, comp.cesta_dia.total, ciudad);
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

    async function guardarMenuEnHistorial() {
        if (!currentMenuData || !currentMenuData.comparativa) {
            alert('Primero busca precios con tu selección para poder guardar.');
            return;
        }

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

<!-- Estilos para los Marcadores Avanzados -->
<style>
    .marker-pin {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: white;
        border: 2px solid #fff;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .marker-pin:hover {
        transform: scale(1.2) translateY(-5px);
    }

    .marker-pin img {
        width: 75%;
        height: 75%;
        object-fit: contain;
    }

    .marker-mercadona {
        border-color: #009432;
        box-shadow: 0 0 20px rgba(0, 148, 50, 0.5);
    }

    .marker-dia {
        border-color: #EA2027;
        box-shadow: 0 0 20px rgba(234, 32, 39, 0.5);
    }

    .pulse-effect {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        opacity: 0.5;
        animation: pulse 2s infinite;
        z-index: -1;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.5;
        }

        100% {
            transform: scale(2.5);
            opacity: 0;
        }
    }
</style>

<!-- Google Maps SDK v3.56+ con librerías necesarias -->
<script
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo getenv('GOOGLE_MAPS_API_KEY'); ?>&libraries=marker,places&v=weekly"></script>

<script>
    // =====================================================================
    // MAPA DE SUPERMERCADOS CON GOOGLE MAPS PLATFORM
    // =====================================================================
    let googleMap = null;
    let directionsService = null;
    let directionsRenderer = null;
    let googleMarkers = [];
    let currentInfoWindow = null;

    const CENTROS_CIUDAD = {
        'Valencia': { lat: 39.4699, lng: -0.3763 },
        'Madrid': { lat: 40.4168, lng: -3.7038 },
        'Barcelona': { lat: 41.3851, lng: 2.1734 },
        'Sevilla': { lat: 37.3891, lng: -5.9845 },
        'Malaga': { lat: 36.7213, lng: -4.4214 },
        'Zaragoza': { lat: 41.6488, lng: -0.8891 },
        'Bilbao': { lat: 43.2630, lng: -2.9350 }
    };

    async function sugerirTiendasCercanas() {
        if (typeof google === 'undefined') {
            setTimeout(sugerirTiendasCercanas, 500);
            return;
        }

        if (!navigator.geolocation) return alert("Geolocalización no soportada.");

        const btn = document.getElementById('btn-smart-search');
        const originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Buscando en Google Maps...";
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            async pos => {
                const uLat = pos.coords.latitude;
                const uLng = pos.coords.longitude;
                const userLoc = { lat: uLat, lng: uLng };

                try {
                    if (!googleMap) inicializarMapa(null, null);

                    const placesService = new google.maps.places.PlacesService(googleMap);

                    const buscarEnGoogle = (brand) => {
                        return new Promise((resolve) => {
                            const request = {
                                location: userLoc,
                                radius: '15000', // 15km
                                query: brand === 'Dia' ? 'Supermercados DIA' : `supermercado ${brand}`
                            };
                            placesService.textSearch(request, (results, status) => {
                                if (status === google.maps.places.PlacesServiceStatus.OK) {
                                    // Filtro estricto para evitar Dialprix u otros falsos positivos
                                    const filtrados = results.filter(r => {
                                        const name = r.name.toLowerCase();
                                        const brandLower = brand.toLowerCase();
                                        if (brandLower === 'dia') {
                                            // No queremos Dialprix
                                            if (name.includes('dialprix')) return false;
                                            // Queremos que contenga "dia" como palabra o inicio
                                            return name === 'dia' || name.includes('supermercados dia') || name.startsWith('dia ') || name.includes(' la plaza de dia');
                                        }
                                        return name.includes(brandLower);
                                    });

                                    resolve(filtrados.map(r => ({
                                        cadena: brand.toLowerCase(),
                                        nombre: r.name, // Usamos el nombre real de Google para el popup
                                        lat: r.geometry.location.lat(),
                                        lng: r.geometry.location.lng(),
                                        direccion: r.formatted_address || r.name,
                                        placeId: r.place_id
                                    })));
                                } else {
                                    resolve([]);
                                }
                            });
                        });
                    };

                    const [mRes, dRes] = await Promise.all([buscarEnGoogle('Mercadona'), buscarEnGoogle('Dia')]);
                    const todas = [...mRes, ...dRes];

                    if (todas.length === 0) throw new Error("Google no ha encontrado tiendas cerca de tu posición.");

                    // Calcular distancias reales y ordenar
                    todas.forEach(t => {
                        t.distancia = calcularDistancia(uLat, uLng, t.lat, t.lng);
                    });
                    todas.sort((a, b) => a.distancia - b.distancia);

                    const masCercanos = [];
                    const foundM = todas.find(t => t.cadena === 'mercadona');
                    const foundD = todas.find(t => t.cadena === 'dia');
                    if (foundM) masCercanos.push(foundM);
                    if (foundD) masCercanos.push(foundD);

                    let pM = currentMenuData?.comparativa?.cesta_mercadona?.total || null;
                    let pD = currentMenuData?.comparativa?.cesta_dia?.total || null;

                    finalizarCargaMapa(pM, pD, 'Valencia', masCercanos);

                    // Centrar en usuario y tiendas
                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(userLoc);
                    masCercanos.forEach(s => bounds.extend({ lat: s.lat, lng: s.lng }));
                    googleMap.fitBounds(bounds);

                    // Marcador de usuario con estilo premium
                    const userMarkerEl = document.createElement('div');
                    userMarkerEl.className = 'marker-pin';
                    userMarkerEl.style.backgroundColor = '#3b82f6';
                    userMarkerEl.style.boxShadow = '0 0 25px rgba(59,130,246,0.8)';
                    userMarkerEl.innerHTML = '<div class="pulse-effect"></div><div style="width:10px; height:10px; background:white; border-radius:50%;"></div>';

                    new google.maps.marker.AdvancedMarkerElement({
                        position: userLoc,
                        map: googleMap,
                        content: userMarkerEl,
                        title: "Tu posición"
                    });

                } catch (error) {
                    alert("Aviso: " + error.message);
                } finally {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            },
            err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert("Error de GPS: " + err.message);
            },
            { timeout: 10000 }
        );
    }

    function inicializarMapa(precioMercadona, precioDia, ciudadSelection = 'Valencia') {
        if (typeof google === 'undefined') {
            setTimeout(() => inicializarMapa(precioMercadona, precioDia, ciudadSelection), 500);
            return;
        }
        const wrapper = document.getElementById('mapa-wrapper');
        if (wrapper) {
            wrapper.style.display = 'block';
        }

        const coordsCentro = CENTROS_CIUDAD[ciudadSelection] || CENTROS_CIUDAD['Valencia'];

        if (!googleMap) {
            googleMap = new google.maps.Map(document.getElementById('mapa-supermercados'), {
                center: coordsCentro,
                zoom: 13,
                mapId: '<?php echo getenv("MAP_ID"); ?>', // MAP ID Premium para Advanced Markers
                disableDefaultUI: false,
                zoomControl: true,
                styles: [
                    { "featureType": "all", "elementType": "geometry", "stylers": [{ "color": "#1e293b" }] },
                    { "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "color": "#94a3b8" }] },
                    { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [{ "visibility": "off" }] },
                    { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{ "color": "#334155" }, { "weight": 1.2 }] },
                    { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#0f172a" }] },
                    { "featureType": "poi", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                    { "featureType": "road", "elementType": "geometry", "stylers": [{ "color": "#1e293b" }] },
                    { "featureType": "road.highway", "elementType": "geometry", "stylers": [{ "color": "#334155" }] },
                    { "featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [{ "color": "#1e293b" }, { "weight": 0.1 }] },
                    { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#334155" }] },
                    { "featureType": "transit", "elementType": "geometry", "stylers": [{ "color": "#1e293b" }] },
                    { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#020617" }] }
                ]
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: googleMap,
                suppressMarkers: true,
                polylineOptions: { strokeColor: "#3b82f6", strokeWeight: 6, strokeOpacity: 0.8 }
            });
        }

        finalizarCargaMapa(precioMercadona, precioDia, ciudadSelection);
    }

    function cambiarCiudadMapa(nuevaCiudad) {
        if (!googleMap) return;
        const coords = CENTROS_CIUDAD[nuevaCiudad];
        if (coords) {
            googleMap.setCenter(coords);
            googleMap.setZoom(13);
            // Actualizar marcadores locales para la nueva ciudad
            let pM = currentMenuData?.comparativa?.cesta_mercadona?.total || null;
            let pD = currentMenuData?.comparativa?.cesta_dia?.total || null;
            finalizarCargaMapa(pM, pD, nuevaCiudad);
        }
    }

    function finalizarCargaMapa(precioMercadona, precioDia, ciudadSelection, listaTiendasCustom = null) {
        googleMarkers.forEach(m => m.map = null);
        googleMarkers = [];
        if (directionsRenderer) directionsRenderer.setDirections({ routes: [] });

        const tiendas = listaTiendasCustom || [
            { cadena: 'mercadona', nombre: 'Mercadona Local', lat: CENTROS_CIUDAD[ciudadSelection].lat + 0.005, lng: CENTROS_CIUDAD[ciudadSelection].lng + 0.005, direccion: 'Cerca del centro' },
            { cadena: 'dia', nombre: 'Día Local', lat: CENTROS_CIUDAD[ciudadSelection].lat - 0.005, lng: CENTROS_CIUDAD[ciudadSelection].lng - 0.005, direccion: 'Cerca del centro' }
        ];

        tiendas.forEach(store => {
            // Crear elemento HTML para el marcador avanzado
            const pinEl = document.createElement('div');
            pinEl.className = `marker-pin marker-${store.cadena}`;

            const domain = store.cadena === 'mercadona' ? 'mercadona.es' : 'dia.es';
            const logoUrl = `https://www.google.com/s2/favicons?domain=${domain}&sz=64`;

            pinEl.innerHTML = `<div class="pulse-effect"></div><img src="${logoUrl}" alt="${store.nombre}" style="border-radius: 4px;">`;

            const marker = new google.maps.marker.AdvancedMarkerElement({
                position: { lat: store.lat, lng: store.lng },
                map: googleMap,
                content: pinEl,
                title: store.nombre
            });

            marker.addListener('click', () => {
                if (currentInfoWindow) currentInfoWindow.close();
                const content = construirPopup(store, precioMercadona, precioDia);
                currentInfoWindow = new google.maps.InfoWindow({ content });
                currentInfoWindow.open(googleMap, marker);
            });

            googleMarkers.push(marker);
        });
    }

    function trazarRuta(destLat, destLng) {
        if (!navigator.geolocation) return alert("GPS no disponible");

        navigator.geolocation.getCurrentPosition(pos => {
            const uLat = pos.coords.latitude;
            const uLng = pos.coords.longitude;
            const request = {
                origin: { lat: uLat, lng: uLng },
                destination: { lat: parseFloat(destLat), lng: parseFloat(destLng) },
                travelMode: 'DRIVING'
            };

            directionsService.route(request, (result, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                    if (currentInfoWindow) currentInfoWindow.close();

                    // Extraer info de la ruta
                    const route = result.routes[0].legs[0];
                    const infoPanel = document.getElementById('info-ruta');
                    if (infoPanel) {
                        infoPanel.style.display = 'block';
                        infoPanel.innerHTML = `
                        <div style="background: rgba(255,255,255,0.95); padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border: 1px solid #ddd; min-width: 200px; backdrop-filter: blur(10px);">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                <strong style="color:#1e293b; font-size:1.1em;">Resumen de Ruta</strong>
                                <button onclick="document.getElementById('info-ruta').style.display='none'" style="border:none; background:none; cursor:pointer; font-size:1.2em; color:#999;">&times;</button>
                            </div>
                            <div style="display:flex; gap:15px; margin-bottom:12px;">
                                <div>
                                    <div style="font-size:0.7em; color:#64748b; text-transform:uppercase; font-weight:700;">Tiempo</div>
                                    <div style="font-size:1.2em; font-weight:800; color:#3b82f6;">${route.duration.text}</div>
                                </div>
                                <div style="border-left: 1px solid #e2e8f0; padding-left:15px;">
                                    <div style="font-size:0.7em; color:#64748b; text-transform:uppercase; font-weight:700;">Distancia</div>
                                    <div style="font-size:1.2em; font-weight:800; color:#1e293b;">${route.distance.text}</div>
                                </div>
                            </div>
                            <a href="https://www.google.com/maps/dir/?api=1&origin=${uLat},${uLng}&destination=${destLat},${destLng}&travelmode=driving" 
                               target="_blank"
                               style="display:block; text-align:center; background:#10b981; color:white; text-decoration:none; padding:10px; border-radius:8px; font-weight:700; font-size:0.9em;">
                               🚀 Iniciar Navegación GPS
                            </a>
                        </div>
                    `;
                    }
                } else {
                    alert("No se pudo calcular la ruta: " + status);
                }
            });
        });
    }

    function construirPopup(store, precioMercadona, precioDia) {
        const precio = store.cadena === 'mercadona' ? precioMercadona : precioDia;
        const color = store.cadena === 'mercadona' ? '#009432' : '#EA2027';
        const domain = store.cadena === 'mercadona' ? 'mercadona.es' : 'dia.es';
        const logoUrl = `https://www.google.com/s2/favicons?domain=${domain}&sz=64`;

        return `
        <div style="font-family: 'Inter', sans-serif; padding: 10px; min-width: 220px; color: #1e293b;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <img src="${logoUrl}" style="width:24px; height:24px; object-fit: contain; border-radius: 4px;">
                <div style="color: ${color}; font-weight: 800; font-size: 1.1em;">${store.nombre}</div>
            </div>
            <div style="color: #64748b; font-size: 0.85em; margin-bottom: 12px; line-height:1.4;">${store.direccion}</div>
            ${precio ? `
                <div style="background: #f1f5f9; padding: 10px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid ${color};">
                    <div style="font-size: 0.7em; color: #94a3b8; text-transform: uppercase; font-weight:700;">Coste de tu lista</div>
                    <div style="font-size: 1.3em; font-weight: 800; color: #0f172a;">${precio.toFixed(2)} €</div>
                </div>
            ` : ''}
            <button onclick="trazarRuta(${store.lat}, ${store.lng})" 
                    style="width: 100%; background: ${color}; color: white; border: none; padding: 10px; border-radius: 6px; font-weight: 700; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition: opacity 0.2s;">
                🚗 Calcular Ruta
            </button>
        </div>
    `;
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
</script>