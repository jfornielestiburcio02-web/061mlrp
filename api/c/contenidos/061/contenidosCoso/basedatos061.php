<?php
// 1. CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("Sesión no válida");

// 2. VERIFICACIÓN DE ROL 061 (Lado del servidor)
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
if (!$autorizado) die("Acceso denegado: Se requiere rol 061 para acceder a la base de datos de pacientes.");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; display: flex; gap: 20px; justify-content: center; }
        
        /* Buscador */
        .search-box { background: white; width: 350px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content; }
        .search-box input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .result-item { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; font-size: 14px; }
        .result-item:hover { background: #f9f9f9; }

        /* Formulario */
        .form-container { background: white; width: 600px; padding: 40px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 10px solid #007d48; }
        .banner { color: #007d48; font-weight: bold; border-bottom: 2px solid #007d48; margin-bottom: 20px; padding-bottom: 5px; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .field { margin-bottom: 15px; }
        .field label { display: block; font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 3px; }
        .field input, .field select, .field textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; }
        
        .full { grid-column: span 2; }
        .btn-crear { background: #007d48; color: white; border: none; padding: 15px; width: 100%; font-weight: bold; cursor: pointer; margin-top: 10px; border-radius: 4px; }
        .btn-crear:hover { background: #005a34; }
        
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; display: none; }
    </style>
</head>
<body>

<div class="search-box">
    <h3 style="margin-top:0; color:#007d48;">Buscador de Pacientes</h3>
    <input type="text" id="query" placeholder="Buscar por DNI o Nombre..." onkeyup="buscarPacientes()">
    <div id="resultados" style="margin-top:15px;"></div>
</div>

<div class="form-container">
    <div class="success-msg" id="msg-ok">Paciente registrado correctamente en la base de datos.</div>
    <div class="banner">━━━━━━━━━━━━━━━━━━━━━━ SERVICIO DE SALUD DE MÁLAGA</div>
    
    <div class="grid">
        <div class="field full">
            <label>Nombre completo</label>
            <input type="text" id="f-nombre" placeholder="Nombre y Apellidos">
        </div>
        <div class="field">
            <label>DNI del ciudadano</label>
            <input type="text" id="f-dni" placeholder="12345678X">
        </div>
        <div class="field">
            <label>Fecha de nacimiento</label>
            <input type="date" id="f-nacimiento">
        </div>
        <div class="field">
            <label>Grupo sanguíneo</label>
            <select id="f-sangre">
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
            </select>
        </div>
        <div class="field">
            <label>Seguro Médico</label>
            <select id="f-seguro">
                <option value="Básico">Básico</option>
                <option value="Baremado">Baremado</option>
                <option value="Privado">Privado</option>
            </select>
        </div>
        <div class="field full">
            <label>Alergias conocidas</label>
            <textarea id="f-alergias" rows="2" placeholder="Medicamentos, alimentos, etc."></textarea>
        </div>
        <div class="field full">
            <label>Condiciones médicas</label>
            <textarea id="f-condiciones" rows="2" placeholder="Diabetes, asma, etc."></textarea>
        </div>
        <div class="field">
            <label>Centro médico asignado</label>
            <select id="f-centro">
                <option value="Hospital Regional Universitario de Málaga">Hospital Regional Universitario de Málaga</option>
                <option value="Hospital Universitario Virgen de la Victoria">Hospital Universitario Virgen de la Victoria</option>
            </select>
        </div>
        <div class="field">
            <label>Fecha de emisión</label>
            <input type="text" id="f-emision" value="<?php echo date('d/m/Y'); ?>" readonly>
        </div>
    </div>
    
    <button class="btn-crear" onclick="registrarPaciente()">REGISTRAR NUEVO PACIENTE</button>
</div>

<script>
const apiKey = "<?php echo $apiKey; ?>";
const urlPacientes = "https://firestore.googleapis.com/v1/projects/<?php echo $projectId; ?>/databases/(default)/documents/pacientes?key=" + apiKey;

// BUSCAR
async function buscarPacientes() {
    const q = document.getElementById('query').value.toLowerCase();
    const resDiv = document.getElementById('resultados');
    if (q.length < 3) { resDiv.innerHTML = ""; return; }

    const response = await fetch(urlPacientes);
    const data = await response.json();
    resDiv.innerHTML = "";

    if (data.documents) {
        data.documents.forEach(doc => {
            const f = doc.fields;
            const nombre = f.nombre.stringValue;
            const dni = f.dni.stringValue;

            if (nombre.toLowerCase().includes(q) || dni.toLowerCase().includes(q)) {
                resDiv.innerHTML += `
                    <div class="result-item" onclick="cargarPaciente(${JSON.stringify(f).replace(/"/g, '&quot;')})">
                        <strong>${nombre}</strong><br>
                        <small>DNI: ${dni} | Sangre: ${f.sangre.stringValue}</small>
                    </div>`;
            }
        });
    }
}

// REGISTRAR
async function registrarPaciente() {
    const btn = document.querySelector('.btn-crear');
    btn.disabled = true;
    
    const body = {
        fields: {
            nombre: { stringValue: document.getElementById('f-nombre').value },
            dni: { stringValue: document.getElementById('f-dni').value },
            nacimiento: { stringValue: document.getElementById('f-nacimiento').value },
            sangre: { stringValue: document.getElementById('f-sangre').value },
            seguro: { stringValue: document.getElementById('f-seguro').value },
            alergias: { stringValue: document.getElementById('f-alergias').value },
            condiciones: { stringValue: document.getElementById('f-condiciones').value },
            centro: { stringValue: document.getElementById('f-centro').value },
            emision: { stringValue: document.getElementById('f-emision').value }
        }
    };

    const res = await fetch(urlPacientes, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });

    if (res.ok) {
        document.getElementById('msg-ok').style.display = 'block';
        setTimeout(() => location.reload(), 2000);
    }
}

// VER DATOS (Al hacer clic en el buscador)
function cargarPaciente(f) {
    document.getElementById('f-nombre').value = f.nombre.stringValue;
    document.getElementById('f-dni').value = f.dni.stringValue;
    document.getElementById('f-nacimiento').value = f.nacimiento.stringValue;
    document.getElementById('f-sangre').value = f.sangre.stringValue;
    document.getElementById('f-seguro').value = f.seguro.stringValue;
    document.getElementById('f-alergias').value = f.alergias.stringValue;
    document.getElementById('f-condiciones').value = f.condiciones.stringValue;
    document.getElementById('f-centro').value = f.centro.stringValue;
    document.getElementById('f-emision').value = f.emision.stringValue;
    alert("Datos del paciente cargados en el formulario.");
}
</script>

</body>
</html>
