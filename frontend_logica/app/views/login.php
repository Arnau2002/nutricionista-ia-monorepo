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
        <p style="text-align: center; margin-top: 10px;">
            <a href="/?r=forgot_password" style="color: #666; font-size: 0.9em; text-decoration: underline;">¿Has olvidado tu contraseña?</a>
        </p>
        <div>
            <button class="btn" type="submit">Entrar</button>
        </div>
    </div>
</form>
<p class="muted">¿No tienes cuenta? <a href="/?r=register">Regístrate</a>.</p>