<?php
session_start();
require_once 'conexion.php';

// Validar que sea administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = trim($_POST['matricula']);
    $id_clase = trim($_POST['id_clase']);
    $estatus_materia = 'Cursando'; // Estatus por defecto al inscribir

    // Consulta para insertar la inscripción
    $sql = "INSERT INTO inscripciones (matricula, id_clase, estatus_materia) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sis", $matricula, $id_clase, $estatus_materia);
        if ($stmt->execute()) {
            header("Location: dashboard_admin.php?status=inscripcion_exitosa");
            exit();
        } else {
            header("Location: dashboard_admin.php?status=error_guardado");
            exit();
        }
    } else {
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }
}
?>