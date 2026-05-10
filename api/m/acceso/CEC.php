<?php
// Configuración de Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = isset($_GET['jsessionid']) ? $_GET['jsessionid'] : '';
$modulo_url = isset($_GET['modulo']) ? $_GET['modulo'] : '';

if (empty($jsessionid_url) || empty($modulo_url)) {
    die("Se produció un error");
}

// 1. Buscar usuario por jsessionid (Misma lógica que sel.php)
function buscarUsuario($projectId, $apiKey, $sessionId) {
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents:runQuery?key=$apiKey";
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

    return (isset($data[0]['document'])) ? $data[0]['document']['fields'] : null;
}

$userData = buscarUsuario($projectId, $apiKey, $jsessionid_url);

if (!$userData) {
    die("Se produció un error");
}

// 2. Comprobar si el usuario tiene el rol (módulo) solicitado
$rolesDisponibles = [];
if (isset($userData['roles']['arrayValue']['values'])) {
    foreach ($userData['roles']['arrayValue']['values'] as $roleItem) {
        $rolesDisponibles[] = $roleItem['stringValue'];
    }
}

if (!in_array($modulo_url, $rolesDisponibles)) {
    die("No tiene estos permisos");
}

// 3. Preparar variables para las URLs de los iframes
$rolLower = strtolower($modulo_url);
$baseUrl = "/c/contenidos/$rolLower";
$authParams = "?jsessionid=$jsessionid_url";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - <?php echo $modulo_url; ?></title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; font-family: Arial, sans-serif; }
        
        /* Contenedor Principal */
        .wrapper { display: flex; height: 100vh; width: 100vw; }

        /* Estética del Sidebar que se abre al pasar el ratón */
        #sidebar-container {
            width: 60px; /* Ancho contraído */
            height: 100%;
            transition: width 0.3s ease;
            background: #fffff;
            z-index: 100;
        }
        #sidebar-container:hover {
            width: 250px; /* Ancho expandido */
        }

        /* Área de Contenido (Header + Main) */
        .main-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        #header-container {
            height: 60px;
            width: 100%;
            border-bottom: 1px solid #ccc;
        }

        #content-container {
            flex-grow: 1;
            width: 100%;
        }

        iframe { border: none; width: 100%; height: 100%; }
    </style>
</head>
<body>

<div class="wrapper">
    <div id="sidebar-container">
        <iframe src="<?php echo $baseUrl; ?>/sidebar/prin.php<?php echo $authParams; ?>"></iframe>
    </div>

    <div class="main-area">
        <div id="header-container">
            <iframe src="<?php echo $baseUrl; ?>/header/prin.php<?php echo $authParams; ?>"></iframe>
        </div>

        <div id="content-container">
                <iframe name="mainFrame" id="m_frame" src="<?php echo $baseUrl; ?>/contenidosCoso/prinb.php<?php echo $authParams; ?>"></iframe>
        </div>
    </div>
</div>

</body>
</html>
