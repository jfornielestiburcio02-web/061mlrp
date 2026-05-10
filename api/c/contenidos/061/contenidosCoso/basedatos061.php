<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Sesión no válida");

// VALIDACIÓN ROL 061
$url_usuarios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url_usuarios);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$userData = json_decode($res, true);
curl_close($ch);

$autorizado = false;
if (isset($userData['documents'])) {
    foreach ($userData['documents'] as $doc) {
        $f = $doc['fields'];
        if (isset($f['jsessionid']['stringValue']) && $f['jsessionid']['stringValue'] === $jsid) {
            if (isset($f['roles']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "061") { $autorizado = true; break; }
                }
            }
        }
    }
}
if (!$autorizado) die("Acceso denegado: Se requiere rol 061.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 30px; display: flex; flex-direction: column; align-items: center; }
        .search-card { background: white; width: 500px; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #007d48; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        input#query { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .btn-nuevo { background: #007d48; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; font-size: 13px; transition: 0.3s; }
        .btn-nuevo:hover { background: #005a34; }
        .results { margin-top: 20px; border-top: 1px solid #eee; }
        .paciente-item { padding: 15px; border-bottom: 1px solid #f0f0f0; background: #fff; transition: 0.2s; }
        .paciente-item:hover { background: #f9fefb; }
    </style>
</head>
<body>

<div class="search-card">
    <div class="header-box">
        <h2 style="margin:0; color:#007d48;">Base de Datos Pacientes</h2>
        <a href="nuevo_pac.php?jsessionid=<?php echo $jsid; ?>&modulo=<?php echo $mod; ?>" class="btn-nuevo">+ NUEVO PACIENTE</a>
    </div>
    
    <input type="text" id="query" placeholder="Buscar por Nombre o DNI..." onkeyup="buscar()">
    
    <div id="res" class="results"></div>
</div>

<script>
async function buscar() {
    const q = document.getElementById('query').value.toLowerCase();
    const resDiv = document.getElementById('res');
    if (q.length < 3) { resDiv.innerHTML = ""; return; }

    const url = "https://firestore.googleapis.com/v1/projects/<?php echo $projectId; ?>/databases/(default)/documents/pacientes?key=<?php echo $apiKey; ?>";
    const response = await fetch(url);
    const data = await response.json();
    resDiv.innerHTML = "";

    if (data.documents) {
        data.documents.forEach(doc => {
            const f = doc.fields;
            const n = f.nombre.stringValue;
            const d = f.dni.stringValue;
            if (n.toLowerCase().includes(q) || d.toLowerCase().includes(q)) {
                resDiv.innerHTML += `
                <div class="paciente-item">
                    <strong style="color:#007d48;">${n}</strong><br>
                    <small>DNI: ${d} | Grupo: ${f.sangre.stringValue} | Seguro: ${f.seguro.stringValue}</small><br>
                    <small style="color:#888;">Centro: ${f.centro.stringValue}</small>
                </div>`;
            }
        });
    }
}
</script>
</body>
</html>
