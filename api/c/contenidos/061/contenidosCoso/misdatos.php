

<?php
// 1. CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("Sesión no válida");

// 2. OBTENER DATOS ACTUALES PARA MOSTRARLOS EN LOS INPUTS
$url_base = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url_base);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$userData = json_decode($res, true);
curl_close($ch);

$userDocId = "";
$currentImg = "";
$currentPass = "";

if (isset($userData['documents'])) {
    foreach ($userData['documents'] as $doc) {
        $f = $doc['fields'];
        if (isset($f['jsessionid']['stringValue']) && $f['jsessionid']['stringValue'] === $jsid) {
            $userDocId = basename($doc['name']);
            $currentImg = $f['imagenPerfil']['stringValue'] ?? '';
            $currentPass = $f['contrasena']['stringValue'] ?? '';
            break;
        }
    }
}

if (!$userDocId) die("Usuario no encontrado");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        
        .profile-preview { text-align: center; margin-bottom: 20px; }
        .profile-preview img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #007d48; background: #eee; }
        
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 13px; font-weight: bold; color: #666; margin-bottom: 5px; }
        .field input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .field input:focus { border-color: #007d48; outline: none; }

        .btn-save { width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; background: #007d48; transition: 0.3s; }
        .btn-save:hover { background: #005a34; }
        .btn-save:disabled { background: #ccc; cursor: not-allowed; }

        #msg { text-align: center; margin-top: 15px; font-size: 14px; display: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>Mis Datos</h2>
    
    <div class="profile-preview">
        <img id="img-view" src="<?php echo $currentImg; ?>" onerror="this.src='https://via.placeholder.com/100?text=PS'">
    </div>

    <div class="field">
        <label>URL Imagen de Perfil</label>
        <input type="text" id="input-img" value="<?php echo $currentImg; ?>" oninput="updatePreview()" placeholder="https://enlace-a-tu-foto.jpg">
    </div>

    <div class="field">
        <label>Nueva Contraseña</label>
        <input type="password" id="input-pass" value="<?php echo $currentPass; ?>" placeholder="Escribe tu nueva contraseña">
    </div>

    <button id="save-btn" class="btn-save" onclick="guardarDatos()">GUARDAR CAMBIOS</button>
    
    <div id="msg"></div>
</div>

<script>
const projectId = "<?php echo $projectId; ?>";
const apiKey = "<?php echo $apiKey; ?>";
const userDocId = "<?php echo $userDocId; ?>";

// Previsualización instantánea de la imagen
function updatePreview() {
    const url = document.getElementById('input-img').value;
    document.getElementById('img-view').src = url;
}

async function guardarDatos() {
    const btn = document.getElementById('save-btn');
    const msg = document.getElementById('msg');
    const newImg = document.getElementById('input-img').value;
    const newPass = document.getElementById('input-pass').value;

    btn.disabled = true;
    btn.innerText = "Guardando...";

    // URL de actualización (PATCH) para Firestore
    // Usamos updateMask para actualizar solo los campos específicos y no borrar el resto (jsessionid, roles, etc)
    const urlPatch = `https://firestore.googleapis.com/v1/projects/${projectId}/databases/(default)/documents/usuarios/${userDocId}?updateMask.fieldPaths=imagenPerfil&updateMask.fieldPaths=contrasena&key=${apiKey}`;

    const body = {
        fields: {
            imagenPerfil: { stringValue: newImg },
            contrasena: { stringValue: newPass }
        }
    };

    try {
        const response = await fetch(urlPatch, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });

        if (response.ok) {
            msg.style.display = "block";
            msg.style.color = "#007d48";
            msg.innerText = "✓ Datos actualizados correctamente";
            // Recargamos el header (si está en un iframe superior) para que se vea la nueva foto
            if (window.parent && window.parent.frames['headerFrame']) {
                window.parent.frames['headerFrame'].location.reload();
            }
        } else {
            throw new Error();
        }
    } catch (e) {
        msg.style.display = "block";
        msg.style.color = "#d93025";
        msg.innerText = "✕ Error al guardar los datos";
    } finally {
        btn.disabled = false;
        btn.innerText = "GUARDAR CAMBIOS";
    }
}
</script>

</body>
</html>
