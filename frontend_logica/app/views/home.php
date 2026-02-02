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

  <div class="card" style="padding: 20px; background: white;">
      <div class="row">
        <div class="full-width">
          <label for="listaInput"><strong>Tu Lista de la Compra:</strong></label>
          <textarea id="listaInput" rows="6" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" 
            placeholder="Ejemplo:&#10;1 litro de leche entera&#10;Pechuga de pollo&#10;Arroz redondo&#10;Aceite de oliva"></textarea>
        </div>
        <div class="full-width" style="margin-top:15px;">
          <button class="btn" id="btnComparar" onclick="compararPrecios()" style="width:100%; font-size:1.1em;">
            🔍 Analizar Ofertas con IA
          </button>
        </div>
      </div>
  </div>

  <div id="loading" style="display:none; text-align:center; margin-top:20px;">
      <p>🧠 La IA está pensando... comparando precios...</p>
  </div>

  <div id="error-msg" style="display:none; color: white; background: #e74c3c; padding: 10px; margin-top: 20px; border-radius: 4px;"></div>

  <div id="results-section" style="display:none; margin-top: 30px;">
      
      <div id="winner-banner" class="winner-box" style="background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; text-align: center; margin-bottom: 20px; border: 1px solid #c3e6cb;">
          <h2 id="winner-title" style="margin:0;">🏆 Mejor opción: ...</h2>
          <p id="winner-savings" style="margin:5px 0 0 0; font-size: 1.2em;">Ahorro estimado: 0.00€</p>
      </div>

      <div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
          
          <div class="mercadona-card" style="flex: 1; min-width: 300px; background: #f4fbf7; padding: 15px; border-radius: 8px; border: 1px solid #ddd; border-top: 4px solid #007a3e;">
              <h3 style="color: #007a3e; border-bottom: 2px solid #007a3e; padding-bottom: 5px;">Mercadona</h3>
              <h4 id="total-mercadona" style="font-size: 1.4em; margin: 10px 0;">0.00 €</h4>
              <div id="list-mercadona"></div>
              <div id="missing-mercadona" style="color: #c0392b; font-size: 0.9em; margin-top: 10px;"></div>
          </div>

          <div class="dia-card" style="flex: 1; min-width: 300px; background: #fff5f6; padding: 15px; border-radius: 8px; border: 1px solid #ddd; border-top: 4px solid #d50032;">
              <h3 style="color: #d50032; border-bottom: 2px solid #d50032; padding-bottom: 5px;">Dia</h3>
              <h4 id="total-dia" style="font-size: 1.4em; margin: 10px 0;">0.00 €</h4>
              <div id="list-dia"></div>
              <div id="missing-dia" style="color: #c0392b; font-size: 0.9em; margin-top: 10px;"></div>
          </div>

      </div>
  </div>

  <script>
    async function compararPrecios() {
        const input = document.getElementById('listaInput').value;
        const btn = document.getElementById('btnComparar');
        const loading = document.getElementById('loading');
        const results = document.getElementById('results-section');
        const errorDiv = document.getElementById('error-msg');

        // 1. Limpiar estado visual
        errorDiv.style.display = 'none';
        results.style.display = 'none';
        
        // 2. Procesar entrada (separar por líneas y limpiar espacios)
        const ingredientes = input.split('\n').map(line => line.trim()).filter(line => line.length > 0);

        if (ingredientes.length === 0) {
            alert("Por favor, escribe al menos un producto.");
            return;
        }

        // 3. Activar animación de carga
        btn.disabled = true;
        btn.innerText = "Analizando...";
        loading.style.display = 'block';

        try {
            // 4. PETICIÓN AL BACKEND PYTHON (Puerto 8001)
            const response = await fetch('http://localhost:8001/comparar-lista-compra', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                // IMPORTANTE: La clave debe ser 'ingredientes' (español)
                body: JSON.stringify({ ingredientes: ingredientes }) 
            });

            if (!response.ok) {
                // Si el servidor devuelve error (422, 500, etc.)
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.detail || "Error conectando con el servidor (Puerto 8001)");
            }

            const data = await response.json();
            
            // 5. Mostrar resultados si todo fue bien
            mostrarResultados(data);

        } catch (err) {
            console.error(err);
            errorDiv.innerText = "⚠️ Error: " + err.message + ". Comprueba que el backend (Docker) está encendido.";
            errorDiv.style.display = 'block';
        } finally {
            // 6. Restaurar botón
            btn.disabled = false;
            btn.innerText = "🔍 Analizar Ofertas con IA";
            loading.style.display = 'none';
        }
    }

    function mostrarResultados(data) {
        const results = document.getElementById('results-section');
        results.style.display = 'block';

        // Scroll suave hacia los resultados
        results.scrollIntoView({ behavior: 'smooth' });

        // A. Banner del Ganador
        const winnerTitle = document.getElementById('winner-title');
        const winnerBox = document.getElementById('winner-banner');
        
        winnerTitle.innerText = "🏆 Mejor opción: " + data.mejor_supermercado;
        document.getElementById('winner-savings').innerText = "Ahorro estimado: " + data.ahorro_total + " €";

        // Cambio de color según quién gane
        if (data.mejor_supermercado === 'Mercadona') {
            winnerBox.style.background = "#d4edda"; // Verde
            winnerBox.style.color = "#155724";
        } else if (data.mejor_supermercado === 'Dia') {
            winnerBox.style.background = "#fadbd8"; // Rojo suave
            winnerBox.style.color = "#721c24";
        } else {
            winnerBox.style.background = "#fff3cd"; // Amarillo (Empate)
            winnerBox.style.color = "#856404";
        }

        // B. Renderizar Columnas
        renderCesta(data.cesta_mercadona, 'total-mercadona', 'list-mercadona', 'missing-mercadona');
        renderCesta(data.cesta_dia, 'total-dia', 'list-dia', 'missing-dia');
    }

    function renderCesta(cesta, idTotal, idList, idMissing) {
        // Total
        document.getElementById(idTotal).innerText = cesta.total + " €";
        
        // Lista de productos encontrados
        const listaHtml = cesta.productos_encontrados.map(prod => `
            <div style="border-bottom:1px solid #eee; padding: 8px 0;">
                <div style="font-weight:bold; font-size:0.95em;">${prod.nombre}</div>
                <div style="color:#555; font-size:0.85em; display:flex; justify-content:space-between;">
                    <span>${prod.precio}€ / ${prod.unidad}</span>
                    ${prod.final_score < 0.6 ? '<span title="Coincidencia baja" style="cursor:help;">⚠️</span>' : ''}
                </div>
            </div>
        `).join('');
        
        const container = document.getElementById(idList);
        container.innerHTML = listaHtml || "<p style='color:#777; font-style:italic;'>Sin productos</p>";

        // Productos NO encontrados
        if (cesta.productos_no_encontrados.length > 0) {
            document.getElementById(idMissing).innerHTML = 
                "<div style='margin-top:10px; padding-top:10px; border-top:2px dashed #ecc;'>" +
                "<strong>❌ No disponible:</strong> <span style='color:#c0392b;'>" + 
                cesta.productos_no_encontrados.join(", ") + 
                "</span></div>";
        } else {
            document.getElementById(idMissing).innerHTML = "";
        }
    }
  </script>

<?php endif; ?>