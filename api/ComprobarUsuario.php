<?php
// Configuración de tu proyecto Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$destinoBase = "/SelectorModulo.php";

// 1. Verificar si ya existe una cookie
if (isset($_COOKIE['auth_061_token'])) {
    // Si ya existe la cookie, recuperamos el token (que ahora tiene el ID pegado)
    $data = explode("|", base64_decode($_COOKIE['auth_061_token']));
    $idExistente = $data[1] ?? '';
    header("Location: $destinoBase?jsessionid=$idExistente");
    exit();
}

// 2. Procesar POST
$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

$usuarioDoc = preg_replace('/[^a-zA-Z0-9]/', '', $usuario);

// 3. Consulta a Firestore (GET)
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

        // A) Generar un NUEVO jsessionid aleatorio (ejemplo: cadena alfanumérica de 20 caracteres)
        $nuevoJSession = bin2hex(random_bytes(10));

        // B) ACTUALIZAR Firestore con el nuevo jsessionid (Método PATCH)
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

        // C) Guardar en sesión y cookie
        session_start();
        $_SESSION['user'] = $usuarioDoc;
        $_SESSION['jsessionid'] = $nuevoJSession;

        setcookie("auth_061_token", base64_encode($usuarioDoc . "|" . $nuevoJSession), time() + 86400, "/");

        // D) Redirección FINAL con el parámetro en la URL
        header("Location: $destinoBase?jsessionid=$nuevoJSession");
        exit();

    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Acceso Denegado: Usuario no registrado'); window.location='login.php';</script>";
}
?>
