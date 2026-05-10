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





<!DOCTYPE html>
<html>
<head>
    <style>
        /* TU CSS INTEGRADO Y CORREGIDO */
        body, html { margin: 0; padding: 0; height: 100%; font-family: Verdana, Arial, sans-serif; overflow: hidden; }
        
        .menu {
            background-color: #EBEBEB;
            background-image: url("https://rayuela.educarex.es/modulos/menu/imagenes/v3/bg_menu_lat.jpg");
            background-repeat: repeat-y;
            height: 100vh;
            width: 50px;
            transition: width 0.3s;
            position: absolute;
            left: 0; top: 0;
            z-index: 1000;
            overflow-x: hidden;
        }
        
        .menu:hover { width: 220px; }

        .contenedorImagenes { float: left; width: 45px; padding-top: 10px; }
        
        .menu_handle {
            width: 35px; height: 33px;
            margin: 0 auto 15px auto;
            cursor: pointer;
            display: block;
        }

        .contenedorMenus {
            margin-left: 50px;
            padding-top: 10px;
            width: 170px;
            display: none; /* Se oculta si no hay hover */
        }
        
        .menu:hover .contenedorMenus { display: block; }

        .nombremenuNivel0 {
            font-size: 11px;
            font-weight: bold;
            color: #272726;
            border-bottom: 1px solid #C5E1D1;
            padding: 8px 5px 5px 20px;
            background-image: url("https://rayuela.educarex.es/modulos/menu/imagenes/v3/vineta_menu0.gif");
            background-repeat: no-repeat;
            background-position: 5px center;
        }

        .nombresubmenu { padding: 5px 0; }

        .elementoAccion {
            padding-left: 20px;
            margin-bottom: 5px;
            background-repeat: no-repeat;
            background-position: 7px center;
        }

        /* El famoso lápiz de Rayuela */
        .elementoAccionNivel1 {
            background-image: url("https://rayuela.educarex.es/modulos/menu/imagenes/v3/lapiz1.gif");
        }

        .eltoResaltado {
            font-size: 10px;
            color: #272726;
            text-decoration: none;
        }

        .eltoResaltado:hover { color: #6B9330; font-weight: bold; }

        /* TOOLTIP */
        .Ntooltip span { display: none; }
    </style>
</head>
<body>

<div id="menu_lat" class="menu">
    <div class="contenedorImagenes">
        <div class="menu_handle">
            <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/seguimiento_centro.png" width="35" height="33">
        </div>
        <div class="menu_handle">
            <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/comunicaciones.png" width="35" height="33">
        </div>
        <div class="menu_handle">
            <img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/mi_centro.png" width="35" height="33">
        </div>
    </div>

    <div class="contenedorMenus">
        
        <div class="contenedorMenu">
            <div class="nombremenuNivel0">Seguimiento del Centro</div>
            <div class="nombresubmenu">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/horario/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Horario</a>
                </div>
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/faltas/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Faltas de asistencia</a>
                </div>
            </div>
        </div>

        <div style="height:15px;"></div>

        <div class="contenedorMenu">
            <div class="nombremenuNivel0">Comunicaciones</div>
            <div class="nombresubmenu">
                <div class="elementoAccion elementoAccionNivel1">
                    <a href="/c/contenidos/<?php echo $rolLow; ?>/mensajeria/prin.php<?php echo $linkAuth; ?>" target="mainFrame" class="eltoResaltado">Mensajería</a>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
