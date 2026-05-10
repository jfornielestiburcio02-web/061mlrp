<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

// Obtener mi nombre para que el remitente sea automático
$url_u = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$user_data = json_decode(file_get_contents($url_u), true);
$miNombreRemitente = "";
foreach ($user_data['documents'] as $doc) {
    if (($doc['fields']['jsessionid']['stringValue'] ?? '') === $jsid) {
        $miNombreRemitente = $doc['fields']['nombrePersona']['stringValue'];
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asunto'])) {
    $url_send = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/mensajeria?key=$apiKey";
    
    $payload = ["fields" => [
        "remitente"    => ["stringValue" => $miNombreRemitente],
        "destinatario" => ["stringValue" => $_POST['destinatario']],
        "asunto"       => ["stringValue" => $_POST['asunto']],
        "cuerpo"       => ["stringValue" => $_POST['cuerpo']],
        "fecha"        => ["stringValue" => date('d/m/Y H:i')]
    ]];

    $ch = curl_init($url_send);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    header("Location: mensajeria.php?jsessionid=$jsid");
    exit;
}
?>
<h2>Nuevo Mensaje</h2>
<form method="POST">
    <p><b>Remitente:</b> <?php echo $miNombreRemitente; ?></p>
    <input type="text" name="destinatario" placeholder="Nombre exacto del destinatario" required style="width:300px;"><br><br>
    <input type="text" name="asunto" placeholder="Asunto" required style="width:300px;"><br><br>
    <textarea name="cuerpo" placeholder="Contenido del mensaje..." required style="width:300px; height:100px;"></textarea><br><br>
    <button type="submit">ENVIAR</button> 
    <a href="mensajeria.php?jsessionid=<?php echo $jsid; ?>">Cancelar</a>
</form>
