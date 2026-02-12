<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? $appName) ?> — <?= htmlspecialchars($appName) ?></title>
  <link rel="stylesheet" href="/css/style.css">
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
          <a href="/?r=dashboard">Dashboard</a>
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
</body>
</html>
