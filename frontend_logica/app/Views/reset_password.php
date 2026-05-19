<div style="max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">Nueva Contraseña</h2>
    
    <form action="/?r=process_reset_password" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px; color:#333;">Nueva contraseña (mínimo 6):</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px; color:#333;">Repetir contraseña:</label>
            <input type="password" name="password2" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        <button type="submit" style="width: 100%; background: #27ae60; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer;">Guardar Contraseña</button>
    </form>
</div>