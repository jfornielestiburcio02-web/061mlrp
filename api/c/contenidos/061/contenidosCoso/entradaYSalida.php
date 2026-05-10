<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("Sesión no válida");

// Validar usuario y obtener ID
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
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; flex-direction: column; align-items: center; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; width: 450px; margin-bottom: 20px; }
        .btn { width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; margin-top: 10px; }
        .btn-entrar { background: #007d48; }
        .btn-salir { background: #d93025; }
        #timer { font-size: 32px; font-weight: bold; margin: 15px 0; color: #007d48; font-family: monospace; }
        
        .history-card { background: white; width: 450px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
        th { text-align: left; border-bottom: 2px solid #f0f0f0; padding: 8px; color: #666; }
        td { padding: 8px; border-bottom: 1px solid #f9f9f9; }
        .duracion { font-weight: bold; color: #007d48; }
        .loading { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>

<div class="card">
    <h2 id="status-text">Cargando...</h2>
    <div id="timer">00:00:00</div>
    <button id="main-btn" class="btn" style="display:none;"></button>
</div>

<div class="history-card">
    <h3 style="margin:0; font-size:16px;">Historial de Servicios</h3>
    <table id="tabla-historial">
        <thead>
            <tr>
                <th>Entrada</th>
                <th>Tiempo</th>
            </tr>
        </thead>
        <tbody id="historial-body">
            </tbody>
    </table>
</div>

<script>
const projectId = "<?php echo $projectId; ?>";
const apiKey = "<?php echo $apiKey; ?>";
const userDocId = "<?php echo $userDocId; ?>";
const urlServicios = `https://firestore.googleapis.com/v1/projects/${projectId}/databases/(default)/documents/servicios/${userDocId}/registros?key=${apiKey}`;

let registroActivoId = null;
let intervalo = null;

async function checkStatus() {
    const res = await fetch(urlServicios);
    const data = await res.json();
    const body = document.getElementById('historial-body');
    body.innerHTML = "";
    registroActivoId = null;

    if (data.documents) {
        // Ordenar por fecha (el más reciente arriba)
        const docs = data.documents.sort((a, b) => b.createTime.localeCompare(a.createTime));

        docs.forEach(d => {
            const f = d.fields;
            const entrada = f.horaEntrada.stringValue;
            const salida = f.horaSalida.stringValue;

            if (salida === "EN_CURSO") {
                registroActivoId = d.name.split('/').pop();
                renderUI(true, entrada);
            } else {
                const fila = `<tr>
                    <td>${entrada.split(',')[0]} <br><small>${entrada.split(',')[1]}</small></td>
                    <td class="duracion">${calcularDiferencia(entrada, salida)}</td>
                </tr>`;
                body.innerHTML += fila;
            }
        });
    }
    if (!registroActivoId) renderUI(false);
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
    const body = { fields: { horaEntrada: { stringValue: ahora }, horaSalida: { stringValue: "EN_CURSO" } } };
    await fetch(urlServicios, { method: 'POST', body: JSON.stringify(body) });
    checkStatus();
}

async function finalizarServicio() {
    document.getElementById('main-btn').classList.add('loading');
    const ahora = new Date().toLocaleString('es-ES');
    const urlPatch = `${urlServicios.split('?')[0]}/${registroActivoId}?updateMask.fieldPaths=horaSalida&key=${apiKey}`;
    await fetch(urlPatch, { method: 'PATCH', body: JSON.stringify({ fields: { horaSalida: { stringValue: ahora } } }) });
    checkStatus();
}

function calcularDiferencia(inicioStr, finStr) {
    const inicio = parseDate(inicioStr).getTime();
    const fin = parseDate(finStr).getTime();
    const diff = fin - inicio;
    
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    return `${h}h ${m}m ${s}s`;
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
    const [d, t] = str.split(', ');
    const [day, month, year] = d.split('/');
    return new Date(`${year}-${month}-${day}T${t.replace(/\./g, '')}`);
}

checkStatus();
</script>
</body>
</html>
