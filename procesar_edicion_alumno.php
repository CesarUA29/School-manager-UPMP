<?php
session_start();
require_once 'conexion.php';

// Validar sesión
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
if (!$usuario_id) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario'] ?? $_POST['id'] ?? 0);
    $matricula  = trim($_POST['matricula'] ?? '');
    $nombre     = trim($_POST['nombre'] ?? '');
    $correo     = trim($_POST['correo'] ?? '');
    $carrera    = trim($_POST['carrera'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($id_usuario > 0) {
        if (!empty($password)) {
            // Si le asignaste/cambiaste la contraseña, la encriptamos con BCRYPT para el login
            $pass_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, correo = ?, matricula = ?, carrera = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $nombre, $correo, $matricula, $carrera, $pass_hash, $id_usuario);
        } else {
            // Guardar sin modificar la contraseña existente
            $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, correo = ?, matricula = ?, carrera = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $correo, $matricula, $carrera, $id_usuario);
        }

        if ($stmt->execute()) {
            $stmt->close();

            // Si también mantienes la tabla 'alumnos', actualiza los datos allí
            $stmt_al = $conn->prepare("UPDATE alumnos SET matricula = ?, nombre = ?, correo = ?, carrera = ? WHERE id_usuario = ? OR id = ?");
            if ($stmt_al) {
                $stmt_al->bind_param("ssssii", $matricula, $nombre, $correo, $carrera, $id_usuario, $id_usuario);
                $stmt_al->execute();
                $stmt_al->close();
            }

            header("Location: gestion_alumnos.php?mensaje=actualizado");
            exit();
        } else {
            echo "Error al actualizar: " . $conn->error;
        }
    }
}

header("Location: gestion_alumnos.php");
exit();