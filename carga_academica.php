<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

$matricula = $_SESSION['matricula'];
$sql = "SELECT m.nombre_materia, cl.dias, cl.hora_inicio, cl.hora_fin, i.calificacion 
        FROM inscripciones i 
        JOIN clases cl ON i.id_clase = cl.id_clase 
        JOIN materias m ON cl.id_materia = m.id_materia 
        WHERE i.matricula = '$matricula'";
$materias = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga Académica - Portal Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { background-color: #002855; min-height: 100vh; color: white; }
        .nav-link { color: rgba(255,255,255,0.8); }
        .nav-link:hover, .nav-link.active { color: white; background-color: rgba(255,255,255,0.1); border-radius: 5px; }
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Menú Lateral -->
    <nav class="sidebar p-3" style="width: 250px;">
        <h4 class="text-center py-3">Portal Alumno</h4>
        <ul class="nav flex-column gap-2">
            <li class="nav-item"><a class="nav-link" href="dashboard_alumno.php"><i class="fa-solid fa-house me-2"></i> Inicio</a></li>
            <li class="nav-item"><a class="nav-link active" href="carga_academica.php"><i class="fa-solid fa-book me-2"></i> Carga Académica</a></li>
            <li class="nav-item"><a class="nav-link" href="finanzas_alumno.php"><i class="fa-solid fa-wallet me-2"></i> Finanzas</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido Principal -->
    <main class="p-4 w-100">
        <h2>Mi Carga Académica</h2>
        <div class="card shadow-sm border-0 p-4 mt-3 bg-white">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Horario</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($materias && $materias->num_rows > 0): ?>
                        <?php while($m = $materias->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($m['nombre_materia']); ?></td>
                            <td><?php echo htmlspecialchars($m['dias'] . ' ' . substr($m['hora_inicio'], 0, 5) . ' - ' . substr($m['hora_fin'], 0, 5)); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $m['calificacion'] !== null ? htmlspecialchars($m['calificacion']) : 'N/A'; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No tienes materias inscritas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>