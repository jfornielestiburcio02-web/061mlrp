<?php
// Configuración básica
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("Sesión no válida");

// 1. Obtener datos del usuario y validar rol 061
$url_base = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url_base);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$userData = json_decode($res, true);
curl_close($ch);

$userDocId = "";
$autorizado = false;

if (isset($userData['documents'])) {
    foreach ($userData['documents'] as $doc) {
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #f0f2f5; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; width: 350px; }
        .btn { width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; margin-top: 20px; }
        .btn-entrar { background: #007d48; }
        .btn-salir { background: #d93025; }
        #timer { font-size: 28px; font-weight: bold; margin: 15px 0; color: #333; }
        .loading { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>

<div class="card" id="app">
    <h2 id="status-text">Cargando estado...</h2>
    <div id="timer">00:00:00</div>
    <button id="main-btn" class="btn" style="display:none;"></button>
</div>

<script>
const projectId = "<?php echo $projectId; ?>";
const apiKey = "<?php echo $apiKey; ?>";
const userDocId = "<?php echo $userDocId; ?>";
const urlServicios = `https://firestore.googleapis.com/v1/projects/${projectId}/databases/(default)/documents/servicios/${userDocId}/registros?key=${apiKey}`;

let registroActivoId = null;
let intervalo = null;

// Función para obtener el estado actual desde Firestore
async function checkStatus() {
    const res = await fetch(urlServicios);
    const data = await res.json();
    registroActivoId = null;

    if (data.documents) {
        const activo = data.documents.find(d => d.fields.horaSalida.stringValue === "EN_CURSO");
        if (activo) {
            registroActivoId = activo.name.split('/').pop();
            renderUI(true, activo.fields.horaEntrada.stringValue);
            return;
        }
    }
    renderUI(false);
}

function renderUI(isEnServicio, horaEntrada = null) {
    const btn = document.getElementById('main-btn');
    const status = document.getElementById('status-text');
    btn.style.display = 'block';
    btn.classList.remove('loading');

    if (isEnServicio) {
        status.innerText = "ESTÁS DE SERVICIO";
        btn.innerText = "FINALIZAR SERVICIO";
        btn.className = "btn btn-salir";
        btn.onclick = finalizarServicio;
        startTimer(horaEntrada);
    } else {
        status.innerText = "FUERA DE SERVICIO";
        btn.innerText = "ENTRAR DE SERVICIO";
        btn.className = "btn btn-entrar";
        btn.onclick = entrarServicio;
        stopTimer();
    }
}

async function entrarServicio() {
    document.getElementById('main-btn').classList.add('loading');
    const ahora = new Date().toLocaleString('es-ES');
    const body = {
        fields: {
            horaEntrada: { stringValue: ahora },
            horaSalida: { stringValue: "EN_CURSO" }
        }
    };
    await fetch(urlServicios, { method: 'POST', body: JSON.stringify(body) });
    checkStatus();
}

async function finalizarServicio() {
    document.getElementById('main-btn').classList.add('loading');
    const ahora = new Date().toLocaleString('es-ES');
    const urlPatch = `${urlServicios.split('?')[0]}/${registroActivoId}?updateMask.fieldPaths=horaSalida&key=${apiKey}`;
    const body = {
        fields: { horaSalida: { stringValue: ahora } }
    };
    await fetch(urlPatch, { method: 'PATCH', body: JSON.stringify(body) });
    checkStatus();
}

function startTimer(entrada) {
    stopTimer();
    const inicio = parseDate(entrada).getTime();
    intervalo = setInterval(() => {
        const diff = new Date().getTime() - inicio;
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        document.getElementById('timer').innerText = 
            `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }, 1000);
}

function stopTimer() {
    clearInterval(intervalo);
    document.getElementById('timer').innerText = "00:00:00";
}

function parseDate(str) {
    // Formato: DD/MM/YYYY, HH:MM:SS
    const [d, t] = str.split(', ');
    const [day, month, year] = d.split('/');
    return new Date(`${year}-${month}-${day}T${t}`);
}

checkStatus();
</script>

</body>
</html>
