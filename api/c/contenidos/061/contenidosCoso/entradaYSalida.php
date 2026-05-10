<?php
// 1. ERRORES Y CONFIGURACIÓN
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Falta jsessionid");

// 2. IDENTIFICAR USUARIO (Tu lógica que ya funciona)
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
            $userDocId = basename($doc['name']); // Ejemplo: jmatamorosd
            $autorizado = true;
            break;
        }
    }
}

if (!$autorizado) die("No autorizado");

// URL DE LA SUBCOLECCIÓN
$url_servicios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros?key=$apiKey";

// 3. PROCESAR EL BOTÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ahora = date('Y-m-d H:i:s');

    if (isset($_POST['entrar'])) {
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
        
        // Recargar para ver los cambios
        header("Location: entrada_salida.php?jsessionid=$jsid&modulo=$mod");
        exit;
    }

    if (isset($_POST['salir']) && isset($_POST['doc_id'])) {
        $doc_id = $_POST['doc_id'];
        $patchUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/servicios/$userDocId/registros/$doc_id?updateMask.fieldPaths=horaSalida&key=$apiKey";
        $patchData = ["fields" => ["horaSalida" => ["stringValue" => $ahora]]];
        
        $ch = curl_init($patchUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($patchData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
        
        header("Location: entrada_salida.php?jsessionid=$jsid&modulo=$mod");
        exit;
    }
}

// 4. LEER ESTADO ACTUAL
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; text-align: center; }
        .card { background: white; max-width: 450px; margin: auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #007d48; }
        .btn { width: 100%; padding: 15px; font-size: 16px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; color: white; }
        .btn-entrar { background: #007d48; }
        .btn-salir { background: #d93025; }
        .timer { font-size: 30px; font-weight: bold; color: #007d48; margin: 20px 0; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 12px; }
        th { background: #eee; padding: 8px; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="card">
    <h2>Servicio 061</h2>

    <?php if (!$servicio_activo): ?>
        <form method="POST" action="entrada_salida.php?jsessionid=<?php echo $jsid; ?>&modulo=<?php echo $mod; ?>">
            <button type="submit" name="entrar" class="btn btn-entrar">ENTRAR DE SERVICIO</button>
        </form>
    <?php else: ?>
        <div style="color: #007d48; font-weight: bold;">SERVICO ACTIVO</div>
        <div class="timer" id="clock">00:00:00</div>
        <form method="POST" action="entrada_salida.php?jsessionid=<?php echo $jsid; ?>&modulo=<?php echo $mod; ?>">
            <input type="hidden" name="doc_id" value="<?php echo $servicio_activo['id']; ?>">
            <button type="submit" name="salir" class="btn btn-salir">FINALIZAR SERVICIO</button>
        </form>

        <script>
            const inicio = new Date("<?php echo str_replace('-', '/', $servicio_activo['entrada']); ?>").getTime();
            setInterval(() => {
                const ahora = new Date().getTime();
                const diff = ahora - inicio;
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                document.getElementById('clock').innerText = 
                    String(h).padStart(2,'0')+":"+String(m).padStart(2,'0')+":"+String(s).padStart(2,'0');
            }, 1000);
        </script>
    <?php endif; ?>

    <h3>Historial</h3>
    <table>
        <tr><th>Entrada</th><th>Salida</th></tr>
        <?php foreach (array_reverse($historial) as $item): ?>
            <tr><td><?php echo $item['entrada']; ?></td><td><?php echo $item['salida']; ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
