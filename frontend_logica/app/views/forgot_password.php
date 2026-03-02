<div style="max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">Recuperar Contraseña</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Introduce tu email y te enviaremos un enlace para crear una nueva clave.</p>
    
    <form action="/?r=process_forgot_password" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px; color:#333;">Email:</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        <button type="submit" style="width: 100%; background: #0984e3; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer;">Enviar enlace</button>
    </form>
    <p style="text-align: center; margin-top: 15px;"><a href="/?r=login" style="color: #0984e3; text-decoration: none;">Volver al Login</a></p>
</div>