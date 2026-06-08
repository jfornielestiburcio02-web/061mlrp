<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$basePath = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents";

$jsessionid = $_GET['jsessionid'] ?? '';
$accesoPermitido = false;

// 1. VALIDACIÓN
if (!empty($jsessionid)) {
    $query = ['structuredQuery' => ['from' => [['collectionId' => 'usuarios']], 'where' => ['fieldFilter' => ['field' => ['fieldPath' => 'jsessionid'], 'op' => 'EQUAL', 'value' => ['stringValue' => $jsessionid]]]]];
    $ch = curl_init("$basePath:runQuery");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res, true);
    curl_close($ch);

    if (isset($data[0]['document']['fields']['roles']['arrayValue']['values'])) {
        foreach ($data[0]['document']['fields']['roles']['arrayValue']['values'] as $item) {
            if (isset($item['stringValue']) && $item['stringValue'] === '061') {
                $accesoPermitido = true;
                break;
            }
        }
    }
}

if (!$accesoPermitido) { die("Acceso denegado: Rol 061 no validado."); }

// 2. ACCIONES (Procesar antes de enviar NADA al navegador)
if (isset($_GET['delete'])) {
    $ch = curl_init("$basePath/inventario/" . $_GET['delete']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_exec($ch);
    curl_close($ch);
    header("Location: ?jsessionid=$jsessionid");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $data = ['fields' => [
        'nombre' => ['stringValue' => $_POST['nombre']],
        'unidades' => ['integerValue' => (int)$_POST['unidades']],
        'medicable' => ['booleanValue' => isset($_POST['medicable'])],
        'precio' => ['doubleValue' => (float)$_POST['precio']]
    ]];
    $ch = curl_init("$basePath/inventario");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    header("Location: ?jsessionid=$jsessionid");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>
<div class="container">
    <h1>Inventario</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="number" name="unidades" placeholder="Unidades" required>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required>
        <label><input type="checkbox" name="medicable"> ¿Es Medicable?</label>
        <button type="submit">Guardar</button>
    </form>
    <table>
        <tr><th>Nombre</th><th>Precio</th><th>Acción</th></tr>
        <?php
        $res = file_get_contents("$basePath/inventario");
        $json = json_decode($res, true);
        if (isset($json['documents'])) {
            foreach ($json['documents'] as $doc) {
                $id = basename($doc['name']);
                $f = $doc['fields'];
                // Nota: Verificamos si existe el campo antes de imprimir para evitar errores
                $nombre = $f['nombre']['stringValue'] ?? 'N/A';
                $precio = $f['precio']['doubleValue'] ?? 0;
                echo "<tr><td>$nombre</td><td>$precio</td><td><a href='?delete=$id&jsessionid=$jsessionid'>Eliminar</a></td></tr>";
            }
        }
        ?>
    </table>
</div>
</body>
</html>
