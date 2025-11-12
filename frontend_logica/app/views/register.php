<?php $title = $title ?? 'Registro'; $view = 'register'; ?>
<h2>Crear cuenta</h2>
<form method="post" action="/?r=auth.register">
  <div class="row">
    <div>
      <label for="name">Nombre</label>
      <input type="text" name="name" id="name" required />
    </div>
    <div>
      <label for="email">Email</label>
      <input type="email" name="email" id="email" required />
    </div>
    <div>
      <label for="password">Contraseña</label>
      <input type="password" name="password" id="password" minlength="6" required />
    </div>
    <div>
      <label for="password2">Repetir contraseña</label>
      <input type="password" name="password2" id="password2" minlength="6" required />
    </div>
    <div>
      <button class="btn" type="submit">Registrarme</button>
    </div>
  </div>
</form>
<p class="muted">¿Ya tienes cuenta? <a href="/?r=login">Inicia sesión</a>.</p>
