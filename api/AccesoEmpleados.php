<?php
// Configuración de tu proyecto de Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f";

// Endpoint de la API REST oficial de Firestore para obtener el documento de mantenimiento
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/mantenimiento/mantenimiento";

$esMantenimiento = false; // Estado por defecto por si falla la conexión

// Realizar la petición REST mediante cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 segundos para que no ralentice la web
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    
    // Verificamos si existe el campo "esMantenimiento" dentro del formato JSON de la API REST de Firestore
    if (isset($data['fields']['esMantenimiento']['booleanValue'])) {
        $esMantenimiento = $data['fields']['esMantenimiento']['booleanValue'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Personal - 061 Andalucía</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        .logo { width: 150px; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007d48; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #005f36; }
        
        /* Estilos específicos para la tarjeta de mantenimiento "en bonito" */
        .mantenimiento-card { border-top: 5px solid #d9534f; }
        .mantenimiento-icono { font-size: 45px; color: #d9534f; margin-bottom: 10px; animation: pulse 2s infinite; }
        .mantenimiento-titulo { color: #333; font-size: 1.2rem; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .mantenimiento-texto { color: #666; font-size: 0.9rem; line-height: 1.4; margin-bottom: 5px; }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.08); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="login-card <?php echo $esMantenimiento ? 'mantenimiento-card' : ''; ?>">
        <img src="https://www.platesa.org/wp-content/uploads/2024/05/061.jpg" alt="Logo 061" class="logo">
        
        <?php if ($esMantenimiento): ?>
            <div class="mantenimiento-icono">🛠️</div>
            <div class="mantenimiento-titulo">APLICACIÓN EN MANTENIMIENTO</div>
            <p class="mantenimiento-texto">Estamos realizando tareas de optimización en los servidores del sistema.</p>
            <p class="mantenimiento-texto" style="font-weight: bold; font-size: 0.8rem; color: #007d48;">Disculpe las molestias. Volveremos pronto.</p>
            
        <?php else: ?>
            <form action="ComprobarUsuario.php" method="POST">
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">ENTRAR</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
