<?php
// Configuración de Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

// Obtener jsessionid de la URL
$jsessionid_url = isset($_GET['jsessionid']) ? $_GET['jsessionid'] : '';

if (empty($jsessionid_url)) {
    die("Se produció un error");
}

/**
 * Función para buscar el usuario que tenga ese jsessionid mediante una consulta (Query)
 */
function buscarUsuarioPorSesion($projectId, $apiKey, $sessionId) {
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents:runQuery?key=$apiKey";
    
    // Estructura de la consulta estructurada de Firestore
    $query = [
        "structuredQuery" => [
            "from" => [["collectionId" => "usuarios"]],
            "where" => [
                "fieldFilter" => [
                    "field" => ["fieldPath" => "jsessionid"],
                    "op" => "EQUAL",
                    "value" => ["stringValue" => $sessionId]
                ]
            ],
            "limit" => 1
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    // Si hay resultados, devolvemos los campos del primer documento encontrado
    if (!empty($data) && isset($data[0]['document'])) {
        return $data[0]['document']['fields'];
    }
    return null;
}

$userData = buscarUsuarioPorSesion($projectId, $apiKey, $jsessionid_url);

if (!$userData) {
    die("Se produció un error");
}

// Extraer los roles del array de Firestore
$rolesDisponibles = [];
if (isset($userData['roles']['arrayValue']['values'])) {
    foreach ($userData['roles']['arrayValue']['values'] as $roleItem) {
        $rolesDisponibles[] = $roleItem['stringValue'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Selector de Rol - 061</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; text-align: center; padding-top: 50px; }
        .container { background: white; width: 350px; margin: auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .logo { width: 120px; margin-bottom: 20px; }
        .btn-rol { 
            display: block; width: 100%; padding: 15px; margin: 10px 0; 
            border: none; border-radius: 5px; cursor: pointer; 
            font-weight: bold; text-decoration: none; color: white;
        }
        .btn-061 { background-color: #007d48; }
        .btn-dir { background-color: #005a9c; }
        .btn-rol:hover { opacity: 0.9; }
    </style>
</head>
<body>

<div class="container">
    <img src="https://www.platesa.org/wp-content/uploads/2024/05/061.jpg" alt="061" class="logo">
    <h3>Seleccione su Rol de Acceso</h3>

    <?php if (in_array("061", $rolesDisponibles)): ?>
        <button class="btn-rol btn-061" onclick="entrar('061')">Acceso Como 061</button>
    <?php endif; ?>

    <?php if (in_array("DIR", $rolesDisponibles)): ?>
        <button class="btn-rol btn-dir" onclick="entrar('DIR')">Acceso Como DIR</button>
    <?php endif; ?>
</div>

<script>
function entrar(modulo) {
    const sid = "<?php echo $jsessionid_url; ?>";
    // Redirige por JS como pediste
    window.location.href = "/m/acceso/CEC.php?jsessionid=" + sid + "&modulo=" + modulo;
}
</script>

</body>
</html>
