<?php
session_start();
require_once 'conexion.php';

// Validar que sea administrador
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// Procesar cuando se envíe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula']);
    $id_clase  = intval($_POST['id_clase']);

    if (!empty($matricula) && $id_clase > 0) {
        // Insertar en la tabla inscripciones
        $sql = "INSERT INTO inscripciones (matricula, id_clase, estatus_materia) VALUES (?, ?, 'Cursando')";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $matricula, $id_clase);
            if ($stmt->execute()) {
                $mensaje = "<div class='alert alert-success'>¡Materia asignada correctamente al alumno! Ya aparecerá en su portal.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al inscribir (Quizá ya está inscrito a esta clase): " . $conn->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $mensaje = "<div class='alert alert-warning'>Por favor completa todos los campos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Materia a Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow border-0 rounded-3 p-4">
            <h3 class="mb-3 text-primary fw-bold"><i class="fa-solid fa-book-medical me-2"></i> Asignar Clase a Alumno</h3>
            
            <?php echo $mensaje; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Matrícula del Alumno:</label>
                    <input type="text" name="matricula" class="form-control" placeholder="Ej. 2023110008" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">ID de la Clase:</label>
                    <input type="number" name="id_clase" class="form-control" placeholder="Ej. 1 o 2 (según tu tabla clases)" required>
                    <div class="form-text">Puedes consultar los IDs disponibles en tu tabla 'clases' de la base de datos.</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard_admin.php" class="btn btn-secondary">Volver al Panel</a>
                    <button type="submit" class="btn btn-primary">Asignar Materia</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>