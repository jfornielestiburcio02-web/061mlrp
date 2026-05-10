<?php
// Desactivar en producción, útil ahora para ver si algo falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) {
    die("Error: Sesión no recibida");
}

// 2. VALIDACIÓN Y OBTENCIÓN DE DATOS (Misma lógica que el Sidebar)
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

if (!$userData) {
    die("Error: Usuario no encontrado");
}

// 3. EXTRACCIÓN DE DATOS ESPECÍFICOS
$nombre = $userData['nombrePersona']['stringValue'] ?? 'No Identificado';
$imgPerfil = $userData['imagenPerfil']['stringValue'] ?? 'https://via.placeholder.com/50';

// Extraer array "externo"
$externos = [];
if (isset($userData['externo']['arrayValue']['values'])) {
    foreach ($userData['externo']['arrayValue']['values'] as $v) {
        if (isset($v['stringValue'])) {
            $externos[] = $v['stringValue'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0 20px;
            height: 60px;
            background-color: #ffffff;
            border-bottom: 2px solid #007d48;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Verdana', sans-serif;
        }

        /* Lado Izquierdo: Perfil */
        .perfil-box {
            display: flex;
            align-items: center;
        }
        .perfil-box img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007d48;
            margin-right: 12px;
        }
        .perfil-box .nombre {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        /* Lado Derecho: Roles */
        .roles-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .roles-box span {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .btn-rol {
            background-color: #007d48;
            color: white;
            border: none;
            padding: 7px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-rol:hover {
            background-color: #005a34;
        }
    </style>
</head>
<body>

    <div class="perfil-box">
        <img src="<?php echo $imgPerfil; ?>" alt="Foto">
        <div class="nombre"><?php echo $nombre; ?> (Personal 061) </div>
    </div>

    <div class="roles-box">
        <span>Cambiar a:</span>
        <?php foreach ($externos as $rol): ?>
            <button class="btn-rol" onclick="recargarTodo('<?php echo $rol; ?>')">
                <?php echo $rol; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <script>
    function recargarTodo(nuevoModulo) {
        // Obtenemos el jsessionid actual de PHP
        const sid = "<?php echo $jsid; ?>";
        
        // IMPORTANTE: window.top recarga la página completa que contiene los iframes
        // Esto actualiza el Sidebar, el Header y el Contenido al nuevo rol
        if (confirm('¿Desea cambiar al módulo ' + nuevoModulo + '?')) {
            window.top.location.href = "/m/acceso/CEC.php?jsessionid=" + sid + &nuevoModulo;
        }
    }
    </script>

</body>
</html>
