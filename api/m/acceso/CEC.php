<?php
// Configuración de Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = isset($_GET['jsessionid']) ? $_GET['jsessionid'] : '';
$modulo_url = isset($_GET['modulo']) ? $_GET['modulo'] : '';

if (empty($jsessionid_url) || empty($modulo_url)) {
    die("Se produció un error");
}

// 1. Buscar usuario por jsessionid
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

// 2. VALIDACIÓN COMPUESTA (Roles + Externos)
$autorizado = false;

// Comprobar en el array 'roles'
if (isset($userData['roles']['arrayValue']['values'])) {
    foreach ($userData['roles']['arrayValue']['values'] as $roleItem) {
        if ($roleItem['stringValue'] === $modulo_url) {
            $autorizado = true;
            break;
        }
    }
}

// Comprobar en el array 'externo' (Médico, TES, Enfermero) si aún no está autorizado
if (!$autorizado && isset($userData['externo']['arrayValue']['values'])) {
    foreach ($userData['externo']['arrayValue']['values'] as $extItem) {
        if ($extItem['stringValue'] === $modulo_url) {
            $autorizado = true;
            break;
        }
    }
}

if (!$autorizado) {
    die("No tiene estos permisos para el perfil: " . htmlspecialchars($modulo_url));
}

// 3. Preparar variables
$rolLower = strtolower($modulo_url);
$baseUrl = "/c/contenidos/$rolLower";
$authParams = "?jsessionid=$jsessionid_url&modulo=$modulo_url";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - <?php echo htmlspecialchars($modulo_url); ?></title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; font-family: Arial, sans-serif; background: #f4f4f4; }
        
        .wrapper { display: flex; height: 100vh; width: 100vw; }

        /* Sidebar dinámico */
        #sidebar-container {
            width: 50px; 
            height: 100%;
            transition: width 0.3s ease;
            background: #fff;
            z-index: 1000;
            border-right: 1px solid #ddd;
            overflow: hidden;
        }
        #sidebar-container:hover {
            width: 220px; 
        }

        .main-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        #header-container {
            height: 60px;
            width: 100%;
            background: #fff;
            border-bottom: 2px solid #007d48;
        }

        #content-container {
            flex-grow: 1;
            width: 100%;
            background: #fff;
        }

        iframe { border: none; width: 100%; height: 100%; display: block; }
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
