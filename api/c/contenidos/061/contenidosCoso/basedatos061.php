<?php
// 1. CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f";
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w";
$jsid = $_GET['jsessionid'] ?? '';

if (!$jsid) die("Sesión no válida");

// (Opcional) Validación de rol 061 aquí si lo deseas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Base de Datos de Pacientes - SSM</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; display: flex; gap: 20px; justify-content: center; }
        
        /* Contenedor Izquierdo: Buscador */
        .search-container { background: white; width: 400px; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0
