<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $password = $_POST['password']; // Contraseña escrita por el usuario

    // Buscamos al usuario por su nombre de usuario o correo, e incluimos el rol
    $sql = "SELECT u.id, u.usuario, u.password, u.rol_id, r.nombre_rol, u.matricula 
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id 
            WHERE u.usuario = '$usuario' OR u.correo = '$usuario' LIMIT 1";
            
    $resultado = $conn->query($sql);

    if ($resultado->num_rows == 1) {
        $row = $resultado->fetch_assoc();
        
        // Verificación de contraseña (asumiendo texto plano para pruebas rápidas. 
        // Si usas contraseñas seguras en BD cambia esto por: if (password_verify($password, $row['password']))
        if ($password === $row['password']) {
            
            // Creamos las variables de sesión del usuario
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['username'] = $row['usuario'];
            $_SESSION['rol'] = $row['nombre_rol'];
            $_SESSION['matricula'] = $row['matricula'];

            // Redirección dependiendo del rol que tiene en la base de datos
            switch (strtolower($row['nombre_rol'])) {
                case 'administrador':
                case 'admin':
                    header("Location: dashboard_admin.php");
                    break;
                case 'maestro':
                case 'docente':
                    header("Location: dashboard_maestro.php");
                    break;
                case 'alumno':
                case 'estudiante':
                    header("Location: dashboard_alumno.php");
                    break;
                default:
                    header("Location: login.php?error=rol_no_valido");
                    break;
            }
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
