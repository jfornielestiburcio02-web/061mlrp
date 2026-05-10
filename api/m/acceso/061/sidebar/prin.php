<?php
// Configuración de Firebase (Oculta en PHP)
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = $_GET['jsessionid'] ?? '';
$modulo_url = $_GET['modulo'] ?? '';

if (empty($jsessionid_url) || empty($modulo_url)) die("Se produció un error");

// 1. Buscamos el usuario por jsessionid
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents:runQuery?key=$apiKey";
$query = ["structuredQuery" => ["from" => [["collectionId" => "usuarios"]], "where" => ["fieldFilter" => ["field" => ["fieldPath" => "jsessionid"], "op" => "EQUAL", "value" => ["stringValue" => $jsessionid_url]]], "limit" => 1]];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

// 2. Verificamos Rol
$hasRole = false;
if (isset($data[0]['document'])) {
    $fields = $data[0]['document']['fields'];
    if (isset($fields['roles']['arrayValue']['values'])) {
        foreach ($fields['roles']['arrayValue']['values'] as $r) {
            if ($r['stringValue'] === $modulo_url) $hasRole = true;
        }
    }
}

if (!$hasRole) die("No tiene estos permisos");

$rolLower = strtolower($modulo_url);
$auth = "?jsessionid=$jsessionid_url";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* INTEGRACIÓN DE TU CSS */
        <?php echo file_get_contents('estilo_rayuela.css'); // O pega el código aquí directamente ?>
        
        /* Ajustes funcionales para el layout */
        body, html { height: 100%; overflow: hidden; font-family: Verdana, Arial, sans-serif; }
        .pantalla { display: flex; height: 100vh; width: 100vw; }
        
        /* El sidebar contenedor */
        .sidebar-wrapper {
            width: 50px; /* Tamaño del icono */
            height: 100%;
            background: #f1f1f1;
            transition: width 0.3s;
            z-index: 100;
            overflow: hidden;
            border-right: 1px solid #ccc;
        }
        
        /* Cuando pasas el ratón se expande como en el video */
        .sidebar-wrapper:hover { width: 220px; }
        .sidebar-wrapper:hover .contenedorMenus { display: block; }

        .main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        #iframe_header { height: 75px; width: 100%; border: none; }
        #iframe_main { flex-grow: 1; width: 100%; border: none; }

        /* Estilo para que los submenús se vean al hacer hover en el contenedor */
        .contenedorMenu:hover .nombresubmenu { display: block; }
    </style>
</head>
<body>

<div class="pantalla">
    <div class="sidebar-wrapper menu">
        <div class="contenedorImagenes">
            <div class="menu_handle"><img src="/images/icons/seguimiento.png" width="30"></div>
            <div class="menu_handle"><img src="/images/icons/comunicaciones.png" width="30"></div>
            <div class="menu_handle"><img src="/images/icons/centro.png" width="30"></div>
        </div>

        <div class="contenedorMenus">
            <div class="contenedorMenu">
                <div class="nombremenu nombremenuNivel0">Seguimiento</div>
                <div class="nombresubmenu">
                    <div class="elementoAccion elementoAccionNivel1">
                        <a href="/c/contenidos/<?php echo $rolLower; ?>/horario/prin.php<?php echo $auth; ?>" target="mainFrame" class="tituloElemento">Horarios</a>
                    </div>
                    <div class="elementoAccion elementoAccionNivel1">
                        <a href="/c/contenidos/<?php echo $rolLower; ?>/examenes/prin.php<?php echo $auth; ?>" target="mainFrame" class="tituloElemento">Exámenes</a>
                    </div>
                </div>
            </div>

            <div class="contenedorMenu">
                <div class="nombremenu nombremenuNivel0">Comunicaciones</div>
                <div class="nombresubmenu">
                    <div class="elementoAccion elementoAccionNivel1">
                        <a href="/c/contenidos/<?php echo $rolLower; ?>/mensajes/prin.php<?php echo $auth; ?>" target="mainFrame" class="tituloElemento">Mensajería</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-panel">
        <iframe id="iframe_header" src="/c/contenidos/<?php echo $rolLower; ?>/header/prin.php<?php echo $auth; ?>"></iframe>
        <iframe id="iframe_main" name="mainFrame" src="/c/contenidos/<?php echo $rolLower; ?>/contenidosCoso/prinb.php<?php echo $auth; ?>"></iframe>
    </div>
</div>

</body>
</html>
