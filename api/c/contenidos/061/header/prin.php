<?php
// 1. CONFIGURACIÓN Y VALIDACIÓN (Igual que el Sidebar para seguridad)
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Error de sesión");

// Buscamos los datos del usuario en Firestore
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$json = json_decode($res, true);
curl_close($ch);

$userData = null;
if (isset($json['documents'])) {
    foreach ($json['documents'] as $doc) {
        $f = $doc['fields'];
        if (isset($f['jsessionid']['stringValue']) && $f['jsessionid']['stringValue'] === $jsid) {
            $userData = $f;
            break;
        }
    }
}

if (!$userData) die("Sesión inválida");

// Extraer datos para el Header
$nombre = $userData['nombrePersona']['stringValue'] ?? 'Usuario';
$imgPerfil = $userData['imagenPerfil']['stringValue'] ?? 'https://via.placeholder.com/40';

// Extraer el array "externo"
$externos = [];
if (isset($userData['externo']['arrayValue']['values'])) {
    foreach ($userData['externo']['arrayValue']['values'] as $v) {
        $externos[] = $v['stringValue'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            margin: 0; padding: 0 20px; height: 60px; 
            background: #fff; border-bottom: 1px solid #ddd;
            display: flex; align-items: center; justify-content: space-between;
            font-family: Arial, sans-serif;
        }
        .user-info { display: flex; align-items: center; }
        .user-info img { 
            width: 40px; height: 40px; border-radius: 50%; 
            object-fit: cover; margin-right: 15px; border: 1px solid #ccc;
        }
        .user-info span { font-weight: bold; color: #333; font-size: 14px; }
        
        .roles-nav { display: flex; gap: 10px; }
        .btn-externo {
            background: #007d48; color: white; border: none;
            padding: 8px 12px; border-radius: 4px; cursor: pointer;
            font-size: 12px; font-weight: bold; transition: 0.2s;
        }
        .btn-externo:hover { background: #005a34; }
        .label-rol { font-size: 11px; color: #666; margin-right: 5px; align-self: center;}
    </style>
</head>
<body>

    <div class="user-info">
        <img src="<?php echo $imgPerfil; ?>" alt="Perfil">
        <span><?php echo $nombre; ?></span>
    </div>

    <div class="roles-nav">
        <span class="label-rol">Cambiar a:</span>
        <?php foreach ($externos as $ext): ?>
            <button class="btn-externo" onclick="cambiarRol('<?php echo $ext; ?>')">
                <?php echo $ext; ?>
            </button>
        <?php <?php endforeach; ?>
    </div>

    <script>
    function cambiarRol(nuevoRol) {
        // Obtenemos el jsessionid actual de la URL del header
        const urlParams = new URLSearchParams(window.location.search);
        const sid = urlParams.get('jsessionid');
        
        // REGLA DE ORO: Para cambiar TODOS los iframes, redirigimos la ventana PADRE (top)
        // Volvemos al CEC.php o al selector con el nuevo módulo
        if (sid) {
            window.top.location.href = "/m/acceso/CEC.php?modulo=" + nuevoRol + "&jsessionid=" + sid;
        }
    }
    </script>

</body>
</html>
