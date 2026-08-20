<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard_maestro.php");
    exit();
}

$id_materia     = $_POST['id_materia'] ?? null;
$parcial        = $_POST['parcial'] ?? 1;
$grupo_filtro   = $_POST['grupo_filtro'] ?? 'todos';
$calificaciones = $_POST['calificaciones'] ?? [];

if ($id_materia && !empty($calificaciones)) {
    // Inserta o actualiza la calificación según la materia, el alumno y el parcial
    $query = "INSERT INTO calificaciones (id_materia, matricula_alumno, parcial, calificacion) 
              VALUES (?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion)";
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        foreach ($calificaciones as $matricula => $nota) {
            // Guardar únicamente si el campo tiene valor numérico
            if ($nota !== '' && is_numeric($nota)) {
                $nota_float = (float) $nota;
                $stmt->bind_param("isid", $id_materia, $matricula, $parcial, $nota_float);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
}

// Redireccionar manteniendo activos los filtros seleccionados
header("Location: dashboard_maestro.php?seccion=calificaciones&id_materia=" . urlencode($id_materia) . "&parcial=" . urlencode($parcial) . "&grupo=" . urlencode($grupo_filtro) . "&mensaje=calificaciones_guardadas");
exit();
?>