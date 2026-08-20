<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$matricula = trim($_GET['matricula'] ?? '');

if (empty($matricula)) {
    header("Location: gestion_alumnos.php?status=error");
    exit();
}

$conn->begin_transaction();

try {
    // 1. Borrar de la tabla usuarios (si está vinculada por matrícula)
    $stmt_user = $conn->prepare("DELETE FROM usuarios WHERE matricula = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("s", $matricula);
        $stmt_user->execute();
        $stmt_user->close();
    }

    // 2. Borrar de la tabla principal de alumnos
    $stmt_alum = $conn->prepare("DELETE FROM alumnos WHERE matricula = ?");
    if ($stmt_alum) {
        $stmt_alum->bind_param("s", $matricula);
        $stmt_alum->execute();
        $stmt_alum->close();
    } else {
        throw new Exception("Error al preparar la consulta de eliminación en alumnos.");
    }

    $conn->commit();
    
    // Éxito: Redirigir a la gestión de alumnos con el mensaje verde
    header("Location: gestion_alumnos.php?status=eliminado");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Si hay un error, lo mostramos claramente en pantalla para salir de dudas
    echo "<div style='font-family: Arial; padding: 30px;'>";
    echo "<h2 style='color: red;'>Error crítico al intentar eliminar:</h2>";
    echo "<p style='background: #f8d7da; color: #842029; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<br><a href='gestion_alumnos.php' style='padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>Regresar</a>";
    echo "</div>";
    exit();
}
?>