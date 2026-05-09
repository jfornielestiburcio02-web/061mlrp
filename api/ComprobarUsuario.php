<?php
// Configuración de tu proyecto Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$destinoBase = "SelectorModulo.php"; // Nombre del archivo al que redirige

// 1. Verificar si ya existe una cookie válida para saltar el login
if (isset($_COOKIE['auth_061_token'])) {
    $data = explode("|", base64_decode($_COOKIE['auth_061_token']));
    $usuarioDoc = $data[0] ?? '';
    $idExistente = $data[1] ?? '';
    
    // Si tenemos los datos, vamos directo al selector con el token actual
    if ($usuarioDoc && $idExistente) {
        header("Location: $destinoBase?jsessionid=$idExistente");
        exit();
    }
}

// 2. Procesar el formulario de Login (POST)
$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

// Limpiar el ID del documento (ej: jmatamorosd)
$usuarioDoc = preg_replace('/[^a-zA-Z0-9]/', '', $usuario);

// 3. Consulta a Firestore para validar credenciales
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
    $passFirebase = $json['fields']['contrasena']['stringValue'] ?? '';

    if ($passInput === $passFirebase) {
        // --- LOGIN EXITOSO ---

        // A) Generar el NUEVO jsessionid (el string que verás en la BD)
        // Usamos una cadena alfanumérica similar a la de tu captura (q2oiu...)
        $nuevoJSession = substr(bin2hex(random_bytes(10)), 0, 20);

        // B) ACTUALIZAR Firestore con este nuevo token (Método PATCH)
        // Esto sobreescribe el campo jsessionid en el documento del empleado
        $updateUrl = "{$url}?updateMask.fieldPaths=jsessionid";
        $updateData = [
            "fields" => [
                "jsessionid" => ["stringValue" => $nuevoJSession]
            ]
        ];

        $chUp = curl_init();
        curl_setopt($chUp, CURLOPT_URL, $updateUrl);
        curl_setopt($chUp, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($chUp, CURLOPT_POSTFIELDS, json_encode($updateData));
        curl_setopt($chUp, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chUp, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($chUp);
        curl_close($chUp);

        // C) Iniciar sesión y guardar Cookie
        session_start();
        $_SESSION['user'] = $usuarioDoc;
        $_SESSION['jsessionid'] = $nuevoJSession;

        // Guardamos usuario|token en base64 para recuperarlo si refresca la página
        setcookie("auth_061_token", base64_encode($usuarioDoc . "|" . $nuevoJSession), time() + 86400, "/");

        // D) Redirección al Selector pasando el token por parámetro GET
        header("Location: $destinoBase?jsessionid=$nuevoJSession");
        exit();

    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Acceso Denegado: Usuario no registrado'); window.location='login.php';</script>";
}
?>
