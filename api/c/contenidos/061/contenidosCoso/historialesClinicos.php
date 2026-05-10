<?php
// 1. CONFIGURACIÓN Y SESIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Sesión no válida");

// 2. VALIDACIÓN ROL 061
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
if (!$autorizado) die("Acceso denegado: Área restringida para personal 061.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historiales Clínicos - 061</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .container { display: flex; gap: 20px; width: 1100px; max-width: 100%; }
        
        /* Columna Buscador */
        .search-panel { background: white; width: 350px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); height: fit-content; }
        .res-item { padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; transition: 0.2s; }
        .res-item:hover { background: #f0f7f4; border-left: 4px solid #007d48; }

        /* Columna Formulario */
        .form-panel { background: white; flex: 1; padding: 35px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 8px solid #007d48; }
        .section-title { background: #f8f9fa; padding: 10px; font-weight: bold; color: #007d48; margin: 20px 0 15px 0; border-left: 4px solid #007d48; text-transform: uppercase; font-size: 13px; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
        .field { display: flex; flex-direction: column; }
        label { font-size: 11px; font-weight: bold; color: #666; margin-bottom: 4px; text-transform: uppercase; }
        input, select, textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .full { grid-column: span 3; }
        
        .btn-save { background: #007d48; color: white; border: none; padding: 15px 30px; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 5px; margin-top: 25px; width: 100%; }
        .btn-save:hover { background: #005a34; }
        
        .no-seguro-box { background: #fff1f0; border: 1px solid #ffa39e; padding: 10px; border-radius: 4px; margin-top: 10px; display: flex; align-items: center; gap: 10px; font-size: 13px; color: #cf1322; }
    </style>
</head>
<body>

<div class="container">
    <div class="search-panel">
        <h3 style="color:#007d48; margin-top:0;">🔍 Buscador</h3>
        <input type="text" id="search-input" placeholder="Nombre o DNI del paciente..." style="width:100%; box-sizing:border-box; padding:10px;" onkeyup="buscarHistoriales()">
        <div id="search-results" style="margin-top:15px;"></div>
    </div>

    <div class="form-panel">
        <h2 style="margin-top:0;">Informe de Intervención Clínica</h2>
        
        <div class="section-title">Datos del Paciente</div>
        <div class="grid">
            <div class="field">
                <label>DNI / NIE</label>
                <input type="text" id="p-dni" placeholder="Buscar seguro..." onblur="verificarSeguro(this.value)">
            </div>
            <div class="field">
                <label>Nombre Completo</label>
                <input type="text" id="p-nombre">
            </div>
            <div class="field">
                <label>Seguro Médico Detectado</label>
                <input type="text" id="p-seguro" readonly style="background:#f0f0f0;">
                <div id="box-no-seguro" class="no-seguro-box" style="display:none;">
                    <input type="checkbox" id="chk-no-seguro"> <label style="margin:0; color:#cf1322;">No tiene seguro</label>
                </div>
            </div>
        </div>

        <div class="section-title">Evaluación de la Enfermedad / Incidente</div>
        <div class="grid">
            <div class="field full">
                <label>Motivo de la llamada / Enfermedad principal</label>
                <input type="text" id="m-enfermedad" placeholder="Ej: Dolor torácico, Accidente de tráfico, etc.">
            </div>
            <div class="field">
                <label>Frecuencia Cardíaca (LPM)</label>
                <input type="number" id="v-fc">
            </div>
            <div class="field">
                <label>Tensión Arterial</label>
                <input type="text" id="v-ta" placeholder="120/80">
            </div>
            <div class="field">
                <label>Saturación O2 (%)</label>
                <input type="number" id="v-so2">
            </div>
            <div class="field full">
                <label>Descripción de los síntomas / Observaciones</label>
                <textarea id="m-sintomas" rows="3"></textarea>
            </div>
            <div class="field full">
                <label>Tratamiento aplicado en el lugar</label>
                <textarea id="m-tratamiento" rows="2"></textarea>
            </div>
            <div class="field">
                <label>Traslado a Centro</label>
                <select id="m-traslado">
                    <option value="No requerido">No requerido</option>
                    <option value="H. Regional">H. Regional Universitario</option>
                    <option value="H. Clínico">H. Clínico (Virgen de la Victoria)</option>
                    <option value="Centro de Salud">Centro de Salud Urbano</option>
                </select>
            </div>
            <div class="field">
                <label>Prioridad</label>
                <select id="m-prioridad">
                    <option value="Verde">Verde (Leve)</option>
                    <option value="Amarillo">Amarillo (Urgente)</option>
                    <option value="Rojo">Rojo (Emergencia)</option>
                </select>
            </div>
            <div class="field">
                <label>Fecha Intervención</label>
                <input type="text" id="m-fecha" value="<?php echo date('d/m/Y H:i'); ?>" readonly>
            </div>
        </div>

        <button class="btn-save" onclick="guardarHistorial()">GUARDAR HISTORIAL CLÍNICO</button>
    </div>
</div>

<script>
const apiKey = "<?php echo $apiKey; ?>";
const urlPacientes = "https://firestore.googleapis.com/v1/projects/<?php echo $projectId; ?>/databases/(default)/documents/pacientes?key=" + apiKey;
const urlHistoriales = "https://firestore.googleapis.com/v1/projects/<?php echo $projectId; ?>/databases/(default)/documents/historiales_clinicos?key=" + apiKey;

// 1. BUSCAR SEGURO POR DNI
async function verificarSeguro(dni) {
    if (!dni) return;
    const res = await fetch(urlPacientes);
    const data = await res.json();
    let encontrado = false;

    if (data.documents) {
        data.documents.forEach(doc => {
            const f = doc.fields;
            if (f.dni.stringValue === dni) {
                document.getElementById('p-seguro').value = f.seguro.stringValue;
                document.getElementById('p-nombre').value = f.nombre.stringValue;
                document.getElementById('box-no-seguro').style.display = 'none';
                encontrado = true;
            }
        });
    }

    if (!encontrado) {
        document.getElementById('p-seguro').value = "No encontrado";
        document.getElementById('box-no-seguro').style.display = 'flex';
    }
}

// 2. GUARDAR HISTORIAL
async function guardarHistorial() {
    const body = {
        fields: {
            paciente_dni: { stringValue: document.getElementById('p-dni').value },
            paciente_nombre: { stringValue: document.getElementById('p-nombre').value },
            seguro: { stringValue: document.getElementById('p-seguro').value },
            no_tiene_seguro: { booleanValue: document.getElementById('chk-no-seguro').checked },
            enfermedad: { stringValue: document.getElementById('m-enfermedad').value },
            fc: { stringValue: document.getElementById('v-fc').value },
            ta: { stringValue: document.getElementById('v-ta').value },
            so2: { stringValue: document.getElementById('v-so2').value },
            sintomas: { stringValue: document.getElementById('m-sintomas').value },
            tratamiento: { stringValue: document.getElementById('m-tratamiento').value },
            traslado: { stringValue: document.getElementById('m-traslado').value },
            prioridad: { stringValue: document.getElementById('m-prioridad').value },
            fecha: { stringValue: document.getElementById('m-fecha').value }
        }
    };

    const res = await fetch(urlHistoriales, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });

    if (res.ok) {
        alert("Historial guardado correctamente.");
        location.reload();
    }
}

// 3. BUSCADOR DE HISTORIALES
async function buscarHistoriales() {
    const q = document.getElementById('search-input').value.toLowerCase();
    const resDiv = document.getElementById('search-results');
    if (q.length < 3) { resDiv.innerHTML = ""; return; }

    const res = await fetch(urlHistoriales);
    const data = await res.json();
    resDiv.innerHTML = "";

    if (data.documents) {
        data.documents.forEach(doc => {
            const f = doc.fields;
            const nombre = f.paciente_nombre.stringValue;
            const enf = f.enfermedad.stringValue;
            const fecha = f.fecha.stringValue;

            if (nombre.toLowerCase().includes(q) || f.paciente_dni.stringValue.includes(q)) {
                resDiv.innerHTML += `
                    <div class="res-item" onclick="mostrarDetalle(${JSON.stringify(f).replace(/"/g, '&quot;')})">
                        <strong>${nombre}</strong><br>
                        <small>${fecha} - ${enf}</small>
                    </div>`;
            }
        });
    }
}

function mostrarDetalle(f) {
    let mensaje = `HISTORIAL DETALLADO\n\n`;
    mensaje += `Paciente: ${f.paciente_nombre.stringValue} (${f.paciente_dni.stringValue})\n`;
    mensaje += `Fecha: ${f.fecha.stringValue}\n`;
    mensaje += `Enfermedad: ${f.enfermedad.stringValue}\n`;
    mensaje += `----------------------------\n`;
    mensaje += `Constantes: FC: ${f.fc.stringValue} | TA: ${f.ta.stringValue} | SO2: ${f.so2.stringValue}\n`;
    mensaje += `Síntomas: ${f.sintomas.stringValue}\n`;
    mensaje += `Tratamiento: ${f.tratamiento.stringValue}\n`;
    mensaje += `Prioridad: ${f.prioridad.stringValue} | Traslado: ${f.traslado.stringValue}\n`;
    alert(mensaje);
}
</script>

</body>
</html>
