<?php
// 1. CONFIGURACIÓN DE SEGURIDAD (OCULTA)
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = $_GET['jsessionid'] ?? '';
$modulo_url = $_GET['modulo'] ?? '';

// Si no hay sesión, cortamos el grifo
if (empty($jsessionid_url)) {
    die("Sesión no válida");
}

// 2. VALIDACIÓN REAL CONTRA FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents:runQuery?key=$apiKey";
$query = [
    "structuredQuery" => [
        "from" => [["collectionId" => "usuarios"]],
        "where" => [
            "fieldFilter" => [
                "field" => ["fieldPath" => "jsessionid"],
                "op" => "EQUAL",
                "value" => ["stringValue" => $jsessionid_url]
            ]
        ],
        "limit" => 1
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

// Verificamos si el documento existe y si tiene el ROL solicitado
$tieneAcceso = false;
if (isset($data[0]['document']['fields'])) {
    $fields = $data[0]['document']['fields'];
    if (isset($fields['roles']['arrayValue']['values'])) {
        foreach ($fields['roles']['arrayValue']['values'] as $r) {
            if ($r['stringValue'] === $modulo_url) {
                $tieneAcceso = true;
                break;
            }
        }
    }
}

if (!$tieneAcceso) {
    die("Error de autenticación o rol insuficiente.");
}

// Variables para las rutas
$rolLower = strtolower($modulo_url);
$auth = "&jsessionid=" . $jsessionid_url;
?>

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
            <div class="nombremenu nombremenuNivel0">Seguimiento del Centro</div>
            <div class="nombresubmenu">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/horario/prin.php?modulo=<?php echo $modulo_url . $auth; ?>" target="mainFrame" class="eltoResaltado">Horarios</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/faltas/prin.php?modulo=<?php echo $modulo_url . $auth; ?>" target="mainFrame" class="eltoResaltado">Faltas de asistencia</a>
                </div>
            </div>
        </div>

        <div class="contenedorMenu">
            <div class="nombremenu nombremenuNivel0">Comunicaciones</div>
            <div class="nombresubmenu">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLower; ?>/mensajeria/prin.php?modulo=<?php echo $modulo_url . $auth; ?>" target="mainFrame" class="eltoResaltado">Mensajería</a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Parches para que tu CSS funcione sin el JS original de Rayuela */
    #menu_lat:hover .contenedorMenus { display: block !important; }
    .contenedorMenu:hover .nombresubmenu { display: block !important; }
    .nombresubmenu { background: #fff; border-left: 1px solid #ccc; }
</style>
