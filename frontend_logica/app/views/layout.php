<!DOCTYPE html>
<html lang="es">
<head>
  <script>
    // Pre-vuelo para evitar flash de color incorrecto
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? $appName) ?> — <?= htmlspecialchars($appName) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
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
        
        <button class="theme-toggle" id="themeToggle" title="Cambiar modo">
            <span id="themeIcon">🌙</span>
        </button>
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

  <script>
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const html = document.documentElement;

    // Inicializar icono basándose en el atributo actual
    function updateIcon() {
        const currentTheme = html.getAttribute('data-theme');
        themeIcon.innerText = currentTheme === 'light' ? '☀️' : '🌙';
    }
    
    updateIcon();

    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateIcon();
    });
  </script>
</body>
</html>
