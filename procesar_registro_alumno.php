<?php
session_start();
require_once 'conexion.php';

// Validar que sea administrador
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recibir y limpiar los datos del formulario de registro del alumno
    $matricula          = trim($_POST['matricula'] ?? '');
    $nombre             = trim($_POST['nombre'] ?? '');
    $apellido_paterno   = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno   = trim($_POST['apellido_materno'] ?? '');
    $correo             = trim($_POST['correo_electronico'] ?? '');
    $id_carrera         = intval($_POST['id_carrera'] ?? 0);
    $grado              = intval($_POST['grado'] ?? 0);
    $grupo              = trim($_POST['grupo'] ?? '');
    $password           = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT); // Contraseña por defecto si no se pone

    if (empty($matricula) || empty($nombre) || empty($correo)) {
        header("Location: gestion_alumnos.php?error=campos_vacios");
        exit();
    }

    // 2. Insertar al alumno en la tabla 'alumnos'
    $sql_alumno = "INSERT INTO alumnos (matricula, nombre, apellido_paterno, apellido_materno, correo_electronico, id_carrera, grado, grupo) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_alumno);
    $stmt->bind_param("sssssiis", $matricula, $nombre, $apellido_paterno, $apellido_materno, $correo, $id_carrera, $grado, $grupo);

    if ($stmt->execute()) {
        $stmt->close();

        // 3. ¡LA MAGIA AUTOMÁTICA! 
        // Buscar todas las clases que coincidan con el grupo del alumno (ej. 'B' o '3° B')
        // Esto buscará en tu tabla clases de phpMyAdmin las materias correspondientes
        $sql_clases = "SELECT id_clase FROM clases WHERE grupo = ?";
        $stmt_clases = $conn->prepare($sql_clases);
        $stmt_clases->bind_param("s", $grupo);
        $stmt_clases->execute();
        $resultado_clases = $stmt_clases->get_result();

        // 4. Inscribir automáticamente al alumno a cada clase encontrada
        if ($resultado_clases->num_rows > 0) {
            $sql_insert_inscripcion = "INSERT IGNORE INTO inscripciones (matricula, id_clase, estatus_materia, calificacion) VALUES (?, ?, 'Cursando', 0)";
            $stmt_inscripcion = $conn->prepare($sql_insert_inscripcion);

            while ($clase = $resultado_clases->fetch_assoc()) {
                $id_clase = $clase['id_clase'];
                $stmt_inscripcion->bind_param("si", $matricula, $id_clase);
                $stmt_inscripcion->execute();
            }
            $stmt_inscripcion->close();
        }
        $stmt_clases->close();

        // Redirigir con éxito
        header("Location: gestion_alumnos.php?mensaje=alumno_registrado_con_materias");
        exit();

    } else {
        $error = $conn->error;
        $stmt->close();
        header("Location: gestion_alumnos.php?error=sql_error&detalles=" . urlencode($error));
        exit();
    }
}
?>