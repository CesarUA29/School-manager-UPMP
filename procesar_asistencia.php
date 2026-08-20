<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard_maestro.php");
    exit();
}

$asistencias    = $_POST['asistencia'] ?? [];
$fecha_registro = $_POST['fecha_asistencia'] ?? date('Y-m-d');
$grupo_filtro   = $_POST['grupo_filtro'] ?? 'todos';

if (!empty($asistencias)) {
    // Insertar o actualizar la asistencia por alumno y fecha
    $query = "INSERT INTO asistencias (matricula_alumno, estatus, fecha) 
              VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE estatus = VALUES(estatus)";
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        foreach ($asistencias as $matricula => $estatus) {
            $stmt->bind_param("sss", $matricula, $estatus, $fecha_registro);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Redireccionar conservando los filtros activos de fecha y grupo
header("Location: dashboard_maestro.php?seccion=asistencia&fecha=" . urlencode($fecha_registro) . "&grupo=" . urlencode($grupo_filtro) . "&mensaje=asistencias_guardadas");
exit();
?>