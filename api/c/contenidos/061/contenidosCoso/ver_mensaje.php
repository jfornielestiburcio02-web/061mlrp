<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';
$idMsg = $_GET['x_mensaje'] ?? '';

if (!$idMsg) die("ERROR: ID_NO_RECIBIDO");

$url_v = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/mensajeria/$idMsg?key=$apiKey";
$msg = json_decode(file_get_contents($url_v), true);

if (!isset($msg['fields'])) die("ERROR: MENSAJE_NO_EXISTE");

$f = $msg['fields'];

echo "<h1>Lectura de Mensaje</h1>";
echo "<p><b>De:</b> " . ($f['remitente']['stringValue'] ?? 'Desconocido') . "</p>";
echo "<p><b>Fecha:</b> " . ($f['fecha']['stringValue'] ?? '---') . "</p>";
echo "<p><b>Asunto:</b> " . ($f['asunto']['stringValue'] ?? 'Sin asunto') . "</p>";
echo "<hr>";
echo "<div style='padding:15px; background:#fff; border:1px solid #ddd;'>";
echo nl2br($f['cuerpo']['stringValue'] ?? '');
echo "</div>";
echo "<br><br>";
echo "<a href='mensajeria.php?jsessionid=$jsid'>[ Volver a la bandeja ]</a>";
?>
