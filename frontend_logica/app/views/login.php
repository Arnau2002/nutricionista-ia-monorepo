<?php $title = $title ?? 'Iniciar sesión';
$view = 'login'; ?>

<style>
/* --- ANIMACIÓN DE BLOBS PARA AUTH --- */
.auth-bg {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: -1;
    overflow: hidden;
    background: var(--bg);
}

.auth-blob {
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(60px);
    animation: float 20s infinite alternate ease-in-out;
}

.auth-blob-1 { top: -10%; left: -10%; background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%); }
.auth-blob-2 { bottom: -10%; right: -10%; background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 70%); animation-delay: -10s; }

@keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(100px, 50px) rotate(90deg); }
}

/* --- REFINAMIENTO DE CARD --- */
.login-header {
    text-align: center;
    margin-bottom: 40px;
}

.login-header h2 {
    font-size: 2.2rem;
    margin-bottom: 10px;
    letter-spacing: -1px;
}

.login-header p {
    color: var(--muted);
    font-weight: 500;
}

.auth-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn-login {
    height: 55px;
    font-size: 1.1rem !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4) !important;
}

.btn-login:hover {
    box-shadow: 0 15px 40px rgba(99, 102, 241, 0.6) !important;
    transform: translateY(-3px);
}

[data-theme="light"] .forgot-link { color: #64748b !important; }
[data-theme="light"] .forgot-link:hover { color: #4f46e5 !important; }

/* Ajuste para que la card no flote tanto */
.card.login {
    border-radius: 30px;
    padding: 50px;
    box-shadow: 0 30px 100px rgba(0,0,0,0.2);
}
</style>

<div class="auth-container">
    <div class="auth-bg">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>
    </div>

    <div class="login-header">
        <div style="background: white; border-radius: 20px; padding: 10px; display: inline-block; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);">
            <img src="/public/img/logo.png" alt="Logo" style="width: 80px; height: auto; border-radius: 12px;">
        </div>
        <h2>¡Hola de nuevo!</h2>
        <p>Introduce tus datos para acceder a tu planificador.</p>
    </div>

    <form class="form" method="post" action="/?r=auth.login">
        <div class="row">
            <div>
                <label for="email">Tu Email</label>
                <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required />
            </div>
            <div>
                <label for="password">Tu Contraseña</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required />
            </div>
            <p style="text-align: right; margin-top: -10px;">
                <a href="/?r=forgot_password" class="forgot-link" style="color: var(--muted); font-size: 0.85em; font-weight: 600; text-decoration: none;">¿Olvidaste tu contraseña?</a>
            </p>
            <div>
                <button class="btn btn-login" type="submit">Entrar al Sistema</button>
            </div>
        </div>
    </form>

    <div class="auth-footer">
        <p class="muted" style="margin:0;">¿Aún no tienes cuenta? <a href="/?r=register" style="color: #6366f1; font-weight: 800;">Regístrate gratis</a></p>
    </div>
</div>