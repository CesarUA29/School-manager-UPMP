<?php
session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar y limpiar variables ingresadas
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $apellido_paterno = $conn->real_escape_string(trim($_POST['apellido_paterno']));
    $apellido_materno = $conn->real_escape_string(trim($_POST['apellido_materno']));
    $correo = $conn->real_escape_string(trim($_POST['correo']));

    $usuario = $conn->real_escape_string(trim($_POST['usuario']));
    $password = $_POST['password'];

    // 1. Validar que no existan duplicados (Usuario o Correo)
    $check_user = "SELECT id FROM usuarios WHERE usuario = '$usuario' OR correo = '$correo'";
    $res_user = $conn->query($check_user);

    if ($res_user && $res_user->num_rows > 0) {
        header("Location: dashboard_admin.php?status=error_usuario_existe");
        exit();
    }

    // Usar transacción para guardar en maestros y usuarios de forma atómica
    $conn->begin_transaction();

    try {
        // 2. Insertar docente en la tabla maestros
        $sql_maestro = "INSERT INTO maestros (nombre, apellido_paterno, apellido_materno, correo) 
                        VALUES ('$nombre', '$apellido_paterno', '$apellido_materno', '$correo')";
        $conn->query($sql_maestro);

        // 3. Crear usuario de acceso con rol_id = 3 (Maestro)
        $sql_usuario = "INSERT INTO usuarios (usuario, correo, password, rol_id, matricula) 
                        VALUES ('$usuario', '$correo', '$password', 3, NULL)";
        $conn->query($sql_usuario);

        $conn->commit();
        header("Location: dashboard_admin.php?status=docente_exitoso");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }
} else {
    header("Location: dashboard_admin.php");
    exit();
}