<?php
$host = "localhost";
$user = "root";
$password = ""; // Por defecto en XAMPP viene vacío
$database = "school_manager";

$conn = new mysqli($host, $user, $password, $database);

// Verificar si hay errores de conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar caracteres a utf8mb4 para tildes y ñ
$conn->set_charset("utf8mb4");
?>