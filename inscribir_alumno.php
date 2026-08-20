<?php
session_start();
require_once 'conexion.php';

// Validar que sea administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol'] ?? ''), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Matrícula del alumno al que le quieres asignar las materias
$matricula = '2023110008'; // Puedes cambiar esto por una variable POST si viene de un formulario

// 1. Buscar las clases que corresponden (por ejemplo, las del grupo '3° A' o todas las disponibles)
// Aquí puedes filtrar por el grado y grupo del alumno si lo deseas
$sql_clases = "SELECT id_clase FROM clases WHERE grupo = '3° A'";
$resultado_clases = $conn->query($sql_clases);

if ($resultado_clases && $resultado_clases->num_rows > 0) {
    $inscritas = 0;
    
    while ($clase = $resultado_clases->fetch_assoc()) {
        $id_clase = $clase['id_clase'];

        // 2. Verificar si ya está inscrito a esta clase para evitar duplicados
        $check = $conn->query("SELECT * FROM inscripciones WHERE matricula = '$matricula' AND id_clase = $id_clase");
        
        if ($check->num_rows == 0) {
            // 3. Insertar la inscripción
            $sql_insert = "INSERT INTO inscripciones (matricula, id_clase, estatus_materia) VALUES ('$matricula', $id_clase, 'Cursando')";
            if ($conn->query($sql_insert)) {
                $inscritas++;
            }
        }
    }
    
    echo "¡Proceso exitoso! Se inscribieron $inscritas materias a la matrícula $matricula.";
} else {
    echo "No se encontraron clases disponibles para asignar.";
}
?>