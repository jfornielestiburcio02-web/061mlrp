<?php
// 1. CONFIGURACIÓN (Exactamente como tu código)
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = isset($_GET['jsessionid']) ? $_GET['jsessionid'] : '';
$modulo_url = isset($_GET['modulo']) ? $_GET['modulo'] : '';

if (empty($jsessionid_url) || empty($modulo_url)) {
    die("Se produció un error");
}

// 2. FUNCIÓN BUSCAR (Tu lógica exacta)
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

// 3. COMPROBAR ROL (Tu lógica exacta)
$rolesDisponibles = [];
if (isset($userData['roles']['arrayValue']['values'])) {
    foreach ($userData['roles']['arrayValue']['values'] as $roleItem) {
        $rolesDisponibles[] = $roleItem['stringValue'];
    }
}

if (!in_array($modulo_url, $rolesDisponibles)) {
    die("No tiene estos permisos");
}

// Variables para los enlaces
$rolLower = strtolower($modulo_url);
$authParams = "&jsessionid=$jsessionid_url"; // Usamos & porque ya habrá un ?modulo=
?>
<LINK REL="STYLESHEET" href="/css/cec.css">
<div id="menu_lat" class="menu">
    
    <div class="contenedorImagenes">
        <div class="menu_handle">
            <a class="Ntooltip">
                <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/seguimiento_centro.png" width="35" height="33">
                <span>Seguimiento</span>
            </a>
        </div>
        <div class="menu_handle">
            <a class="Ntooltip">
                <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/comunicaciones.png" width="35" height="33">
                <span>Comunicaciones</span>
            </a>
        </div>
    </div>

    <div class="contenedorMenus">
        <div class="contenedorMenu">
            <div class="nombremenu nombremenuNivel0"><?php echo ($modulo_url == '061') ? 'Gestión 061' : 'Dirección'; ?></div>
            <div class="nombresubmenu">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/horario/prin.php?modulo=<?php echo $modulo_url . $authParams; ?>" target="mainFrame" class="eltoResaltado">Horarios</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/mensajeria/prin.php?modulo=<?php echo $modulo_url . $authParams; ?>" target="mainFrame" class="eltoResaltado">Mensajería</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/datos/prin.php?modulo=<?php echo $modulo_url . $authParams; ?>" target="mainFrame" class="eltoResaltado">Datos Personales</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Forzar el comportamiento del Sidebar de Rayuela con tu CSS */
    #menu_lat:hover .contenedorMenus { display: block !important; }
    .contenedorMenu:hover .nombresubmenu { display: block !important; }
    .nombresubmenu { display: none; } /* Se oculta por defecto */
</style>
