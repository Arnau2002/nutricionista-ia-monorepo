<?php
$title = $title ?? 'Inicio';
$view = 'home';
?>

<div style="background: #e74c3c; color: white; padding: 20px; text-align: center; margin-bottom: 20px; font-weight: bold;">
    MODO PRUEBA ACTIVADO: Si ves esto, has editado el archivo correcto.
</div>

<h2>Bienvenido</h2>

<?php if (!isset($_SESSION['username'])): ?>
  <p class="LogInP"><a class="btn LogInBtn" href="/?r=login">Iniciar sesión</a></p>
<?php else: ?>

<p>Cuéntame tus gustos e intolerancias.</p>

<form id="prefsForm" class="form">
  <div class="row">
    <div class="full-width">
      <label>Gustos</label>
      <textarea name="likes" id="likes" rows="2" placeholder="Ej: chocolate..."></textarea>
    </div>
    <div class="full-width LogInBtn">
      <button class="btn" type="submit" id="prefsSubmit">Buscar Precios (Directo a Python)</button>
    </div>
  </div>
</form>

<div id="results-section" style="margin-top: 40px;">
    <h3>Resultados:</h3>
    <div id="results" class="row">
        <div class="card" style="background: white; padding: 20px; border-left: 5px solid green;">
            <h3>Leche de Avena (Ejemplo)</h3>
            <p>1.20 € - Mercadona</p>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
const prefsForm = document.getElementById('prefsForm');
const results = document.getElementById('results');

// Función para pintar tarjetas
function renderItems(items){
  if(!items || !items.length){
    results.innerHTML = '<p>No hay resultados.</p>';
    return;
  }
  results.innerHTML = items.map(item => `
    <div class="card" style="background:white; padding:15px; margin-bottom:10px; border:1px solid #ccc;">
        <h3>${item.name}</h3>
        <p><strong>${item.price} €</strong> en ${item.store_name}</p>
    </div>
  `).join('');
}

// Conexión DIRECTA a Python (Puerto 8001)
async function fetchBestPrices(){
  try {
    const res = await fetch('http://localhost:8001/mercadona/category/112/best_prices?lang=es&wh=vlc1');
    if(!res.ok) throw new Error('Error Python: ' + res.status);
    const data = await res.json();
    renderItems(data.items);
  } catch (e){
    results.innerHTML = `<div style="color:red; background: #fee; padding: 10px;">Error de Conexión: ${e.message}</div>`;
  }
}

<?php if (isset($_SESSION['username'])): ?>
if (prefsForm) {
  prefsForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    results.innerHTML = 'Cargando...';
    // Saltamos el guardado en PHP para evitar el error '<'
    await fetchBestPrices();
  });
}
<?php endif; ?>
</script>