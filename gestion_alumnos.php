<?php
session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$busqueda = trim($_GET['q'] ?? '');

// Obtener carreras para los desplegables
$carreras_list = @$conn->query("SELECT * FROM carreras ORDER BY nombre_carrera ASC");
if(!$carreras_list || $carreras_list->num_rows == 0) {
    $carreras_list = @$conn->query("SELECT * FROM carreras");
}

// Consulta principal adaptada a la estructura de tu base de datos
$sql = "SELECT a.*, c.nombre_carrera 
        FROM alumnos a 
        LEFT JOIN carreras c ON a.id_carrera = c.id_carrera";

if (!empty($busqueda)) {
    $b = $conn->real_escape_string($busqueda);
    $sql .= " WHERE a.nombre LIKE '%$b%' 
                OR a.apellido_paterno LIKE '%$b%' 
                OR a.matricula LIKE '%$b%' 
                OR a.correo_electronico LIKE '%$b%'";
}

$sql .= " ORDER BY a.matricula DESC";
$alumnos_res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - UPMP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar / Menú Lateral -->
    <div class="sidebar p-3 shadow" style="width: 260px;">
        <h4 class="text-center py-3 border-bottom"><i class="fa-solid fa-sliders me-2 text-warning"></i>UPMP Admin</h4>
        <nav class="mt-3">
            <a href="dashboard_admin.php" class="rounded mb-1"><i class="fa-solid fa-chart-line me-2"></i>Inicio / Métricas</a>
            <a href="gestion_alumnos.php" class="active rounded mb-1"><i class="fa-solid fa-user-graduate me-2"></i>Gestión Alumnos</a>
            <a href="gestion_docentes.php" class="rounded mb-1"><i class="fa-solid fa-chalkboard-user me-2"></i>Gestión Docentes</a>
            <a href="gestion_materias.php" class="rounded mb-1"><i class="fa-solid fa-book me-2"></i>Materias</a>
            <a href="gestion_adeudos.php" class="rounded mb-1"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Finanzas / Adeudos</a>
            <hr>
            <a href="logout.php" class="text-danger rounded"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Contenido Principal -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Gestión de Alumnos</h2>
                <p class="text-muted mb-0">Administra la información, matrículas y registros académicos.</p>
            </div>
            <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>

        <!-- Alertas de Estado -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'eliminado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>El alumno ha sido eliminado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'editado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Los datos del alumno han sido actualizados correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'registro_exitoso'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>El nuevo alumno ha sido registrado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif(str_contains($_GET['status'], 'error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Ocurrió un error en el proceso (Código: <?php echo htmlspecialchars($_GET['status']); ?>).
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Tarjeta de Contenido -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <form action="gestion_alumnos.php" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, matrícula o correo..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="gestion_alumnos.php" class="btn btn-outline-secondary" title="Limpiar filtro"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
                <!-- Botón que abre el Modal de Nuevo Alumno -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoAlumno">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Alumno
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Matrícula</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Carrera</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($alumnos_res && $alumnos_res->num_rows > 0): ?>
                                <?php $i = 1; while($alum = $alumnos_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($alum['matricula'] ?? 'N/A'); ?></span></td>
                                        <td class="fw-semibold">
                                            <?php 
                                                echo htmlspecialchars(
                                                    trim(($alum['nombre'] ?? '') . ' ' . 
                                                    ($alum['apellido_paterno'] ?? '') . ' ' . 
                                                    ($alum['apellido_materno'] ?? ''))
                                                ); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($alum['correo_electronico'] ?? 'Sin correo'); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($alum['nombre_carrera'] ?? 'Sin asignar'); ?></span></td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <button class="btn btn-outline-warning btn-sm me-1 btn-editar-alumno" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarAlumno"
                                                    data-matricula="<?php echo htmlspecialchars($alum['matricula'] ?? ''); ?>"
                                                    data-nombre="<?php echo htmlspecialchars($alum['nombre'] ?? ''); ?>"
                                                    data-paterno="<?php echo htmlspecialchars($alum['apellido_paterno'] ?? ''); ?>"
                                                    data-materno="<?php echo htmlspecialchars($alum['apellido_materno'] ?? ''); ?>"
                                                    data-correo="<?php echo htmlspecialchars($alum['correo_electronico'] ?? ''); ?>"
                                                    data-carrera="<?php echo $alum['id_carrera'] ?? 0; ?>"
                                                    title="Editar Alumno">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_alumno.php?matricula=<?php echo urlencode($alum['matricula']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este alumno?');" title="Eliminar Alumno">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open fs-2 d-block mb-2"></i>
                                        No se encontraron alumnos registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Nuevo Alumno -->
<div class="modal fade" id="modalNuevoAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Registrar Nuevo Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="./procesar_registro_alumno.php" method="POST">
                <div class="modal-body p-4">
                    <h6 class="text-primary fw-bold mb-3">1. Datos Escolares y Personales</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Matrícula</label>
                            <input type="text" name="matricula" class="form-control" required placeholder="Ej. 2026001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Carrera</label>
                            <select name="id_carrera" class="form-select" required>
                                <option value="">Selecciona carrera...</option>
                                <?php 
                                if($carreras_list && $carreras_list->num_rows > 0): 
                                    mysqli_data_seek($carreras_list, 0);
                                    while($car = $carreras_list->fetch_assoc()): 
                                        $c_id = $car['id_carrera'] ?? $car['id'] ?? 0;
                                        $c_nom = $car['nombre_carrera'] ?? $car['nombre'] ?? '';
                                ?>
                                    <option value="<?php echo $c_id; ?>"><?php echo htmlspecialchars($c_nom); ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Género</label>
                            <select name="genero" class="form-select">
                                <option value="Otro">Seleccionar...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Grado</label>
                            <input type="text" name="grado" class="form-control" placeholder="Ej. 1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Grupo</label>
                            <input type="text" name="grupo" class="form-control" placeholder="Ej. A">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Turno</label>
                            <input type="text" name="turno" class="form-control" placeholder="Matutino/Vespertino">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Correo Electrónico</label>
                            <input type="email" name="correo_electronico" class="form-control" required>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary fw-bold mb-3">2. Credenciales de Acceso al Sistema</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre de Usuario</label>
                            <input type="text" name="usuario" class="form-control" required placeholder="Usuario para login">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contraseña Provisional</label>
                            <input type="password" name="password" class="form-control" required placeholder="Contraseña">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Alumno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Alumno -->
<div class="modal fade" id="modalEditarAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Editar Datos del Alumno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_edicion_alumno.php" method="POST">
                <input type="hidden" name="matricula_original" id="edit_matricula_original">
                
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Matrícula</label>
                            <input type="text" name="matricula" id="edit_matricula" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Carrera</label>
                            <select name="carrera_id" id="edit_carrera" class="form-select">
                                <option value="0">Selecciona una carrera...</option>
                                <?php if($carreras_list && $carreras_list->num_rows > 0): ?>
                                    <?php 
                                    mysqli_data_seek($carreras_list, 0);
                                    while($car = $carreras_list->fetch_assoc()): 
                                        $c_id = $car['id_carrera'] ?? $car['id'] ?? 0;
                                        $c_nom = $car['nombre_carrera'] ?? $car['nombre'] ?? '';
                                    ?>
                                        <option value="<?php echo $c_id; ?>"><?php echo htmlspecialchars($c_nom); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nombre(s)</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="edit_paterno" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="edit_materno" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" id="edit_correo" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar-alumno').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('edit_matricula_original').value = this.dataset.matricula;
        document.getElementById('edit_matricula').value = this.dataset.matricula;
        document.getElementById('edit_nombre').value = this.dataset.nombre;
        document.getElementById('edit_paterno').value = this.dataset.paterno;
        document.getElementById('edit_materno').value = this.dataset.materno;
        document.getElementById('edit_correo').value = this.dataset.correo;
        document.getElementById('edit_carrera').value = this.dataset.carrera;
    });
});
</script>
</body>
</html>