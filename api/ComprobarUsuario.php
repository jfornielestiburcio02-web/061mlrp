<?php
// Configuración de Firebase (Oculta en el servidor)
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    // 1. Consultar el usuario en Firestore via REST API
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios/$user?key=$apiKey";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    // 2. Verificar si el usuario existe y la contraseña coincide
    if (isset($data['fields']['contrasena']['stringValue']) && $data['fields']['contrasena']['stringValue'] === $pass) {
        
        // 3. Generar nuevo jsessionid (32 hex aleatorios)
        $newSessionId = bin2hex(random_bytes(16));

        // 4. Actualizar el jsessionid en Firestore (PATCH)
        $updateUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios/$user?updateMask.fieldPaths=jsessionid&key=$apiKey";
        
        $updateData = [
            "fields" => [
                "jsessionid" => ["stringValue" => $newSessionId]
            ]
        ];

        $ch = curl_init($updateUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

        // 5. Redirigir
        header("Location: /m/acceso/sel.php?jsessionid=$newSessionId");
        exit();

    } else {
        echo "<script>alert('Acceso denegado'); window.location.href='index.php';</script>";
    }
}
?>
