<?php
$title = $title ?? 'Comparador Inteligente';
$view = 'home';
?>

<div style="background: #27ae60; color: white; padding: 15px; text-align: center; margin-bottom: 20px; font-weight: bold; border-radius: 5px;">
    ✅ MODO MVP: Conectado al Cerebro IA (Puerto 8001)
</div>

<h2>Comparador de Cestas Inteligente</h2>

<?php if (!isset($_SESSION['username'])): ?>
  <div style="text-align:center; padding: 40px;">
      <p>Necesitas identificarte para usar la IA.</p>
      <a class="btn LogInBtn" href="/?r=login">Iniciar sesión</a>
  </div>

<?php else: ?>
  <p>Pega tu lista de la compra y la IA encontrará los mejores precios en Mercadona y Dia.</p>

  <div class="card" style="padding: 20px; background: white; border: 1px solid #ddd; margin-bottom: 20px;">
      <div class="row">
        <div class="full-width">
          <label for="listaInput"><strong>Tu Lista de la Compra:</strong></label>
          <textarea id="listaInput" rows="6" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" 
            placeholder="Ejemplo:&#10;1 litro de leche entera&#10;Pechuga de pollo&#10;Arroz redondo&#10;Aceite de oliva"></textarea>
        </div>
        <div id="simple-error-msg" style="display:none; color: white; background: #e74c3c; padding: 10px; margin-top: 20px; border-radius: 4px;"></div>
        <div class="full-width" style="margin-top:15px;">
          <button class="btn" id="btnComparar" onclick="compararPrecios()" style="width:100%; font-size:1.1em;">
            🔍 Comparar Precios en Mercadona y Dia
          </button>
        </div>
      </div>
  </div>

  <div id="loading" style="display:none; text-align:center; margin-top:20px;">
      <p>🧠 La IA está pensando... comparando precios...</p>
  </div>

  <!-- Error div removed (moved inside card) -->

  <div id="results-section" style="display:none; margin-top: 30px; max-width: 1000px; margin-left: auto; margin-right: auto;">
      
      <div id="winner-banner" class="winner-box" style="background: #d4edda; color: #155724; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 25px; border: 1px solid #c3e6cb; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
          <h2 id="winner-title" style="margin:0; font-size: 1.8em;">🏆 Mejor opción: ...</h2>
          <p id="winner-savings" style="margin:5px 0 15px 0; font-size: 1.2em;">Ahorro estimado: 0.00€</p>
          
          <button id="btnSave" onclick="guardarCesta()" style="background: white; color: #333; border: 1px solid #ccc; padding: 10px 20px; border-radius: 30px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s;">
            💾 Guardar en mi Historial
          </button>
          <div id="save-msg" style="margin-top: 10px; font-size: 0.9em; display: none;"></div>
      </div>

      <!-- NUEVA ESTRUCTURA DE GRILLA PARA ALINEACIÓN PERFECTA -->
      <div id="comparison-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
          <!-- Columna Mercadona (Header) -->
          <div class="mercadona-header" style="background: #f4fbf7; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #007a3e;">
              <h3 style="color: #007a3e; margin: 0; border-bottom: 2px solid #007a3e; padding-bottom: 8px;">Mercadona</h3>
              <div id="total-mercadona" style="margin-top: 10px;"></div>
          </div>
          <!-- Columna Dia (Header) -->
          <div class="dia-header" style="background: #fff5f6; padding: 20px; border-radius: 12px 12px 0 0; border: 1px solid #ddd; border-bottom: none; border-top: 5px solid #d50032;">
              <h3 style="color: #d50032; margin: 0; border-bottom: 2px solid #d50032; padding-bottom: 8px;">Dia</h3>
              <div id="total-dia" style="margin-top: 10px;"></div>
          </div>

          <!-- Filas de productos inyectadas por JS -->
          <div id="list-container" style="grid-column: 1 / span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px;">
          </div>

          <!-- Footers (No encontrados) -->
          <div id="missing-mercadona" style="background: #f4fbf7; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
          <div id="missing-dia" style="background: #fff5f6; padding: 15px; border-radius: 0 0 12px 12px; border: 1px solid #ddd; border-top: none; color: #c0392b; font-size: 0.9em;"></div>
      </div>
  </div>

  <script>
    // Variable global para recordar los datos de la IA
    let ultimosDatosIA = null;

    // PERSISTENCIA: Cargar al iniciar
    window.addEventListener('load', () => {
        const savedData = localStorage.getItem('ultimoComparador');
        const savedText = localStorage.getItem('ultimoTextoLista');
        if (savedText) {
            const el = document.getElementById('listaInput');
            if (el) el.value = savedText;
        }
        if (savedData) {
            try {
                mostrarResultados(JSON.parse(savedData));
            } catch(e) {}
        }
    });

    async function compararPrecios() {
        const inputEl = document.getElementById('listaInput');
        const input = inputEl ? inputEl.value.trim() : "";
        localStorage.setItem('ultimoTextoLista', input);
        
        const btn = document.getElementById('btnComparar');
        const loading = document.getElementById('loading');
        const results = document.getElementById('results-section');
        const errorDiv = document.getElementById('simple-error-msg');

        errorDiv.style.display = 'none';
        results.style.display = 'none';
        
        const ingredientes = input.split('\n').map(line => line.trim()).filter(line => line.length > 0);

        if (ingredientes.length === 0) {
            alert("Por favor, escribe al menos un producto.");
            return;
        }

        btn.disabled = true;
        btn.innerText = "Analizando...";
        loading.style.display = 'block';

        try {
            const response = await fetch('http://localhost:8001/comparar-lista-compra', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    ingredientes: ingredientes
                }) 
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.detail || "Error conectando con el servidor (Puerto 8001)");
            }

            const data = await response.json();
            
            // Guardar para persistencia
            localStorage.setItem('ultimoComparador', JSON.stringify(data));

            // Mostrar
            mostrarResultados(data);

        } catch (err) {
            console.error(err);
            errorDiv.innerText = "⚠️ Error: " + err.message + ". Comprueba que el backend (Docker) está encendido.";
            errorDiv.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerText = "🔍 Comparar Precios en Mercadona y Dia";
            loading.style.display = 'none';
        }
    }

    function mostrarResultados(data, saveToLocalStorage = true) {
        ultimosDatosIA = data;

        const results = document.getElementById('results-section');
        results.style.display = 'block';
        results.scrollIntoView({ behavior: 'smooth' });

        const btnSave = document.getElementById('btnSave');
        btnSave.disabled = false;
        btnSave.innerText = "💾 Guardar en mi Historial";

        const winnerTitle = document.getElementById('winner-title');
        const winnerBox = document.getElementById('winner-banner');

        // Banner Winner
        winnerTitle.innerHTML = "🏆 Mejor opción: " + data.mejor_supermercado;
        let warningHtml = "";
        if (!data.comparativa_completa) {
            warningHtml = `<div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 0.9em; border: 1px solid #ffeeba;">⚠️ <strong>Aviso:</strong> La comparativa es parcial.</div>`;
        }

        let extraNoteHtml = "";
        if (data.mensaje_ahorro) {
            extraNoteHtml = `<div style="background: #e1f5fe; color: #01579b; padding: 10px; border-radius: 5px; margin-top: 10px; font-size: 0.85em; border: 1px solid #b3e5fc; line-height: 1.4;">💡 <strong>Info:</strong> ${data.mensaje_ahorro}</div>`;
        }

        document.getElementById('winner-savings').innerHTML = `
            ${warningHtml}
            <strong>Ahorras ${data.ahorro_total}€</strong> en tu lista hoy.
            ${extraNoteHtml}
        `;

        if (data.mejor_supermercado === 'Mercadona') {
            winnerBox.style.background = "#d4edda"; winnerBox.style.color = "#155724";
        } else if (data.mejor_supermercado === 'Dia') {
            winnerBox.style.background = "#fadbd8"; winnerBox.style.color = "#721c24";
        } else {
            winnerBox.style.background = "#fff3cd"; winnerBox.style.color = "#856404";
        }

        // Headers Totales
        document.getElementById('total-mercadona').innerHTML = `
            <div style="font-size: 1.5em; color: #111; font-weight: bold;">${data.cesta_mercadona.total.toFixed(2)} €</div>
            <div style="font-size: 0.75em; color: #0984e3; font-weight: bold;">🚀 Eficiencia: ${data.cesta_mercadona.total_normalizado.toFixed(2)}€/kg-L</div>
        `;
        document.getElementById('total-dia').innerHTML = `
            <div style="font-size: 1.5em; color: #111; font-weight: bold;">${data.cesta_dia.total.toFixed(2)} €</div>
            <div style="font-size: 0.75em; color: #0984e3; font-weight: bold;">🚀 Eficiencia: ${data.cesta_dia.total_normalizado.toFixed(2)}€/kg-L</div>
        `;

        // Limpieza y Renderizado de Filas
        const container = document.getElementById('list-container');
        container.innerHTML = "";

        if (data.filas && data.filas.length > 0) {
            data.filas.forEach(fila => {
                // Celda Mercadona
                const divM = document.createElement('div');
                divM.style.background = "#f4fbf7";
                divM.style.padding = "0 20px 12px 20px";
                divM.style.borderLeft = "1px solid #ddd";
                divM.style.borderRight = "1px solid #ddd";
                divM.innerHTML = crearHtmlCelda(fila.mercadona, 'Mercadona');
                
                // Celda Dia
                const divD = document.createElement('div');
                divD.style.background = "#fff5f6";
                divD.style.padding = "0 20px 12px 20px";
                divD.style.borderLeft = "1px solid #ddd";
                divD.style.borderRight = "1px solid #ddd";
                divD.innerHTML = crearHtmlCelda(fila.dia, 'Dia');

                container.appendChild(divM);
                container.appendChild(divD);
            });
        }

        // Missing Items Footer
        const mMiss = data.cesta_mercadona.productos_no_encontrados;
        const dMiss = data.cesta_dia.productos_no_encontrados;
        document.getElementById('missing-mercadona').innerHTML = mMiss.length ? `❌ No disponible: ${mMiss.join(", ")}` : "";
        document.getElementById('missing-dia').innerHTML = dMiss.length ? `❌ No disponible: ${dMiss.join(", ")}` : "";
    }

    // --- NUEVA FUNCIÓN PARA GUARDAR EN MYSQL ---
    async function guardarCesta() {
        if (!ultimosDatosIA) return;

        const btn = document.getElementById('btnSave');
        const msg = document.getElementById('save-msg');
        
        btn.disabled = true;
        btn.innerText = "Guardando...";

        try {
            // Llamamos a nuestro script PHP intermedio
            const response = await fetch('/save_basket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(ultimosDatosIA)
            });

            const res = await response.json();

            if (response.ok) {
                btn.innerText = "✅ ¡Guardado!";
                msg.style.color = "green";
                msg.innerText = "Cesta guardada correctamente en tu Historial.";
                msg.style.display = 'block';
            } else {
                throw new Error(res.error || "Error desconocido");
            }
        } catch (e) {
            btn.innerText = "❌ Error";
            btn.disabled = false;
            msg.style.color = "red";
            msg.innerText = "Error: " + e.message;
            msg.style.display = 'block';
        }
    }

    function crearHtmlCelda(p, tienda) {
        if (!p) {
            return `<div style="display: flex; align-items: center; gap: 12px; padding: 15px 0; color: #999; font-style: italic; border-top: 1px solid #eee;">
                <div style="width: 50px; height: 50px; background: rgba(0,0,0,0.03); border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center;">❓</div>
                <div style="font-size: 0.9em;">No disponible en ${tienda}</div>
            </div>`;
        }

        const esCaro = p.es_formato_grande;
        const imgUrl = (p.imagen && p.imagen !== '') ? p.imagen : 'https://cdn-icons-png.flaticon.com/512/1147/1147931.png';

        return `<div style="display: flex; align-items: center; gap: 12px; padding: 15px 0; border-top: 1px solid #eee;">
            <img src="${imgUrl}" style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: white;">
            <div style="flex: 1;">
                <div style="font-weight: bold; font-size: 0.95em; color: #000; line-height: 1.2;">${p.nombre}</div>
                <div style="color: #555; font-size: 0.85em; display: flex; justify-content: space-between; margin-top: 4px;">
                    <span style="${esCaro ? 'color: #d35400; font-weight: bold;' : ''}">${p.precio.toFixed(2)}€</span>
                    <span style="color: #0984e3; font-weight: bold;">${p.precio_ref > 0 ? p.precio_ref.toFixed(2) + '€/' + p.unidad : ''}</span>
                </div>
            </div>
        </div>`;
    }
  </script>

<?php endif; ?>