<?php
session_start();

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. CHEQUEO DE COOKIE (Para saber qué usuario es)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

// Decodificamos el usuario de la cookie (asumiendo que guardaste solo el usuarioDoc o usuario|id)
$cookieVal = base64_decode($_COOKIE['auth_061_token']);
$usuarioDoc = explode("|", $cookieVal)[0]; 

// 2. CONSULTA A FIRESTORE PARA TRAER EL JSESSIONID REAL Y LOS ROLES
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    
    // EXTRAEMOS EL JSESSIONID QUE ESTÁ EN LA BASE DE DATOS (ej: "q2oiu3rynctuqbqtvo2")
    $jsessionidBD = $data['fields']['jsessionid']['stringValue'] ?? '';
    
    // EXTRAEMOS LOS ROLES
    $misRoles = [];
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue'])) {
            $misRoles[] = $item['stringValue'];
        }
    }
} else {
    header("Location: /modulo_acceso/");
    exit();
}

// 3. PROCESAR SELECCIÓN (Si se pulsa un botón)
if (isset($_POST['set_modulo'])) {
    $_SESSION['modulo_activo'] = $_POST['set_modulo'];
    // REDIRIGIMOS CON EL PARÁMETRO EXACTO DE LA BD
    header("Location: CEC.php?jsessionid=" . $jsessionidBD);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>061 Málaga - Selector de Módulo</title>
    <style>
        body { margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background: #121212; font-family: 'Segoe UI', sans-serif; }
        .container { background: #fff; padding: 40px; border-radius: 15px; text-align: center; color: #333; width: 380px; }
        .logo { height: 70px; margin-bottom: 20px; }
        h2 { margin: 0 0 10px 0; color: #d32f2f; }
        .btn-modulo { display: block; width: 100%; padding: 16px; margin: 12px 0; border: none; border-radius: 8px; background: #d32f2f; color: white; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-modulo.dir { background: #222; }
    </style>
</head>
<body>
    <div class="container">
        <img src="/imagenes/061.png" class="logo">
        <h2>SELECTOR DE ROL</h2>
        <div style="margin-bottom:20px; color:#777;">Usuario: <?php echo $usuarioDoc; ?></div>

        <form method="POST">
            <?php if (in_array("061", $misRoles)): ?>
                <button type="submit" name="set_modulo" value="061" class="btn-modulo">Terminal 061</button>
            <?php endif; ?>

            <?php if (in_array("Dir", $misRoles)): ?>
                <button type="submit" name="set_modulo" value="Dir" class="btn-modulo dir">Dirección (Dir)</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
