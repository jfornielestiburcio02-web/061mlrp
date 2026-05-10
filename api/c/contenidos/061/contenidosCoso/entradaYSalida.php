<?php
// 1. ERRORES Y CONFIGURACIÓN
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Error: Sesión no válida");

// 2. BUSCAR USUARIO Y VALIDAR ROL 061
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
            if (isset($f['roles']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "061") { $autorizado = true; break; }
                }
            }
        }
    }
}

if (!$autorizado) die("Acceso denegado: Rol 061 requerido");

$url_servicios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros?key=$apiKey";

// 3. PROCESAR ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ahora = date('d-m-Y H:i:s');

    if (isset($_POST['accion'])) {
        if ($_POST['accion'] == 'entrar') {
            // INICIAR SERVICIO
            $data = ["fields" => [
                "horaEntrada" => ["stringValue" => $ahora],
                "horaSalida" => ["stringValue" => "EN_CURSO"]
            ]];
            enviar($url_servicios, $data, 'POST');
        } 
        elseif ($_POST['accion'] == 'salir' && isset($_POST['id_registro'])) {
            // FINALIZAR SERVICIO
            $id_reg = $_POST['id_registro'];
            $patchUrl = $url_servicios . "/" . $id_reg . "?updateMask.fieldPaths=horaSalida&key=" . $apiKey;
            $data = ["fields" => ["horaSalida" => ["stringValue" => $ahora]]];
            enviar($patchUrl, $data, 'PATCH');
        }
        // Recargar la misma página para ver cambios
        header("Location: entrada_salida.php?jsessionid=$jsid&modulo=$mod");
        exit;
    }
}

function enviar($url, $data, $metodo) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// 4. OBTENER ESTADO ACTUAL E HISTORIAL
$ch = curl_init($url_servicios);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_serv = curl_exec($ch);
$serv_json = json_decode($res_serv, true);
curl_close($ch);

$activo = null;
$historial = [];

if (isset($serv_json['documents'])) {
    foreach ($serv_json['documents'] as $d) {
        $f = $d['fields'];
        $reg = [
            'id' => basename($d['name']),
            'entrada' => $f['horaEntrada']['stringValue'] ?? '',
            'salida' => $f['horaSalida']['stringValue'] ?? ''
        ];
        
        if ($reg['salida'] === "EN_CURSO") {
            $activo = $reg;
        } else {
            $historial[] = $reg;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f0f2f5; padding: 20px; color: #333; }
        .panel { background: white; max-width: 500px; margin: 0 auto; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #007d48; color: white; padding: 20px; text-align: center; font-weight: bold; }
        .content { padding: 30px; text-align: center; }
        
        .status-badge { padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 20px; display: inline-block; }
        .status-badge.on { background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; }
        .status-badge.off { background: #fff1f0; color: #f5222d; border: 1px solid #ffa39e; }

        .btn { width: 100%; padding: 18px; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; color: white; transition: 0.2s; }
        .btn-entrar { background: #007d48; }
        .btn-entrar:hover { background: #005a34; }
        .btn-salir { background: #d93025; }
        .btn-salir:hover { background: #a8251c; }

        .timer { font-size: 35px; font-weight: bold; margin: 20px 0; font-family: monospace; color: #007d48; }
        
        .history { padding: 20px; background: #fafafa; border-top: 1px solid #eee; }
        .history h3 { font-size: 14px; color: #666; margin-top: 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; color: #999; padding-bottom: 10px; }
        td { padding: 8px 0; border-top: 1px solid #eee; }
    </style>
</head>
<body>

<div class="panel">
    <div class="header">CONTROL DE SERVICIO 061</div>
    
    <div class="content">
        <?php if (!$activo): ?>
            <div class="status-badge off">FUERA DE SERVICIO</div>
            <p style="color: #666;">Haz clic para registrar tu entrada</p>
            <form method="POST">
                <input type="hidden" name="accion" value="entrar">
                <button type="submit" class="btn btn-entrar">ENTRAR DE SERVICIO</button>
            </form>
        <?php else: ?>
            <div class="status-badge on">SERVICIO EN CURSO</div>
            <div class="timer" id="reloj">00:00:00</div>
            <p style="font-size: 13px; color: #666;">Iniciado el: <?php echo $activo['entrada']; ?></p>
            
            <form method="POST">
                <input type="hidden" name="accion" value="salir">
                <input type="hidden" name="id_registro" value="<?php echo $activo['id']; ?>">
                <button type="submit" class="btn btn-salir">FINALIZAR SERVICIO</button>
            </form>

            <script>
                const inicio = new Date("<?php echo str_replace('-', '/', $activo['entrada']); ?>").getTime();
                setInterval(() => {
                    const diff = new Date().getTime() - inicio;
                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    document.getElementById('reloj').innerText = 
                        String(h).padStart(2,'0')+":"+String(m).padStart(2,'0')+":"+String(s).padStart(2,'0');
                }, 1000);
            </script>
        <?php endif; ?>
    </div>

    <div class="history">
        <h3>Historial Reciente</h3>
        <table>
            <tr><th>Entrada</th><th>Salida</th></tr>
            <?php foreach (array_reverse($historial) as $h): ?>
                <tr>
                    <td><?php echo $h['entrada']; ?></td>
                    <td><?php echo $h['salida']; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($historial)): ?>
                <tr><td colspan="2" style="text-align:center; color:#ccc;">No hay registros</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
