<?php
// 1. CONFIGURACIÓN Y SEGURIDAD
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";

$jsid = $_GET['jsessionid'] ?? '';
$mod = $_GET['modulo'] ?? '';

if (!$jsid) die("Sesión no válida");

// 2. VALIDACIÓN DE ROL 061
$url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios?key=$apiKey";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$json = json_decode($res, true);
curl_close($ch);

$autorizado = false;
if (isset($json['documents'])) {
    foreach ($json['documents'] as $doc) {
        $f = $doc['fields'];
        if (isset($f['jsessionid']['stringValue']) && $f['jsessionid']['stringValue'] === $jsid) {
            // Verificar si "061" está en sus roles
            if (isset($f['externo']['arrayValue']['values'])) {
                foreach ($f['roles']['arrayValue']['values'] as $r) {
                    if ($r['stringValue'] === "TES") { $autorizado = true; break; }
                }
            }
        }
    }
}

if (!$autorizado) {
    die("Acceso Denegado: Este contenido es exclusivo para el rol 061.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { background-color: #fcfcfc; font-family: Arial, sans-serif; padding: 20px; color: #333; }
        
        .container { display: flex; gap: 25px; flex-wrap: wrap; max-width: 1000px; }

        /* Estilo de las cajas (Sombras blancas/grises de la imagen) */
        .card { 
            background: white; border-radius: 4px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            border: 1px solid #ddd; flex: 1; min-width: 300px;
            overflow: hidden;
        }

        .card-header { 
            padding: 8px 15px; font-weight: bold; font-size: 13px; 
            color: white; text-transform: uppercase;
        }

        .escritorio .card-header { background-color: #f3b18c; } /* Color Naranja */
        .noticias .card-header { background-color: #b1a0c7; }   /* Color Morado */
        .agenda .card-header { background-color: #a9d18e; }     /* Color Verde */

        .card-content { padding: 0; }
        .row { 
            display: flex; align-items: center; padding: 10px 15px; 
            border-bottom: 1px solid #eee; font-size: 13px; 
        }
        .row:last-child { border-bottom: none; }
        .row img { width: 20px; margin-right: 12px; }

        /* Estilo Agenda / Calendario */
        .calendar-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 12px; }
        .calendar-table th { background: #e2efda; padding: 5px; color: #555; }
        .calendar-table td { padding: 8px; border: 1px solid #f0f0f0; }
        .today { border: 2px solid #ed7d31 !important; font-weight: bold; color: #ed7d31; }
        .marked { background-color: #b1a0c7; color: white; border-radius: 2px; }
        
        .agenda-footer { display: flex; justify-content: space-between; align-items: flex-end; padding: 10px; }
        .hopscotch-img { width: 180px; opacity: 0.8; }
    </style>
</head>
<body>

<div class="container">
    <div class="card escritorio">
        <div class="card-header">Escritorio</div>
        <div class="card-content">
            <div class="row">
                <img src="https://rayuela.educarex.es/imagenes/iconos/mensajeria.png">
                <span>Tiene 0 mensajes pendientes</span>
            </div>
            <div class="row">
                <img src="https://rayuela.educarex.es/imagenes/iconos/cita.png">
                <span>No tiene ninguna cita hoy</span>
            </div>
            <div class="row">
                <img src="https://rayuela.educarex.es/imagenes/iconos/conexion.png">
                <span>Última conexión: <?php echo date('d-m-Y'); ?>, a las <?php echo date('H:i'); ?></span>
            </div>
        </div>
    </div>

    <div class="card noticias">
        <div class="card-header">Noticias</div>
        <div class="card-content" style="height: 100px;"></div>
    </div>
</div>

<br>

<div class="card agenda" style="max-width: 450px;">
    <div class="card-header">Agenda</div>
    <div class="card-content">
        <div style="text-align: center; padding: 5px; font-weight: bold; color: #666;">Mayo</div>
        <table class="calendar-table">
            <tr><th>LUN</th><th>MAR</th><th>MIE</th><th>JUE</th><th>VIE</th><th>SAB</th><th>DOM</th></tr>
            <tr><td></td><td></td><td></td><td></td><td>1</td><td>2</td><td>3</td></tr>
            <tr><td>4</td><td class="marked">5</td><td>6</td><td class="marked">7</td><td class="marked">8</td><td>9</td><td class="today">10</td></tr>
            <tr><td>11</td><td>12</td><td class="marked">13</td><td>14</td><td>15</td><td>16</td><td>17</td></tr>
            <tr><td>18</td><td>19</td><td>20</td><td>21</td><td>22</td><td>23</td><td>24</td></tr>
            <tr><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td></tr>
        </table>
        <div class="agenda-footer">
            <div></div>
            <img class="hopscotch-img" src="https://rayuela.educarex.es/imagenes/v3/rayuela_dibujo.png" onerror="this.style.display='none'">
        </div>
    </div>
</div>

</body>
</html>
