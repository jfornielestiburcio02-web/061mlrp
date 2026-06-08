<?php
$projectId = "yr92q8h4y5972h4y952qhy3f";
$basePath = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents";

// 1. VALIDACIÓN ESTRICTA DE SESIÓN
$jsessionid = $_GET['jsessionid'] ?? '';
$esValido = false;

if (!empty($jsessionid)) {
    // Consulta para buscar al usuario por su jsessionid
    $query = [
        'structuredQuery' => [
            'from' => [['collectionId' => 'usuarios']],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'jsessionid'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $jsessionid]
                ]
            ]
        ]
    ];

    $ch = curl_init("$basePath:runQuery");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res, true);
    curl_close($ch);

    // Verificar si el documento existe y si '061' está en el array 'roles'
    if (!empty($data[0]['document']['fields']['roles']['arrayValue']['values'])) {
        foreach ($data[0]['document']['fields']['roles']['arrayValue']['values'] as $r) {
            if ($r['stringValue'] === '061') {
                $esValido = true;
                break;
            }
        }
    }
}

if (!$esValido) {
    die("<h1 style='color:red; font-family:sans-serif;'>Acceso Denegado: JSESSIONID inválido o sin rol 061.</h1>");
}

// 2. LÓGICA DE ACCIONES (SOLO LLEGA AQUÍ SI ES VÁLIDO)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $ch = curl_init("$basePath/inventario/$id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_exec($ch);
    curl_close($ch);
    header("Location: ?jsessionid=$jsessionid");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $data = [
        'fields' => [
            'nombre' => ['stringValue' => $_POST['nombre']],
            'unidades' => ['integerValue' => (int)$_POST['unidades']],
            'medicable' => ['booleanValue' => isset($_POST['medicable'])],
            'precio' => ['doubleValue' => (float)$_POST['precio']]
        ]
    ];
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
    <h1>Gestión de Inventario</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre Objeto" required>
        <input type="number" name="unidades" placeholder="Unidades" required>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required>
        <label><input type="checkbox" name="medicable"> ¿Es Medicable?</label>
        <button type="submit">Registrar Objeto</button>
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
                echo "<tr>
                    <td>{$f['nombre']['stringValue']}</td>
                    <td>{$f['precio']['doubleValue']}</td>
                    <td><a href='?delete=$id&jsessionid=$jsessionid' style='color:red;'>Eliminar</a></td>
                </tr>";
            }
        }
        ?>
    </table>
</div>
</body>
</html>
