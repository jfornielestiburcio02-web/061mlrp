<?php
// 1. CONFIGURACIÓN DE FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) {
    die("Error: Sesión no válida.");
}

// 2. BUSCAR EL USUARIO POR JSESSIONID Y VALIDAR "TES"
$url_usuarios = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";

$ch = curl_init($url_usuarios);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$data = json_decode($res, true);
curl_close($ch);

$esTES = false;
$nombreUsuario = "";

if (isset($data['documents'])) {
    foreach ($data['documents'] as $doc) {
        $fields = $doc['fields'];
        
        // Verificamos si este documento pertenece al usuario actual por su jsessionid
        if (isset($fields['jsessionid']['stringValue']) && $fields['jsessionid']['stringValue'] === $jsid) {
            $nombreUsuario = $fields['nombrePersona']['stringValue'] ?? 'Usuario';
            
            // VALIDACIÓN CRÍTICA: Mirar en el array 'externo' por el valor 'TES'
            if (isset($fields['externo']['arrayValue']['values'])) {
                foreach ($fields['externo']['arrayValue']['values'] as $valorExterno) {
                    if ($valorExterno['stringValue'] === "TES") {
                        $esTES = true;
                        break;
                    }
                }
            }
            break; 
        }
    }
}

// 3. BLOQUEO SI NO ES TES
if (!$esTES) {
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h2 style='color:#d93025;'>ACCESO RESTRINGIDO</h2>
            <p>Lo sentimos, esta sección es exclusiva para personal con rango <b>TES</b>.</p>
            <a href='javascript:history.back()'>Volver atrás</a>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gama Ambulancias - SSM</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .header { background: #007d48; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .grid-ambus { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .ambu-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-bottom: 5px solid #ffcc00; }
        .ambu-img { width: 100%; height: 180px; background: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #999; }
        .ambu-info { padding: 20px; }
        .tag { background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1 style="margin:0;"> Gama de Ambulancias</h1>
        <small>Bienvenido, <?php echo $nombreUsuario; ?> (Personal TES autorizado)</small>
    </div>
    <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 50px;">
        ESTADO: OPERATIVO
    </div>
</div>

<div class="grid-ambus">
    <div class="ambu-card">
        <div class="ambu-img">IMAGEN AMBU TIPO C</div>
        <div class="ambu-info">
            <span class="tag">SVA - SOPORTE VITAL AVANZADO</span>
            <h3 style="margin: 10px 0;">Ambulancia Tipo C (UVI Móvil)</h3>
            <p style="color: #666; font-size: 14px;">Equipada para asistencia médica intensiva. Incluye monitor desfibrilador, respirador y medicación de emergencia.</p>
        </div>
    </div>

    <div class="ambu-card">
        <div class="ambu-img">IMAGEN AMBU TIPO B</div>
        <div class="ambu-info">
            <span class="tag">SVB - SOPORTE VITAL BÁSICO</span>
            <h3 style="margin: 10px 0;">Ambulancia Tipo B (TES)</h3>
            <p style="color: #666; font-size: 14px;">Destinada al transporte y soporte vital básico del paciente por personal TES cualificado.</p>
        </div>
    </div>

    <div class="ambu-card">
        <div class="ambu-img">IMAGEN VIR</div>
        <div class="ambu-info">
            <span class="tag">VIR - INTERVENCIÓN RÁPIDA</span>
            <h3 style="margin: 10px 0;">V.I.R. (SUV 4x4)</h3>
            <p style="color: #666; font-size: 14px;">Vehículo ligero para llegada rápida de personal facultativo a entornos urbanos o difícil acceso.</p>
        </div>
    </div>
</div>

</body>
</html>
