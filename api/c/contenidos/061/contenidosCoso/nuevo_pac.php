<?php
// 1. TODA LA LÓGICA DE POST DEBE IR AL PRINCIPIO SIN ESPACIOS ANTES
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/pacientes?key=$apiKey";
    
    $body = ["fields" => [
        "nombre" => ["stringValue" => $_POST['nombre']],
        "dni" => ["stringValue" => $_POST['dni']],
        "nacimiento" => ["stringValue" => $_POST['nacimiento']],
        "sangre" => ["stringValue" => $_POST['sangre']],
        "seguro" => ["stringValue" => $_POST['seguro']],
        "alergias" => ["stringValue" => $_POST['alergias']],
        "condiciones" => ["stringValue" => $_POST['condiciones']],
        "centro" => ["stringValue" => $_POST['centro']],
        "emision" => ["stringValue" => date('d/m/Y')]
    ]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    // Redirigir inmediatamente
    header("Location: pacientes.php?jsessionid=$jsid&modulo=$mod");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; display: flex; justify-content: center; }
        .form-card { background: white; width: 600px; padding: 40px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 10px solid #007d48; }
        .banner { color: #007d48; font-weight: bold; border-bottom: 2px solid #007d48; margin-bottom: 20px; font-size: 14px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 3px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; font-size: 14px; }
        .full { grid-column: span 2; }
        .btn-submit { background: #007d48; color: white; border: none; padding: 15px; width: 100%; font-weight: bold; cursor: pointer; margin-top: 20px; font-size: 16px; }
        .btn-submit:hover { background: #005a34; }
        .btn-volver { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

<div class="form-card">
    <div class="banner">━━━━━━━━━━━━━━━━━━━━━━ SERVICIO DE SALUD DE MÁLAGA</div>
    <form method="POST" action="nuevo_pac.php?jsessionid=<?php echo $jsid; ?>&modulo=<?php echo $mod; ?>">
        <div class="grid">
            <div class="field full">
                <label>Nombre completo</label>
                <input type="text" name="nombre" required placeholder="Nombre y Apellidos">
            </div>
            <div class="field">
                <label>DNI del ciudadano</label>
                <input type="text" name="dni" required placeholder="12345678X">
            </div>
            <div class="field">
                <label>Fecha de nacimiento</label>
                <input type="date" name="nacimiento" required>
            </div>
            <div class="field">
                <label>Grupo sanguíneo</label>
                <select name="sangre">
                    <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                    <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                </select>
            </div>
            <div class="field">
                <label>Seguro Médico</label>
                <select name="seguro">
                    <option>Básico</option><option>Baremado</option><option>Privado</option>
                </select>
            </div>
            <div class="field full">
                <label>Alergias conocidas</label>
                <textarea name="alergias" rows="2"></textarea>
            </div>
            <div class="field full">
                <label>Condiciones médicas</label>
                <textarea name="condiciones" rows="2"></textarea>
            </div>
            <div class="field full">
                <label>Centro médico asignado</label>
                <select name="centro">
                    <option>Hospital Regional Universitario de Málaga</option>
                    <option>Hospital Universitario Virgen de la Victoria</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-submit">GUARDAR EN BASE DE DATOS</button>
        <a href="basedatos061.php?jsessionid=<?php echo $jsid; ?>&modulo=<?php echo $mod; ?>" class="btn-volver">Cancelar y volver</a>
    </form>
</div>

</body>
</html>
