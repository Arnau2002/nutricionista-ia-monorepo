<?php $title = $title ?? 'Iniciar sesión';
$view = 'login'; ?>
<h2>Iniciar sesión</h2>
<form class="form" method="post" action="/?r=auth.login">
    <div class="row">
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required />
        </div>
        <div>
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required />
        </div>
        <div>
            <button class="btn" type="submit">Entrar</button>
        </div>
    </div>
</form>
<p class="muted">¿No tienes cuenta? <a href="/?r=register">Regístrate</a>.</p>