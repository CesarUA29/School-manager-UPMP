<?php
session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Consultas para estadísticas
$total_alumnos = ($res = @$conn->query("SELECT COUNT(*) as total FROM alumnos")) ? $res->fetch_assoc()['total'] : 0;
$total_docentes = ($res = @$conn->query("SELECT COUNT(*) as total FROM maestros")) ? $res->fetch_assoc()['total'] : 0;
$total_materias = ($res = @$conn->query("SELECT COUNT(*) as total FROM materias")) ? $res->fetch_assoc()['total'] : 0;
$total_adeudos = ($res = @$conn->query("SELECT COUNT(*) as total FROM adeudos")) ? $res->fetch_assoc()['total'] : 0;

// Obtener datos para los selects de los modales
$carreras_res = @$conn->query("SELECT * FROM carreras ORDER BY nombre_carrera ASC");
$maestros_res = @$conn->query("SELECT * FROM maestros ORDER BY nombre ASC");
$materias_res = @$conn->query("SELECT * FROM materias ORDER BY nombre_materia ASC");
$alumnos_res = @$conn->query("SELECT * FROM alumnos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo - UPMP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; width: 260px; flex-shrink: 0; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 15px; display: block; border-radius: 6px; margin-bottom: 4px; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Menú Lateral -->
    <div class="sidebar p-3">
        <h4 class="text-center py-3"><i class="fa-solid fa-sliders text-warning"></i> UPMP Admin</h4>
        <nav class="mt-3">
            <a href="dashboard_admin.php" class="active"><i class="fa-solid fa-chart-line me-2"></i>Inicio / Métricas</a>
            <a href="gestion_alumnos.php"><i class="fa-solid fa-user-graduate me-2"></i>Gestión Alumnos</a>
            <a href="gestion_docentes.php"><i class="fa-solid fa-chalkboard-user me-2"></i>Gestión Docentes</a>
            <a href="gestion_materias.php"><i class="fa-solid fa-book me-2"></i>Materias</a>
            <a href="gestion_adeudos.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Finanzas / Adeudos</a>
            <hr class="border-secondary">
            <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Contenido Principal -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Panel de Control</h2>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>

        <!-- Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card p-3 bg-primary text-white text-center shadow-sm border-0"><h5>Alumnos</h5><h3><?php echo $total_alumnos; ?></h3></div></div>
            <div class="col-md-3"><div class="card p-3 bg-success text-white text-center shadow-sm border-0"><h5>Docentes</h5><h3><?php echo $total_docentes; ?></h3></div></div>
            <div class="col-md-3"><div class="card p-3 bg-info text-white text-center shadow-sm border-0"><h5>Materias</h5><h3><?php echo $total_materias; ?></h3></div></div>
            <div class="col-md-3"><div class="card p-3 bg-danger text-white text-center shadow-sm border-0"><h5>Adeudos</h5><h3><?php echo $total_adeudos; ?></h3></div></div>
        </div>

        <!-- Acciones Rápidas (Botones sólidos corregidos) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fa-solid fa-bolt text-warning me-2"></i>Acciones Rápidas</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><button class="btn btn-primary w-100 py-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistroAlumno"><i class="fa-solid fa-user-plus me-1"></i> Registrar Alumno</button></div>
                    <div class="col-md-3"><button class="btn btn-success w-100 py-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistroDocente"><i class="fa-solid fa-chalkboard-user me-1"></i> Alta Docente</button></div>
                    <div class="col-md-3"><button class="btn btn-info text-white w-100 py-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistroMateria"><i class="fa-solid fa-book-medical me-1"></i> Nueva Materia</button></div>
                    <div class="col-md-3"><button class="btn btn-secondary w-100 py-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalInscribirMateria"><i class="fa-solid fa-calendar-plus me-1"></i> Asignar Horario</button></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Horarios Registrados -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fa-solid fa-calendar-days text-secondary me-2"></i>Horarios Registrados</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Materia</th><th>Días</th><th>Horario</th><th>Aula</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_h = "SELECT m.nombre_materia, cl.dias, cl.hora_inicio, cl.hora_fin, cl.aula FROM clases cl JOIN materias m ON cl.id_materia = m.id_materia ORDER BY cl.id_clase DESC LIMIT 5";
                        $res_h = $conn->query($query_h);
                        if ($res_h && $res_h->num_rows > 0):
                            while($cl = $res_h->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cl['nombre_materia']); ?></td>
                                    <td><?php echo htmlspecialchars($cl['dias']); ?></td>
                                    <td><?php echo substr($cl['hora_inicio'],0,5).' - '.substr($cl['hora_fin'],0,5); ?></td>
                                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($cl['aula']); ?></span></td>
                                </tr>
                            <?php endwhile; 
                        else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No hay horarios registrados recientemente.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALES DE ACCESO RÁPIDO                  -->
<!-- ========================================== -->

<!-- Modal Registrar Alumno -->
<div class="modal fade" id="modalRegistroAlumno" tabindex="-1">
    <div class="modal-dialog">
        <form action="guardar_alumno.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Registrar Nuevo Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Matrícula</label>
                    <input type="text" name="matricula" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre(s)</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" name="apellido_materno" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Carrera</label>
                    <select name="id_carrera" class="form-select" required>
                        <option value="">Seleccione una carrera...</option>
                        <?php if ($carreras_res) { while($c = $carreras_res->fetch_assoc()) { ?>
                            <option value="<?php echo $c['id_carrera']; ?>"><?php echo $c['nombre_carrera']; ?></option>
                        <?php } } ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Alumno</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Alta Docente -->
<div class="modal fade" id="modalRegistroDocente" tabindex="-1">
    <div class="modal-dialog">
        <form action="guardar_docente.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-chalkboard-user me-2"></i>Alta de Docente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo Institucional</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Especialidad / Departamento</label>
                    <input type="text" name="especialidad" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar Docente</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nueva Materia -->
<div class="modal fade" id="modalRegistroMateria" tabindex="-1">
    <div class="modal-dialog">
        <form action="guardar_materia.php" method="POST" class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa-solid fa-book-medical me-2"></i>Registrar Nueva Materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Materia</label>
                    <input type="text" name="nombre_materia" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cuatrimestre / Semestre</label>
                    <input type="number" name="cuatrimestre" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info text-white">Guardar Materia</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar Horario -->
<div class="modal fade" id="modalInscribirMateria" tabindex="-1">
    <div class="modal-dialog">
        <form action="guardar_horario.php" method="POST" class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-calendar-plus me-2"></i>Asignar Horario / Clase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Materia</label>
                    <select name="id_materia" class="form-select" required>
                        <option value="">Seleccione una materia...</option>
                        <?php 
                        // Reseteamos el puntero si ya se usó antes o volvemos a consultar
                        $mat_res2 = @$conn->query("SELECT * FROM materias ORDER BY nombre_materia ASC");
                        if ($mat_res2) { while($m = $mat_res2->fetch_assoc()) { ?>
                            <option value="<?php echo $m['id_materia']; ?>"><?php echo $m['nombre_materia']; ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Docente Asignado</label>
                    <select name="id_maestro" class="form-select" required>
                        <option value="">Seleccione un docente...</option>
                        <?php if ($maestros_res) { while($doc = $maestros_res->fetch_assoc()) { ?>
                            <option value="<?php echo $doc['id_maestro']; ?>"><?php echo $doc['nombre']; ?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Días (Ej. Lunes, Miércoles)</label>
                    <input type="text" name="dias" class="form-control" placeholder="Lunes, Miércoles" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" name="hora_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora Fin</label>
                        <input type="time" name="hora_fin" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Aula / Laboratorio</label>
                    <input type="text" name="aula" class="form-control" placeholder="Ej. Aula 12 - Edificio C" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-secondary">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>