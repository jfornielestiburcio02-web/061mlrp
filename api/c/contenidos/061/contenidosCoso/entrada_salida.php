<?php
// 1. CONFIGURACIÓN Y VALIDACIÓN
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Error: Sesión no válida");

// 2. BUSCAR USUARIO PARA OBTENER SU ID DE DOCUMENTO
$url_base = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url_base);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$json = json_decode($res, true);
curl_close($ch);

$userDocId = "";
$autorizado = false;

if (isset($json['documents'])) {
    foreach ($json['documents'] as $doc) {
        $f = $doc['fields'];
        if (isset($f['jsessionid']['stringValue']) && $f['jsessionid']['stringValue'] === $jsid) {
            $userDocId = basename($doc['name']);
            // Validar rol 061
            if (isset($f['roles']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "061") { $autorizado = true; break; }
                }
            }
        }
    }
}

if (!$autorizado) die("Acceso denegado: Se requiere rol 061");

$url_servicios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros?key=$apiKey";

// 3. PROCESAR ACCIONES Y EJECUTAR JAVASCRIPT BACK
$ejecutarBack = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ahora = date('d-m-Y H:i:s');

    if (isset($_POST['entrar'])) {
        $postData = ["fields" => [
            "horaEntrada" => ["stringValue" => $ahora],
            "horaSalida" => ["stringValue" => "EN_CURSO"]
        ]];
        enviarAFirebase($url_servicios, $postData, 'POST');
        $ejecutarBack = true;
    }

    if (isset($_POST['salir']) && isset($_POST['doc_id'])) {
        $doc_id = $_POST['doc_id'];
        $patchUrl = $url_servicios . "/" . $doc_id . "?updateMask.fieldPaths=horaSalida&key=" . $apiKey;
        $patchData = ["fields" => ["horaSalida" => ["stringValue" => $ahora]]];
        enviarAFirebase($patchUrl, $patchData, 'PATCH');
        $ejecutarBack = true;
    }
}

function enviarAFirebase($url, $data, $metodo) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// 4. CONSULTAR ESTADO PARA LA INTERFAZ
$ch = curl_init($url_servicios);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_serv = curl_exec($ch);
$servicios_data = json_decode($res_serv, true);
curl_close($ch);

$servicio_activo = null;
if (isset($servicios_data['documents'])) {
    foreach ($servicios_data['documents'] as $d) {
        if (($d['fields']['horaSalida']['stringValue'] ?? '') === "EN_CURSO") {
            $servicio_activo = ['id' => basename($d['name']), 'entrada' => $d['fields']['horaEntrada']['stringValue']];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 6px solid #007d48; width: 350px; }
        .btn { width: 100%; padding: 15px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; transition: 0.2s; }
        .btn-entrar { background: #007d48; }
        .btn-salir { background: #d93025; }
        .msg { margin-bottom: 20px; font-weight: bold; color: #333; }
    </style>
    
    <?php if ($ejecutarBack): ?>
    <script>
        // Si se ha procesado la acción, esperamos medio segundo y volvemos atrás
        setTimeout(function() {
            window.history.back();
        }, 500);
    </script>
    <?php endif; ?>
</head>
<body>

<div class="card">
    <?php if ($ejecutarBack): ?>
        <div class="msg" style="color: #007d48;">¡Registro guardado correctamente!</div>
        <p>Volviendo a la pantalla anterior...</p>
    <?php else: ?>
        <h2>Control de Jornada</h2>
        <div class="msg">
            Estado: <?php echo $servicio_activo ? "DE SERVICIO" : "FUERA DE SERVICIO"; ?>
        </div>

        <form method="POST">
            <?php if (!$servicio_activo): ?>
                <button type="submit" name="entrar" class="btn btn-entrar">ENTRAR DE SERVICIO</button>
            <?php else: ?>
                <input type="hidden" name="doc_id" value="<?php echo $servicio_activo['id']; ?>">
                <button type="submit" name="salir" class="btn btn-salir">FINALIZAR SERVICIO</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
