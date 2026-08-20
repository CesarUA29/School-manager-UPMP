<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibimos los datos del formulario de asignación
    $id_clase    = intval($_POST['id_clase']); // Asegúrate de tener este campo en tu form
    $id_materia  = intval($_POST['id_materia']);
    $id_docente  = intval($_POST['id_docente']);
    $aula        = trim($_POST['aula']);
    $dias        = trim($_POST['dias']);
    $hora_inicio = trim($_POST['hora_inicio']);
    $hora_fin    = trim($_POST['hora_fin']);

    // Actualizamos la tabla clases
    $sql = "UPDATE clases SET id_materia = ?, id_maestro = ?, aula = ?, dias = ?, hora_inicio = ?, hora_fin = ? WHERE id_clase = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssi", $id_materia, $id_docente, $aula, $dias, $hora_inicio, $hora_fin, $id_clase);
    $stmt->execute();
    $stmt->close();
}

header("Location: dashboard_admin.php?mensaje=asignacion_exitosa");
exit();
?>