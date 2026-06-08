<?php
// CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$collection = "inventario";
$baseUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/$collection";

// CAPTURAR JSESSIONID DE LA URL
$jsessionidActual = isset($_GET['jsessionid']) ? $_GET['jsessionid'] : '';

// LÓGICA DE ELIMINACIÓN
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // IMPORTANTE: Aquí podrías añadir una validación extra antes de eliminar
    $ch = curl_init("$baseUrl/$id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_exec($ch);
    curl_close($ch);
    header("Location: " . $_SERVER['PHP_SELF'] . "?jsessionid=$jsessionidActual");
    exit;
}

// LÓGICA DE CREACIÓN
$mensaje = "";
$claseMensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    
    // Validación: Rol 061 y el session ID que viene en la URL
    if ($_POST['rol'] === "061" && !empty($jsessionidActual)) {
        $data = [
            'fields' => [
                'nombre' => ['stringValue' => $_POST['nombre']],
                'unidades' => ['integerValue' => (int)$_POST['unidades']],
                'medicable' => ['booleanValue' => isset($_POST['medicable'])],
                'precio' => ['doubleValue' => (float)$_POST['precio']],
                'sesion_origen' => ['stringValue' => $jsessionidActual]
            ]
        ];
        
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        $mensaje = "Elemento registrado correctamente.";
        $claseMensaje = "exito";
    } else {
        $mensaje = "Error: Acceso denegado. Se requiere rol 061 y un JSESSIONID válido en la URL.";
        $claseMensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .error { color: #d63031; background: #ffecec; padding: 10px; margin-bottom: 10px; }
        .exito { color: #27ae60; background: #e8f8f0; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestor de Inventario</h1>
    <?php if ($mensaje): ?><div class="<?php echo $claseMensaje; ?>"><?php echo $mensaje; ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="rol" value="061">
        Nombre: <input type="text" name="nombre" required>
        Unidades: <input type="number" name="unidades" required>
        Precio: <input type="number" step="0.01" name="precio" required>
        <label><input type="checkbox" name="medicable" style="width:auto;"> ¿Es Medicable?</label>
        <button type="submit" name="guardar">Registrar</button>
    </form>

    <table>
        <tr><th>Nombre</th><th>Precio</th><th>Acción</th></tr>
        <?php
        $res = file_get_contents($baseUrl);
        $json = json_decode($res, true);
        if (isset($json['documents'])) {
            foreach ($json['documents'] as $doc) {
                $id = basename($doc['name']);
                $f = $doc['fields'];
                echo "<tr>
                    <td>{$f['nombre']['stringValue']}</td>
                    <td>{$f['precio']['doubleValue']}</td>
                    <td><a href='?delete=$id&jsessionid=$jsessionidActual'>Eliminar</a></td>
                </tr>";
            }
        }
        ?>
    </table>
</div>
</body>
</html>
