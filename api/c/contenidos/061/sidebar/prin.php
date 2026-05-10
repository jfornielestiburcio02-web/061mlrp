<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsessionid_url = $_GET['jsessionid'] ?? '';
$modulo_url = $_GET['modulo'] ?? '';

if (empty($jsessionid_url)) die("Se produció un error");

// Buscamos usuario
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents:runQuery?key=$apiKey";
$query = ["structuredQuery" => ["from" => [["collectionId" => "usuarios"]], "where" => ["fieldFilter" => ["field" => ["fieldPath" => "jsessionid"], "op" => "EQUAL", "value" => ["stringValue" => $jsessionid_url]]], "limit" => 1]];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$data = json_decode($res, true);
curl_close($ch);

if (!isset($data[0]['document'])) die("Se produció un error");

$rolLower = strtolower($modulo_url);
$auth = "&jsessionid=$jsessionid_url"; // Usamos & porque ya hay un modulo=
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="tu_archivo_estilo.css"> <style>
        /* Ajustes críticos para que no se solapen los iframes */
        body, html { height: 100%; margin: 0; overflow: hidden; background: #fff; }
        .wrapper { display: flex; height: 100vh; width: 100vw; }
        
        /* El contenedor del sidebar según tu CSS */
        #menuLateral {
            width: 50px; 
            height: 100%;
            transition: width 0.2s;
            z-index: 999;
            background-color: #EBEBEB;
            border-right: 1px solid #ccc;
        }
        #menuLateral:hover {
            width: 200px; /* Se expande para mostrar .contenedorMenus */
        }

        /* Forzamos que se vea el contenido al hacer hover */
        #menuLateral:hover .contenedorMenus { display: block !important; }
        #menuLateral:hover .contenedorMenuSuperior { display: block !important; }

        .contenido-derecha {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        #header-frame { height: 80px; width: 100%; border: none; }
        #main-frame { flex-grow: 1; width: 100%; border: none; }
        
        /* Ajuste para tus clases de submenú */
        .contenedorMenu:hover .nombresubmenu {
            display: block !important;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div id="menuLateral" class="menu">
        
        <div class="contenedorImagenes">
            <div class="menu_handle"><img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/seguimiento_centro.png" width="30"></div>
            <div class="menu_handle"><img src="https://rayuela.educarex.es/modulos/menu/imagenes/v3/comunicaciones.png" width="30"></div>
        </div>

        <div class="contenedorMenus" style="display:none;">
            <div class="contenedorMenu">
                <div class="nombremenu nombremenuNivel0">Seguimiento</div>
                <div class="nombresubmenu">
                    <div class="elementoAccion elementoAccionNivel1">
                        <a href="/c/contenidos/<?php echo $rolLower; ?>/horario/prin.php?modulo=<?php echo $modulo_url . $auth; ?>" target="mainFrame" class="eltoResaltado">Horario</a>
                    </div>
                </div>
            </div>

            <div class="contenedorMenu">
                <div class="nombremenu nombremenuNivel0">Comunicaciones</div>
                <div class="nombresubmenu">
                    <div class="elementoAccion elementoAccionNivel1">
                        <a href="/c/contenidos/<?php echo $rolLower; ?>/mensajes/prin.php?modulo=<?php echo $modulo_url . $auth; ?>" target="mainFrame" class="eltoResaltado">Mensajería</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="contenido-derecha">
        <iframe id="header-frame" src="/c/contenidos/<?php echo $rolLower; ?>/header/prin.php?modulo=<?php echo $modulo_url . $auth; ?>"></iframe>
        <iframe id="main-frame" name="mainFrame" src="/c/contenidos/<?php echo $rolLower; ?>/contenidosCoso/prinb.php?modulo=<?php echo $modulo_url . $auth; ?>"></iframe>
    </div>
</div>

</body>
</html>
