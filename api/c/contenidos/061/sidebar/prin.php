<?php
// Configuración
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Se produció un error");

// BUSQUEDA ULTRA-SIMPLE: Solo pedimos los documentos de la colección 'usuarios'
// Y filtramos por jsessionid de forma manual para evitar errores de la API Query
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$json = json_decode($res, true);
curl_close($ch);

$autorizado = false;
$userData = null;

// Recorremos los documentos para ver si el jsessionid coincide
if (isset($json['documents'])) {
    foreach ($json['documents'] as $doc) {
        $fields = $doc['fields'];
        if (isset($fields['jsessionid']['stringValue']) && $fields['jsessionid']['stringValue'] === $jsid) {
            $userData = $fields;
            $autorizado = true;
            break;
        }
    }
}

if (!$autorizado) die("Se produció un error");

// Preparar rutas
$rolLow = strtolower($mod);
$linkAuth = "?jsessionid=$jsid";
?>

<div id="menu_lat" class="menu">
    <div class="contenedorImagenes">
        <div class="menu_handle">
            <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/seguimiento_centro.png" width="35" height="33">
        </div>
        <div class="menu_handle">
            <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/comunicaciones.png" width="35" height="33">
        </div>
    </div>

    <div class="contenedorMenus">
        <div class="contenedorMenu">
            <div class="nombremenu nombremenuNivel0">
                <?php echo ($mod == '061') ? 'Gestión 061' : 'Dirección'; ?>
            </div>
            <div class="nombresubmenu" style="display:block;">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/horario/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Horarios</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/mensajeria/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Mensajería</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/config/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Ajustes</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Tu CSS de Rayuela hará el resto, pero esto asegura el hover */
    #menu_lat:hover .contenedorMenus { display: block !important; }
    .contenedorMenu:hover .nombresubmenu { display: block !important; }
</style>
