<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("ERROR: SESION_NULA");

// 1. VALIDAR ROL 061 Y OBTENER MI NOMBRE
$url_u = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$user_data = json_decode(file_get_contents($url_u), true);
$miNombre = "";

if (isset($user_data['documents'])) {
    foreach ($user_data['documents'] as $doc) {
        $f = $doc['fields'];
        if (($f['jsessionid']['stringValue'] ?? '') === $jsid) {
            if (isset($f['roles']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "061") { 
                        $miNombre = $f['nombrePersona']['stringValue']; 
                        break; 
                    }
                }
            }
        }
    }
}
if (!$miNombre) die("ERROR: ACCESO_DENEGADO_061");

// 2. OBTENER MENSAJES DE LA COLECCIÓN
$url_m = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/mensajeria?key=$apiKey";
$res_m = json_decode(file_get_contents($url_m), true);

echo "<h1>Mensajería 061</h1>";
echo "<a href='crear_mensaje.php?jsessionid=$jsid'>[ + Redactar ]</a><hr>";

if (isset($res_m['documents'])) {
    // Invertimos para ver los más nuevos arriba
    $docs = array_reverse($res_m['documents']);
    foreach ($docs as $m) {
        $mf = $m['fields'];
        $idMsg = basename($m['name']);
        
        // Filtro: Solo si el destinatario soy yo
        if (($mf['destinatario']['stringValue'] ?? '') === $miNombre) {
            echo "<div style='margin-bottom:10px;'>";
            echo "<b>De:</b> " . $mf['remitente']['stringValue'] . " | ";
            echo "<b>Asunto:</b> <a href='ver_mensaje.php?detalle&x_mensaje=$idMsg&jsessionid=$jsid&pagina_anterior=-1'>" . $mf['asunto']['stringValue'] . "</a> | ";
            echo "<small>" . ($mf['fecha']['stringValue'] ?? '') . "</small>";
            echo "</div><hr>";
        }
    }
} else {
    echo "No hay mensajes.";
}
?>
