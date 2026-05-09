<?php
// Configuración de tu proyecto Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$destinoFinal = "/modulo_acceso/controlador.061";

// 1. Verificar si ya existe una cookie de acceso previo
if (isset($_COOKIE['auth_061_token'])) {
    header("Location: $destinoFinal");
    exit();
}

// 2. Procesar el formulario POST
$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

// Limpiamos el ID del documento
$usuarioDoc = preg_replace('/[^a-zA-Z0-9]/', '', $usuario);

// 3. Consulta a Firestore
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $json = json_decode($response, true);
    
    // Extraemos la contraseña y el jsessionid del JSON de Firestore
    $passFirebase = $json['fields']['contrasena']['stringValue'] ?? '';
    $jsessionidDB = $json['fields']['jsessionid']['stringValue'] ?? '';

    if ($passInput === $passFirebase) {
        // --- LOGIN EXITOSO ---
        
        session_start();
        $_SESSION['user'] = $usuarioDoc;
        // Guardamos el string de la BD en la sesión para que esté disponible en la siguiente página
        $_SESSION['jsessionid'] = $jsessionidDB;

        // Creamos la COOKIE incluyendo el jsessionid (opcional, codificado en base64)
        // Esto permite que el "cache" local también lo identifique
        $cookieValue = base64_encode($usuarioDoc . "|" . $jsessionidDB);
        setcookie("auth_061_token", $cookieValue, time() + 86400, "/");

        // Redirección
        header("Location: $destinoFinal");
        exit();

    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Acceso Denegado: Usuario no registrado'); window.location='login.php';</script>";
}
?>
