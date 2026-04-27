<?php $title = $title ?? 'Registro'; $view = 'register'; ?>

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
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(80px);
    animation: float 25s infinite alternate ease-in-out;
}

.auth-blob-1 { top: -15%; right: -10%; background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, transparent 70%); }
.auth-blob-2 { bottom: -15%; left: -10%; background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%); animation-delay: -12s; }

@keyframes float {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(150px, 80px) scale(1.1); }
}

/* --- REFINAMIENTO DE REGISTRO --- */
.register-header {
    text-align: center;
    margin-bottom: 45px;
}

.register-header h2 {
    font-size: 2.5rem;
    margin-bottom: 12px;
    letter-spacing: -1.5px;
    background: linear-gradient(135deg, var(--text) 0%, var(--muted) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.register-header p {
    color: var(--muted);
    font-size: 1.1rem;
    font-weight: 500;
}

.btn-register {
    height: 55px;
    font-size: 1.1rem !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4) !important;
    margin-top: 20px;
}

.btn-register:hover {
    box-shadow: 0 15px 40px rgba(99, 102, 241, 0.6) !important;
    transform: translateY(-3px);
}

.auth-footer {
    text-align: center;
    margin-top: 35px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.card.register {
    border-radius: 35px;
    padding: 60px;
    box-shadow: 0 40px 120px rgba(0,0,0,0.25);
}

[data-theme="light"] .register-header h2 {
    -webkit-text-fill-color: #0f172a;
}
</style>

<div class="auth-bg">
    <div class="auth-blob auth-blob-1"></div>
    <div class="auth-blob auth-blob-2"></div>
</div>

<div class="register-header">
    <svg width="64" height="64" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);">
        <rect width="40" height="40" rx="8" fill="url(#reg_grad)" />
        <path d="M20 10C14.4772 10 10 14.4772 10 20C10 25.5228 14.4772 30 20 30C25.5228 30 30 25.5228 30 20C30 14.4772 25.5228 10 20 10ZM20 28C15.5817 28 12 24.4183 12 20C12 15.5817 15.5817 12 20 12C24.4183 12 28 15.5817 28 20C28 24.4183 24.4183 28 20 28Z" fill="white" />
        <path d="M20 15C17.2386 15 15 17.2386 15 20C15 22.7614 17.2386 25 20 25C22.7614 25 25 22.7614 25 20C25 17.2386 22.7614 15 20 15ZM20 23C18.3431 23 17 21.6569 17 20C17 18.3431 18.3431 17 20 17C21.6569 17 23 18.3431 23 20C23 21.6569 21.6569 23 20 23Z" fill="white" fill-opacity="0.5" />
        <defs>
            <linearGradient id="reg_grad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#6366f1" />
                <stop offset="1" stop-color="#a855f7" />
            </linearGradient>
        </defs>
    </svg>
    <h2>Únete a la Revolución</h2>
    <p>Empieza a ahorrar y comer mejor con inteligencia artificial.</p>
</div>

<form method="post" action="/?r=auth.register" class="form">
  <div class="row">
    <div>
      <label for="name">Nombre Completo</label>
      <input type="text" name="name" id="name" placeholder="Tu nombre" required />
    </div>
    <div>
      <label for="email">Tu mejor Email</label>
      <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required />
    </div>
    <div>
      <label for="password">Elige una Contraseña</label>
      <input type="password" name="password" id="password" placeholder="Mín. 6 caracteres" minlength="6" required />
    </div>
    <div>
      <label for="password2">Confirma tu Contraseña</label>
      <input type="password" name="password2" id="password2" placeholder="Repite tu contraseña" minlength="6" required />
    </div>
    <div class="full-width">
      <button class="btn btn-register" type="submit">Crear mi Cuenta Gratis</button>
    </div>
  </div>
</form>

<div class="auth-footer">
    <p class="muted" style="margin:0;">¿Ya tienes una cuenta? <a href="/?r=login" style="color: #6366f1; font-weight: 800;">Inicia sesión aquí</a></p>
</div>
