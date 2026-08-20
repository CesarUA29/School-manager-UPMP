<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

$matricula = $_SESSION['matricula'] ?? '';
$sql = "SELECT a.*, c.nombre_carrera FROM alumnos a JOIN carreras c ON a.id_carrera = c.id_carrera WHERE a.matricula = '$matricula'";
$res = $conn->query($sql);
$alumno = $res ? $res->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno - UPMP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: #0b1d3a; min-height: 100vh; color: white; width: 260px; flex-shrink: 0; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
        .sidebar h4 { font-weight: 700; letter-spacing: 0.5px; }
        .sidebar .nav-link { color: #cbd5e1; padding: 12px 20px; margin-bottom: 5px; border-radius: 8px; font-weight: 500; transition: all 0.2s ease; }
        .sidebar .nav-link:hover { color: #ffffff; background-color: rgba(255,255,255,0.08); }
        .sidebar .nav-link.active { color: #ffffff; background-color: #0d6efd; box-shadow: 0 4px 12px rgba(13,110,253,0.3); }
        .card-profile { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #ffffff; }
        .card-header-custom { background-color: transparent; border-bottom: 1px solid #edf2f7; padding: 20px 25px; }
        .info-label { font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-value { font-size: 1.05rem; color: #2d3748; font-weight: 600; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Menú Lateral -->
    <nav class="sidebar p-3">
        <div class="text-center py-4">
            <i class="fa-solid fa-graduation-cap fs-2 text-info mb-2"></i>
            <h4 class="fs-5 mb-0">Portal Alumno</h4>
            <small class="text-white-50">UPMP</small>
        </div>
        <hr class="border-secondary opacity-25 mx-2">
        <ul class="nav flex-column gap-1 mt-3">
            <li class="nav-item"><a class="nav-link active" href="dashboard_alumno.php"><i class="fa-solid fa-house me-2"></i> Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="carga_academica.php"><i class="fa-solid fa-book me-2"></i> Carga Académica</a></li>
            <li class="nav-item"><a class="nav-link" href="finanzas_alumno.php"><i class="fa-solid fa-wallet me-2"></i> Finanzas</a></li>
            <li class="nav-item mt-5"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido Principal -->
    <main class="flex-grow-1 p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">¡Hola, <?php echo htmlspecialchars($alumno['nombre'] ?? 'Estudiante'); ?>!</h2>
                <p class="text-muted mb-0">Bienvenido a tu panel general de control escolar.</p>
            </div>
            <span class="badge bg-primary px-3 py-2 rounded-pill fs-6 shadow-sm"><i class="fa-solid fa-id-card me-1"></i> <?php echo htmlspecialchars($matricula); ?></span>
        </div>

        <!-- Tarjeta de Información Estilizada -->
        <div class="card card-profile">
            <div class="card-header-custom d-flex align-items-center">
                <i class="fa-solid fa-user-circle fs-4 text-primary me-2"></i>
                <h5 class="mb-0 fw-bold text-dark">Información del Estudiante</h5>
            </div>
            <div class="card-body p-4 p-md-4">
                <?php if ($alumno): ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                                <div class="info-label">Nombre Completo</div>
                                <div class="info-value"><?php echo htmlspecialchars(($alumno['nombre'] ?? '') . ' ' . ($alumno['apellido_paterno'] ?? '') . ' ' . ($alumno['apellido_materno'] ?? '')); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                <div class="info-label">Carrera Actual</div>
                                <div class="info-value"><?php echo htmlspecialchars($alumno['nombre_carrera'] ?? 'No asignada'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-info">
                                <div class="info-label">Correo Institucional</div>
                                <div class="info-value"><?php echo htmlspecialchars($alumno['correo'] ?? 'No registrado'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
                                <div class="info-label">Matrícula Escolar</div>
                                <div class="info-value"><?php echo htmlspecialchars($matricula); ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i> No se encontraron registros detallados para esta cuenta.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>