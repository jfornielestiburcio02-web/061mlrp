<?php
session_start();

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. RECUPERAR EL JSESSIONID DE LA URL
$jsessionidUrl = $_GET['jsessionid'] ?? '';

// 2. PROCESAR SELECCIÓN (POST)
if (isset($_POST['set_modulo'])) {
    $_SESSION['modulo_activo'] = $_POST['set_modulo'];
    // Al redirigir, mantenemos el jsessionid en la URL
    header("Location: CEC.xsp?jsessionid=" . urlencode($jsessionidUrl)); 
    exit();
}

// 3. CHEQUEO DE SEGURIDAD (Cookie y Parámetro URL)
if (!isset($_COOKIE['auth_061_token']) || empty($jsessionidUrl)) {
    header("Location: /modulo_acceso/");
    exit();
}

// Decodificamos la cookie (que ahora tiene el formato usuario|jsessionid)
$cookieData = explode("|", base64_decode($_COOKIE['auth_061_token']));
$usuarioDoc = $cookieData[0] ?? '';
$jsessionidCookie = $cookieData[1] ?? '';

// VALIDACIÓN CRUZADA: ¿Coincide el ID de la URL con el de la sesión/cookie?
if ($jsessionidUrl !== $jsessionidCookie) {
    // Si no coinciden, forzamos cierre por seguridad
    setcookie("auth_061_token", "", time() - 3600, "/");
    header("Location: /modulo_acceso/?error=sesion_invalida");
    exit();
}

// 4. CONSULTA A FIRESTORE PARA SACAR LOS ROLES
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$misRoles = [];

if ($httpCode == 200) {
    $data = json_decode($response, true);
    
    // Verificación extra: ¿El jsessionid de la BD coincide con el que trae el usuario?
    $jsessionidBD = $data['fields']['jsessionid']['stringValue'] ?? '';
    
    if ($jsessionidUrl !== $jsessionidBD) {
        header("Location: /modulo_acceso/?error=expired");
        exit();
    }

    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue'])) {
            $misRoles[] = $item['stringValue'];
        }
    }
} else {
    setcookie("auth_061_token", "", time() - 3600, "/");
    header("Location: /modulo_acceso/");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>061 Málaga - Selector de Módulo</title>
    <style>
        body { margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background: #121212; font-family: 'Segoe UI', sans-serif; color: white; }
        .container { background: #fff; padding: 40px; border-radius: 15px; text-align: center; color: #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 380px; }
        .logo { height: 70px; margin-bottom: 20px; }
        h2 { margin: 0 0 10px 0; color: #d32f2f; font-size: 1.5rem; }
        .user-tag { font-size: 0.85rem; color: #777; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-modulo { display: block; width: 100%; padding: 16px; margin: 12px 0; border: none; border-radius: 8px; background: #d32f2f; color: white; font-size: 1rem; font-weight: bold; text-transform: uppercase; cursor: pointer; transition: all 0.3s ease; }
        .btn-modulo:hover { background: #b71c1c; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(211, 47, 47, 0.4); }
        .btn-modulo.dir { background: #222; }
        .btn-modulo.dir:hover { background: #000; }
        .logout { margin-top: 20px; font-size: 0.8rem; color: #d32f2f; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <img src="/imagenes/061.png" class="logo" alt="061">
        <h2>SELECTOR DE ROL</h2>
        <div class="user-tag">Operador: <strong><?php echo htmlspecialchars($usuarioDoc); ?></strong></div>

        <!-- El action incluye el jsessionid para no perderlo al procesar el POST -->
        <form id="formSeleccion" method="POST" action="CEC.xsp?jsessionid=<?php echo htmlspecialchars($jsessionidUrl); ?>">
            <input type="hidden" name="set_modulo" id="inputModulo">
            
            <?php if (in_array("061", $misRoles)): ?>
                <button type="button" class="btn-modulo" onclick="modulo_seleccionado('061')">Acceder Terminal 061</button>
            <?php endif; ?>

            <?php if (in_array("Dir", $misRoles)): ?>
                <button type="button" class="btn-modulo dir" onclick="modulo_seleccionado('Dir')">Acceder Dirección (Dir)</button>
            <?php endif; ?>
        </form>

        <?php if (empty($misRoles)): ?>
            <p style="color: red; font-weight: bold;">Sin roles asignados en sistema.</p>
        <?php endif; ?>

        <div class="logout" onclick="window.location.href='/modulo_acceso/'">Cerrar Sesión</div>
    </div>

    <script>
        function modulo_seleccionado(modulo) {
            document.getElementById('inputModulo').value = modulo;
            document.getElementById('formSeleccion').submit();
        }
    </script>
</body>
</html>
