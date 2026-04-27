<?php
$title = $title ?? 'Comparador Inteligente';
$view = 'home';
?>

<style>
/* --- BASE & ANIMATIONS --- */
:root {
    --pri-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    --mercadona: #16a34a;
    --dia: #dc2626;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes blobFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

/* --- UI COMPONENTS --- */
.view-home {
    max-width: 100% !important;
    width: 100%;
}

.hero-section {
    position: relative;
    padding: 160px 20px 180px;
    margin: -40px -20px 0 -20px;
    background: #060714;
    overflow: hidden;
    text-align: center;
}

/* Blobs de fondo mejorados */
.hero-section::before, .hero-section::after {
    content: '';
    position: absolute;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    filter: blur(120px);
    z-index: 1;
    opacity: 0.15;
    animation: blobFloat 20s infinite alternate;
}
.hero-section::before { background: #6366f1; top: -100px; left: -100px; }
.hero-section::after { background: #a855f7; bottom: -100px; right: -100px; }

.hero-content {
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
    animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.hero-title {
    font-size: clamp(3.5rem, 10vw, 6.5rem);
    font-weight: 900;
    letter-spacing: -4px;
    line-height: 0.9;
    margin-bottom: 30px;
    color: white;
}

.btn-premium {
    display: inline-block;
    background: var(--pri-gradient);
    color: white !important;
    padding: 24px 60px;
    border-radius: 24px;
    font-weight: 800;
    font-size: 1.4rem;
    text-decoration: none !important;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 20px 50px rgba(99, 102, 241, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-premium:hover {
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 30px 70px rgba(99, 102, 241, 0.6);
}

/* --- TRUST SECTION REDISEÑADA CON LOGOS REALES --- */
.trust-section {
    position: relative;
    z-index: 20;
    max-width: 1200px;
    margin: -80px auto 100px;
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(20px);
    padding: 50px 40px;
    border-radius: 40px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    text-align: center;
}

.brand-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 120px;
    margin-top: 30px;
}

.brand-logo-container {
    background: white;
    padding: 15px 30px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.brand-logo-container:hover {
    transform: translateY(-10px) scale(1.05);
}

.brand-logo-container.mercadona:hover { box-shadow: 0 20px 40px rgba(22, 163, 74, 0.3); }
.brand-logo-container.dia:hover { box-shadow: 0 20px 40px rgba(220, 38, 38, 0.3); }

.brand-logo-container img {
    height: 45px;
    object-fit: contain;
}

/* --- GRID DE CARACTERÍSTICAS --- */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Forzar 3 columnas */
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto 120px;
    padding: 0 20px;
}

@media (max-width: 992px) {
    .features-grid { grid-template-columns: 1fr; }
    .brand-logos { gap: 40px; flex-direction: column; }
}

.feature-card {
    background: rgba(255, 255, 255, 0.02);
    padding: 60px 40px;
    border-radius: 40px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.4s ease;
    text-align: center;
}

.feature-card:hover {
    background: rgba(255, 255, 255, 0.04);
    transform: translateY(-10px);
    border-color: rgba(99, 102, 241, 0.3);
}

.feature-icon-wrapper {
    width: 90px;
    height: 90px;
    background: var(--pri-gradient);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    font-size: 3rem;
}

.stats-bar {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin-top: 60px;
}

.stats-item strong {
    color: white;
    font-size: 1.5rem;
    display: block;
    margin-bottom: 5px;
}

/* --- LIGHT MODE OVERRIDES --- */
[data-theme="light"] .hero-section {
    background: #f8fafc;
    background-image: 
        radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.08) 0%, transparent 40%);
}

[data-theme="light"] .hero-section::before, 
[data-theme="light"] .hero-section::after {
    opacity: 0.1;
}

[data-theme="light"] .hero-title {
    color: #0f172a;
}

[data-theme="light"] .hero-section p {
    color: #475569 !important; /* Más oscuro para contraste */
    font-weight: 500;
}

[data-theme="light"] .trust-section {
    background: #ffffff;
    border-color: rgba(0, 0, 0, 0.05);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
}

[data-theme="light"] .brand-logo-container {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
}

[data-theme="light"] .feature-card {
    background: #ffffff;
    border-color: rgba(0, 0, 0, 0.05);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.02);
}

[data-theme="light"] .feature-card:hover {
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.07);
    transform: translateY(-12px);
}

[data-theme="light"] .feature-card h3 {
    color: #0f172a;
}

[data-theme="light"] .stats-item strong {
    color: #0f172a;
}

[data-theme="light"] .stats-item {
    color: #475569 !important; /* Más oscuro */
    font-weight: 600;
}

[data-theme="light"] .badge-ia {
    background: #ffffff !important;
    border-color: rgba(99, 102, 241, 0.3) !important;
    color: #4f46e5 !important;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.08);
}

/* Transición suave para secciones */
html {
    scroll-behavior: smooth;
}

section {
    transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.6s ease;
}
</style>

<?php if (!isset($_SESSION['username'])): ?>
    <section class="hero-section">
        <div class="hero-content">
            <div class="badge-ia" style="display: inline-flex; align-items: center; gap: 10px; background: rgba(99, 102, 241, 0.1); color: #818cf8; padding: 12px 28px; border-radius: 100px; font-size: 0.9rem; font-weight: 800; border: 1px solid rgba(99, 102, 241, 0.2); margin-bottom: 40px;">
                ⚡ SISTEMA DE AHORRO BASADO EN IA
            </div>
            <h1 class="hero-title">Tu cesta perfecta,<br>al mejor precio.</h1>
            <p style="font-size: 1.5rem; color: #94a3b8; max-width: 800px; margin: 0 auto 60px; line-height: 1.6;">
                Comparamos miles de productos de Mercadona y Dia para que tú solo tengas que preocuparte de ahorrar.
            </p>
            <div style="margin-bottom: 80px;">
                <a href="/?r=login" class="btn-premium">¡Empezar a Ahorrar Gratis!</a>
            </div>

            <div class="stats-bar">
                <div class="stats-item"><strong>80,696</strong> Productos</div>
                <div class="stats-item"><strong>7</strong> Ciudades</div>
                <div class="stats-item"><strong>&lt; 2s</strong> Respuesta IA</div>
            </div>
        </div>
    </section>

    <section class="trust-section">
        <p style="color: #6366f1; font-weight: 800; font-size: 0.8rem; letter-spacing: 5px; text-transform: uppercase;">Supermercados Sincronizados</p>
        <div class="brand-logos">
            <div class="brand-logo-container mercadona">
                <img src="/public/img/mercadona.png" alt="Mercadona">
            </div>
            <div class="brand-logo-container dia">
                <img src="/public/img/dia.png" alt="DIA">
            </div>
        </div>
    </section>

    <div style="text-align: center; margin-bottom: 60px;">
        <h2 style="font-size: 3rem; font-weight: 900;">¿Por qué elegirnos?</h2>
    </div>

    <section id="features" class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-wrapper">🚀</div>
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Velocidad Extrema</h3>
            <p style="color: #94a3b8; line-height: 1.7; font-size: 1.1rem;">Buscamos en las bases de datos de todos los supers en milisegundos.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper">🛡️</div>
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Datos 100% Reales</h3>
            <p style="color: #94a3b8; line-height: 1.7; font-size: 1.1rem;">Precios actualizados cada 24h directamente desde la fuente oficial.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper">✨</div>
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Ahorro Garantizado</h3>
            <p style="color: #94a3b8; line-height: 1.7; font-size: 1.1rem;">Optimiza tu lista de la compra y ahorra hasta un 25% mensual.</p>
        </div>
    </section>
<?php else: ?>
    <!-- LOGGED IN VIEW (COMPARATOR) -->
    <div class="comparator-header">
        <div class="badge-ia">
            <span class="dot"></span>
            Cerebro IA Conectado
        </div>
        <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">Compara tu cesta hoy</h2>
        <p style="color: var(--muted); max-width: 600px; margin: 0 auto;">Pega tu lista de la compra y deja que la IA busque los mejores precios por ti.</p>
    </div>

    <div class="glass-panel">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 15px;">
            <label for="listaInput" style="margin: 0; font-weight: 700;">Tu Lista de la Compra:</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0; font-size: 0.85em; font-weight: 600;">📍 Tu Ciudad:</label>
                <select id="ciudadSelect" class="glass-input" style="padding: 8px 15px; border-radius: 12px; width: auto;" onchange="saveCity()">
                    <option value="Valencia">Valencia</option>
                    <option value="Madrid">Madrid</option>
                    <option value="Barcelona">Barcelona</option>
                    <option value="Sevilla">Sevilla</option>
                    <option value="Malaga">Málaga</option>
                    <option value="Zaragoza">Zaragoza</option>
                    <option value="Bilbao">Bilbao</option>
                </select>
            </div>
        </div>

        <textarea id="listaInput" rows="6" class="glass-input"
            placeholder="Escribe aquí tu lista...&#10;Ej: 1L de leche, 1kg de arroz, detergente platos..."></textarea>
        
        <div class="quick-tags" style="margin-top: 15px;">
            <span class="quick-tag" onclick="addIngredient('Leche entera')">🥛 Leche</span>
            <span class="quick-tag" onclick="addIngredient('Docena de huevos')">🥚 Huevos</span>
            <span class="quick-tag" onclick="addIngredient('Pan de molde')">🍞 Pan</span>
            <span class="quick-tag" onclick="addIngredient('Pechuga de pollo')">🍗 Pollo</span>
            <span class="quick-tag" onclick="addIngredient('Aceite de oliva')">🫒 Aceite</span>
        </div>

        <div id="simple-error-msg" class="err" style="display:none; padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center;"></div>
        
        <div style="margin-top:30px;">
            <button class="btn-premium" id="btnComparar" onclick="compararPrecios()">
                ✨ Comparar Precios Reales Ahora
            </button>
        </div>
    </div>

    <div id="loading" style="display:none; text-align:center; margin-top:40px; padding: 40px;" class="glass-panel">
        <div class="badge-ia" style="margin: 0 auto 20px; background: rgba(67, 97, 238, 0.1); color: #4361ee;">
            <span class="dot" style="background: #4361ee;"></span>
            Consultando bases de datos...
        </div>
        <p style="font-size: 1.3rem; font-weight: 700;">Analizando precios en tiempo real...</p>
        <p style="color: var(--muted); font-size: 0.9em;">Esto puede tardar unos segundos dependiendo del tamaño de tu lista.</p>
    </div>

    <div id="results-section" style="display:none; margin-top: 50px;">
        <!-- Banner del Ganador Rediseñado -->
        <div id="winner-banner" style="background: var(--card); border-radius: 28px; padding: 40px; text-align: center; margin-bottom: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 2px solid transparent;">
            <h2 id="winner-title" style="margin:0 0 15px 0; font-size: 2.5rem; font-weight: 800;">🏆 Mejor opción: ...</h2>
            <div id="winner-savings" style="font-size: 1.2rem; margin-bottom: 25px;"></div>
            
            <button id="btnSave" class="btn" onclick="guardarCesta()" style="background: var(--pri); color: white; padding: 15px 35px; border-radius: 50px; font-weight: 800; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s;">
                💾 Guardar en Historial
            </button>
            <div id="save-msg" style="margin-top: 20px; font-weight: 700; display: none;"></div>
        </div>

        <div id="comparison-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Mercadona -->
            <div class="glass-panel mercadona-accent" style="padding: 0; overflow: hidden;">
                <div style="background: rgba(22, 163, 74, 0.05); padding: 30px; text-align: center; border-bottom: 1px solid var(--border);">
                    <img src="/public/img/mercadona.png" style="height: 40px; margin-bottom: 15px;" alt="Mercadona">
                    <div id="total-mercadona"></div>
                </div>
                <div id="mercadona-items-list" style="padding: 10px 25px;"></div>
                <div id="missing-mercadona" style="padding: 15px 25px; font-size: 0.85em; color: var(--err); background: rgba(239, 68, 68, 0.03);"></div>
            </div>

            <!-- Dia -->
            <div class="glass-panel dia-accent" style="padding: 0; overflow: hidden;">
                <div style="background: rgba(220, 38, 38, 0.05); padding: 30px; text-align: center; border-bottom: 1px solid var(--border);">
                    <img src="/public/img/dia.png" style="height: 40px; margin-bottom: 15px;" alt="Dia">
                    <div id="total-dia"></div>
                </div>
                <div id="dia-items-list" style="padding: 10px 25px;"></div>
                <div id="missing-dia" style="padding: 15px 25px; font-size: 0.85em; color: var(--err); background: rgba(239, 68, 68, 0.03);"></div>
            </div>
        </div>
    </div>

    <!-- Script se mantiene similar pero con ajustes en el renderizado para la nueva estructura -->
    <script>
        let ultimosDatosIA = null;

        window.addEventListener('load', () => {
            const savedData = localStorage.getItem('ultimoComparador');
            const savedText = localStorage.getItem('ultimoTextoLista');
            const savedCity = localStorage.getItem('ultimaCiudad');

            if (savedCity) document.getElementById('ciudadSelect').value = savedCity;
            if (savedText) document.getElementById('listaInput').value = savedText;
            if (savedData) {
                try { mostrarResultados(JSON.parse(savedData)); } catch(e) {}
            }
        });

        function saveCity() {
            localStorage.setItem('ultimaCiudad', document.getElementById('ciudadSelect').value);
        }

        function addIngredient(name) {
            const el = document.getElementById('listaInput');
            el.value = el.value.trim() ? el.value + '\n' + name : name;
            localStorage.setItem('ultimoTextoLista', el.value);
            el.focus();
        }

        async function compararPrecios() {
            const input = document.getElementById('listaInput').value.trim();
            const city = document.getElementById('ciudadSelect').value;
            if (!input) { alert("Escribe algo en la lista."); return; }

            localStorage.setItem('ultimoTextoLista', input);
            localStorage.setItem('ultimaCiudad', city);
            
            const btn = document.getElementById('btnComparar');
            const loading = document.getElementById('loading');
            const results = document.getElementById('results-section');
            const errorDiv = document.getElementById('simple-error-msg');

            errorDiv.style.display = 'none';
            results.style.display = 'none';
            btn.disabled = true;
            loading.style.display = 'block';

            try {
                const response = await fetch('http://localhost:8001/comparar-lista-compra', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ingredientes: input.split('\n'), ciudad: city }) 
                });
                if (!response.ok) throw new Error("Error en el servidor");
                const data = await response.json();
                localStorage.setItem('ultimoComparador', JSON.stringify(data));
                mostrarResultados(data);
            } catch (err) {
                errorDiv.innerText = "⚠️ Error: " + err.message;
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                loading.style.display = 'none';
            }
        }

        function mostrarResultados(data) {
            ultimosDatosIA = data;
            document.getElementById('results-section').style.display = 'block';
            document.getElementById('results-section').scrollIntoView({ behavior: 'smooth' });

            const banner = document.getElementById('winner-banner');
            document.getElementById('winner-title').innerText = "🏆 Mejor opción: " + data.mejor_supermercado;
            document.getElementById('winner-savings').innerHTML = `Ahorras <strong>${data.ahorro_total}€</strong> en esta compra.`;
            
            if (data.mejor_supermercado === 'Mercadona') banner.style.borderColor = "#16a34a";
            else if (data.mejor_supermercado === 'Dia') banner.style.borderColor = "#dc2626";

            document.getElementById('total-mercadona').innerHTML = `
                <div style="font-size: 2rem; font-weight: 800;">${data.cesta_mercadona.total.toFixed(2)} €</div>
                <div style="font-size: 0.85rem; color: #16a34a; font-weight: 600;">Eficiencia: ${data.cesta_mercadona.total_normalizado.toFixed(2)}€/kg</div>
            `;
            document.getElementById('total-dia').innerHTML = `
                <div style="font-size: 2rem; font-weight: 800;">${data.cesta_dia.total.toFixed(2)} €</div>
                <div style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">Eficiencia: ${data.cesta_dia.total_normalizado.toFixed(2)}€/kg</div>
            `;

            const mList = document.getElementById('mercadona-items-list');
            const dList = document.getElementById('dia-items-list');
            mList.innerHTML = ""; dList.innerHTML = "";

            data.filas.forEach(f => {
                mList.innerHTML += crearHtmlCelda(f.mercadona, 'Mercadona');
                dList.innerHTML += crearHtmlCelda(f.dia, 'Dia');
            });

            document.getElementById('missing-mercadona').innerText = data.cesta_mercadona.productos_no_encontrados.length ? "❌ No hay: " + data.cesta_mercadona.productos_no_encontrados.join(", ") : "";
            document.getElementById('missing-dia').innerText = data.cesta_dia.productos_no_encontrados.length ? "❌ No hay: " + data.cesta_dia.productos_no_encontrados.join(", ") : "";
        }

        function crearHtmlCelda(p, tienda) {
            if (!p) return `<div style="padding: 15px 0; border-top: 1px solid var(--border); color: var(--muted); font-style: italic; font-size: 0.9rem;">No disponible</div>`;
            const imgUrl = p.imagen || 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';
            return `
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px 0; border-top: 1px solid var(--border);">
                    <img src="${imgUrl}" style="width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: white; border: 1px solid var(--border);">
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.9rem; line-height: 1.2;">${p.nombre}</div>
                        <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.85rem;">
                            <span style="font-weight: 800; color: var(--text);">${p.precio.toFixed(2)}€</span>
                            <span style="color: var(--muted);">${p.precio_ref.toFixed(2)}€/${p.unidad}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        async function guardarCesta() {
            const btn = document.getElementById('btnSave');
            const msg = document.getElementById('save-msg');
            btn.innerText = "Guardando...";
            try {
                const r = await fetch('/save_basket.php', { method: 'POST', body: JSON.stringify(ultimosDatosIA) });
                if (r.ok) { btn.innerText = "✅ Guardado"; msg.innerText = "¡Listo! Ya está en tu historial."; msg.style.display="block"; }
            } catch(e) { btn.innerText = "❌ Error"; }
        }
    </script>
<?php endif; ?>