<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? $appName) ?> — <?= htmlspecialchars($appName) ?></title>
  <link rel="stylesheet" href="/css/style.css">
  <!-- Leaflet.js para el mapa de supermercados -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a class="brand" href="/?r=home"><?= htmlspecialchars($appName) ?></a>
      <nav class="main-nav">
        <a href="/?r=home">Inicio</a>
        
        <?php if (!isset($_SESSION['username'])): ?>
          <a href="/?r=login">Login</a>
          <a href="/?r=register">Registro</a>
        <?php else: ?>
          <a href="/?r=planificador">👨‍🍳 Planificador</a> <a href="/?r=dashboard">Dashboard</a>
          <a href="/?r=logout">Logout</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <?php if (!empty($flash)): ?>
      <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="card <?= htmlspecialchars($view ?? '') ?>">
      <?php include __DIR__ . '/' . $view . '.php'; ?>
    </div>
  </main>

  <footer>
    <p class="muted">Nutricionista.IA • <?= date('Y') ?></p>
  </footer>
  <!-- Leaflet scripts al final del body para garantizar disponibilidad -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
</body>
</html>
