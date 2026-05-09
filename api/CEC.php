<?php
session_start();

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. CAPTURAR EL TOKEN DE LA URL (Crucial para los iframes)
$jsessionidUrl = $_GET['jsessionid'] ?? '';

// 2. CAPTURAR EL ROL
$rolSolicitado = $_POST['set_modulo'] ?? $_SESSION['modulo_activo'] ?? ''; 

if (empty($rolSolicitado) || empty($jsessionidUrl)) {
    header("Location: /modulo_acceso/SelectorModulo.php?jsessionid=" . $jsessionidUrl);
    exit();
}

// 3. VALIDACIÓN DE COOKIE
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

// Extraer usuario de la cookie (formato usuario|token)
$cookieData = explode("|", base64_decode($_COOKIE['auth_061_token']));
$usuarioDoc = $cookieData[0] ?? '';

// 4. CONSULTA A FIRESTORE PARA VERIFICAR PERMISOS Y TOKEN
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tienePermiso = false;
if ($httpCode == 200) {
    $data = json_decode($response, true);
    
    // Verificamos que el jsessionid de la URL coincide con el de la BD (Seguridad total)
    $jsessionidBD = $data['fields']['jsessionid']['stringValue'] ?? '';
    if ($jsessionidUrl !== $jsessionidBD) {
        header("Location: /modulo_acceso/?error=invalid_token");
        exit();
    }

    // Verificamos el Rol
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue']) && $item['stringValue'] === $rolSolicitado) {
            $tienePermiso = true;
            break;
        }
    }
}

if (!$tienePermiso) {
    header("Location: /modulo_acceso/SelectorModulo.php?jsessionid=$jsessionidUrl&error=no_access");
    exit();
}

$rolMin = strtolower($rolSolicitado);
// Preparamos el string de la query para los iframes
$urlSession = "?jsessionid=" . htmlspecialchars($jsessionidUrl);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CEC - <?php echo strtoupper($rolSolicitado); ?></title>
    <link rel="stylesheet" href="/css/CEC.css">
</head>
<body>

    <!-- Header con sesión -->
    <iframe id="frame-header" src="/header/<?php echo $rolMin; ?>/prin.php<?php echo $urlSession; ?>" name="header"></iframe>

    <div id="contenedor-sidebar">
        <!-- Sidebar con sesión -->
        <iframe id="frame-sidebar" src="/sidebar/<?php echo $rolMin; ?>/prin.php<?php echo $urlSession; ?>" name="sidebar"></iframe>
    </div>

    <!-- Contenido principal con sesión -->
    <iframe id="frame-content" src="/contenidoEmpleado/<?php echo $rolMin; ?>/prin.php<?php echo $urlSession; ?>" name="content"></iframe>

</body>
</html>
