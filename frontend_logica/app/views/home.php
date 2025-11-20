<?php
$title = $title ?? 'Inicio';
$view = 'home';
?>
<h2>Bienvenido</h2>

<?php if (!isset($_SESSION['username'])): ?>
  <p class="muted">
    Para personalizar los mejores precios según tus gustos e intolerancias,
    primero debes iniciar sesión.
  </p>
  <p class="LogInP"><a class="btn LogInBtn" href="/?r=login">Iniciar sesión</a></p>
<?php else: ?>


<p>Cuéntame tus gustos e intolerancias y buscaré los mejores precios adaptados a ti.</p>

<form id="prefsForm" class="form">
  <div class="row">

    <!-- GUSTOS / OBJETIVOS -->
    <div class="full-width">
      <label for="likes">Gustos / objetivos</label>
      <textarea
        name="likes"
        id="likes"
        rows="3"
        placeholder="Ej: productos integrales, alto en proteína, frutas, sin azúcar añadido..."></textarea>
    </div>

    <!-- INTOLERANCIAS DINÁMICAS -->
    <div class="full-width">
      <label>Intolerancias / alergias</label>

      <div id="intolerancesWrapper" class="tag-input-group">
        <!-- Primer input visible por defecto -->
        <div class="intolerance-row">
          <input
            type="text"
            name="intolerance[]"
            class="intolerance-input"
            list="intolerancesList"
            placeholder="Ej: sin lactosa, sin gluten, frutos secos..."
          />
        </div>
      </div>

      <!-- Sugerencias (datalist) -->
      <datalist id="intolerancesList">
        <option value="Sin gluten"></option>
        <option value="Sin lactosa"></option>
        <option value="Frutos secos"></option>
        <option value="Huevo"></option>
        <option value="Mariscos"></option>
        <option value="Soja"></option>
        <option value="Sésamo"></option>
        <option value="Sulfitos"></option>
        <option value="Mostaza"></option>
        <option value="Pescado"></option>
        <option value="Cacahuetes"></option>
        <option value="Celíaco"></option>
        <option value="Vegano"></option>
      </datalist>

      <!-- Botón para añadir más inputs -->
      <button type="button" id="addIntolerance" class="btn-ghost">
        <span class="plus">+</span>
        Añadir otra intolerancia
      </button>

      <!-- Campo oculto que se envía realmente al backend -->
      <textarea name="intolerances" id="intolerances" hidden></textarea>
    </div>

    <!-- BOTÓN ENVIAR -->
    <div class="full-width LogInBtn">
      <button class="btn" type="submit" id="prefsSubmit">
        Guardar preferencias y comprobar precios
      </button>
    </div>
  </div>
</form>

<div id="results" class="row"></div>



<?php endif; ?>

<div id="results" class="row"></div>

<script>
const prefsForm   = document.getElementById('prefsForm');
const prefsSubmit = document.getElementById('prefsSubmit');
const results     = document.getElementById('results');

// 🔹 NUEVOS ELEMENTOS PARA INTOLERANCIAS DINÁMICAS
const intolerancesWrap   = document.getElementById('intolerancesWrapper'); // contenedor de inputs
const addIntoleranceBtn  = document.getElementById('addIntolerance');      // botón "+"
const hiddenIntolerances = document.getElementById('intolerances');        // textarea hidden

// Añadir un nuevo input de intolerancia al pulsar "+"
if (addIntoleranceBtn && intolerancesWrap) {
  addIntoleranceBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'intolerance-row';

    row.innerHTML = `
      <input
        type="text"
        name="intolerance[]"
        class="intolerance-input"
        list="intolerancesList"
        placeholder="Ej: sin lactosa, sin gluten, frutos secos..."
      />
    `;

    intolerancesWrap.appendChild(row);
  });
}

function renderItems(items){
  if(!items || !items.length){
    results.innerHTML = '<p class="muted">No hay datos.</p>';
    return;
  }

  results.innerHTML = items.map(item => `
    <div class="col">
      <div class="card">
        <h3>${item.name}</h3>
        <p class="muted">${item.category_name ?? ''}</p>
        <p><strong>${item.price.toFixed(2)} €</strong> — ${item.store_name}</p>
      </div>
    </div>
  `).join('');
}

// Llamada a tu API de precios (la que ya tenías)
async function fetchBestPrices(){
  results.innerHTML = '';
  try {
    const res = await fetch('http://127.0.0.1:8000/mercadona/category/112/best_prices?lang=es&wh=vlc1');
    if(!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    renderItems(data.items);
  } catch (e){
    results.innerHTML = `<div class="flash err">Error: ${e.message}</div>`;
  }
}

<?php if (isset($_SESSION['username'])): ?>
// Solo si está logueado: primero guardamos preferencias, luego buscamos precios
if (prefsForm) {
  prefsForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    results.innerHTML = '';

    // 🔹 ANTES DE ENVIAR: construir el campo "intolerances" desde todos los inputs visibles
    if (intolerancesWrap && hiddenIntolerances) {
      const inputs = Array.from(
        intolerancesWrap.querySelectorAll('.intolerance-input')
      );

      const values = inputs
        .map(i => i.value.trim())
        .filter(v => v.length > 0);

      hiddenIntolerances.value = values.join(', ');
    }

    prefsSubmit.disabled = true;
    prefsSubmit.textContent = 'Guardando y buscando...';

    try {
      const formData = new FormData(prefsForm);
      const res = await fetch('/?r=preferences.save', {
        method: 'POST',
        body: formData
      });

      const data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data.message || 'No se pudieron guardar las preferencias');
      }

      // Una vez guardadas las preferencias, llamamos a la API de precios
      await fetchBestPrices();

    } catch (err) {
      results.innerHTML = `<div class="flash err">Error: ${err.message}</div>`;
    } finally {
      prefsSubmit.disabled = false;
      prefsSubmit.textContent = 'Guardar preferencias y comprobar precios';
    }
  });
}
<?php endif; ?>
</script>

