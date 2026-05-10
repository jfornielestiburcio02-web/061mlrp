<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Sesión no válida");

// 1. VALIDACIÓN DE USUARIO Y ROL 061
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
            $userDocId = basename($doc['name']); // Obtenemos el ID del documento (ej: jmatamorosd)
            if (isset($f['roles']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "061") { $autorizado = true; break; }
                }
            }
        }
    }
}

if (!$autorizado) die("Acceso denegado: Se requiere rol 061");

// 2. LÓGICA DE SERVICIO (FIRESTORE)
$url_servicios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros?key=$apiKey";

// Comprobar si hay un servicio activo (donde horaSalida sea vacío)
$ch = curl_init($url_servicios);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_serv = curl_exec($ch);
$servicios_data = json_decode($res_serv, true);
curl_close($ch);

$servicio_activo = null;
$historial = [];

if (isset($servicios_data['documents'])) {
    foreach ($servicios_data['documents'] as $d) {
        $fields = $d['fields'];
        $id_doc = basename($d['name']);
        $entrada = $fields['horaEntrada']['stringValue'] ?? '';
        $salida = $fields['horaSalida']['stringValue'] ?? '';
        
        if ($salida === "EN_CURSO") {
            $servicio_activo = ['id' => $id_doc, 'entrada' => $entrada];
        } else {
            $historial[] = ['entrada' => $entrada, 'salida' => $salida];
        }
    }
}

// 3. ACCIONES POST (BOTONES)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ahora = date('Y-m-d H:i:s');
    
    if (isset($_POST['entrar']) && !$servicio_activo) {
        // Crear nuevo registro
        $postData = [
            "fields" => [
                "horaEntrada" => ["stringValue" => $ahora],
                "horaSalida" => ["stringValue" => "EN_CURSO"]
            ]
        ];
        $ch = curl_init($url_servicios);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        header("Location: " . $_SERVER['REQUEST_URI']); exit;
    }

    if (isset($_POST['salir']) && $servicio_activo) {
        // Actualizar registro activo
        $doc_id = $servicio_activo['id'];
        $patchUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros/$doc_id?updateMask.fieldPaths=horaSalida&key=$apiKey";
        $patchData = ["fields" => ["horaSalida" => ["stringValue" => $ahora]]];
        
        $ch = curl_init($patchUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($patchData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        header("Location: " . $_SERVER['REQUEST_URI']); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; text-align: center; }
        .card { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #007d48; }
        .status { font-size: 18px; margin-bottom: 20px; font-weight: bold; }
        .btn { padding: 15px 30px; font-size: 16px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; color: white; transition: 0.3s; }
        .btn-entrar { background: #007d48; }
        .btn-entrar:hover { background: #005a34; }
        .btn-salir { background: #d9534f; }
        .btn-salir:hover { background: #c9302c; }
        .timer { font-size: 24px; color: #007d48; margin: 15px 0; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; background: white; font-size: 13px; }
        th { background: #007d48; color: white; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<div class="card">
    <h2>Control de Servicio 061</h2>
    
    <?php if (!$servicio_activo): ?>
        <div class="status" style="color: #666;">Fuera de Servicio</div>
        <form method="POST">
            <button type="submit" name="entrar" class="btn btn-entrar">ENTRAR DE SERVICIO</button>
        </form>
    <?php else: ?>
        <div class="status" style="color: #007d48;">ESTÁS DE SERVICIO</div>
        <div style="font-size: 14px; color: #666;">Desde: <?php echo $servicio_activo['entrada']; ?></div>
        <div class="timer" id="reloj">00:00:00</div>
        <form method="POST">
            <button type="submit" name="salir" class="btn btn-salir">FINALIZAR SERVICIO</button>
        </form>
        
        <script>
            const horaInicio = new Date("<?php echo $servicio_activo['entrada']; ?>").getTime();
            setInterval(() => {
                const ahora = new Date().getTime();
                const diff = ahora - horaInicio;
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                document.getElementById('reloj').innerText = 
                    (h<10?'0':'')+h + ":" + (m<10?'0':'')+m + ":" + (s<10?'0':'')+s;
            }, 1000);
        </script>
    <?php endif; ?>

    <h3>Historial de Turnos</h3>
    <table>
        <thead>
            <tr>
                <th>Entrada</th>
                <th>Salida</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_reverse($historial) as $h): ?>
                <tr>
                    <td><?php echo $h['entrada']; ?></td>
                    <td><?php echo $h['salida']; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?>
                <tr><td colspan="2" style="color:#999;">No hay registros anteriores</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
