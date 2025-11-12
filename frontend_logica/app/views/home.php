<?php $title = $title ?? 'Inicio'; $view = 'home'; ?>
<h2>Bienvenido 👋</h2>
<p>Pulsa el botón para obtener los productos con mejor precio por subcategoría.</p>
<p>
  <button class="btn" id="checkPrices">Comprobar precios</button>
</p>

<div id="results" class="row"></div>
<script>
const btn = document.getElementById('checkPrices');
const results = document.getElementById('results');

function renderItems(items){
  if(!items || !items.length){
    results.innerHTML = '<p class="muted">No hay datos.</p>';
    return;
  }
  const html = items.map(i => `
    <div class="card" style="padding:12px">
      <strong>${i.subcategory_name ?? ('Subcat #' + i.subcategory_id)}</strong><br/>
      <span>${i.name}</span><br/>
      <span><b>${i.price}</b> ${i.currency ?? ''}</span>
    </div>
  `).join('');
  results.innerHTML = html;
}

btn.addEventListener('click', async () => {
  btn.disabled = true; btn.textContent = 'Consultando...';
  results.innerHTML = '';
  try {
    const res = await fetch('http://127.0.0.1:8000/mercadona/category/112/best_prices?lang=es&wh=vlc1');
    console.log(res);
    if(!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    renderItems(data.items);
  } catch (e){
    results.innerHTML = `<div class="flash err">Error: ${e.message}</div>`;
  } finally {
    btn.disabled = false; btn.textContent = 'Comprobar precios';
  }
});
</script>