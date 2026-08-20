<?php
session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$busqueda = trim($_GET['q'] ?? '');

// Obtener lista de alumnos para asociarlos en el modal de nuevo/editar adeudo
$alumnos_list = $conn->query("SELECT matricula, nombre, apellido_paterno, apellido_materno FROM alumnos ORDER BY apellido_paterno ASC");

// Consulta principal con LEFT JOIN para obtener el nombre del alumno
$sql = "SELECT ad.*, 
               CONCAT(IFNULL(a.nombre, ''), ' ', IFNULL(a.apellido_paterno, ''), ' ', IFNULL(a.apellido_materno, '')) AS nombre_alumno 
        FROM adeudos ad 
        LEFT JOIN alumnos a ON ad.matricula = a.matricula";

if (!empty($busqueda)) {
    $b = $conn->real_escape_string($busqueda);
    $sql .= " WHERE ad.matricula LIKE '%$b%' 
               OR ad.concepto LIKE '%$b%' 
               OR a.nombre LIKE '%$b%' 
               OR a.apellido_paterno LIKE '%$b%'";
}

$sql .= " ORDER BY ad.id_adeudo DESC";
$adeudos_res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas y Adeudos - UPMP</title>
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
            <a href="gestion_alumnos.php" class="rounded mb-1"><i class="fa-solid fa-user-graduate me-2"></i>Gestión Alumnos</a>
            <a href="gestion_docentes.php" class="rounded mb-1"><i class="fa-solid fa-chalkboard-user me-2"></i>Gestión Docentes</a>
            <a href="gestion_materias.php" class="rounded mb-1"><i class="fa-solid fa-book me-2"></i>Materias</a>
            <a href="gestion_adeudos.php" class="active rounded mb-1"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Finanzas / Adeudos</a>
            <hr>
            <a href="logout.php" class="text-danger rounded"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Contenido Principal -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Control de Finanzas y Adeudos</h2>
                <p class="text-muted mb-0">Gestión de pagos, inscripciones, colegiaturas y estatus de cuenta.</p>
            </div>
            <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>

        <!-- Alertas de Estado -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'creado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Cargo registrado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'editado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Registro de adeudo actualizado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'eliminado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Adeudo eliminado del sistema.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Ocurrió un error al procesar la solicitud.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Tarjeta de Contenido -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <form action="gestion_adeudos.php" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por matrícula, concepto o alumno..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="gestion_adeudos.php" class="btn btn-outline-secondary" title="Limpiar filtro"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoAdeudo">
                    <i class="fa-solid fa-plus me-1"></i> Asignar Nuevo Cargo
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Matrícula</th>
                                <th>Alumno</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Fecha Límite</th>
                                <th>Estatus</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($adeudos_res && $adeudos_res->num_rows > 0): ?>
                                <?php $i = 1; while($ad = $adeudos_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ad['matricula']); ?></span></td>
                                        <td class="fw-semibold">
                                            <?php echo htmlspecialchars(trim($ad['nombre_alumno']) ?: 'Sin asignar / Registro borrado'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($ad['concepto']); ?></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($ad['monto'], 2); ?></td>
                                        <td>
                                            <i class="fa-regular fa-calendar-days me-1 text-muted"></i>
                                            <?php echo date('d/m/Y', strtotime($ad['fecha_limite'])); ?>
                                        </td>
                                        <td>
                                            <?php if($ad['estatus_pago'] === 'Pagado'): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Pagado</span>
                                            <?php elseif($ad['estatus_pago'] === 'Cancelado'): ?>
                                                <span class="badge bg-secondary"><i class="fa-solid fa-ban me-1"></i>Cancelado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="fa-solid fa-clock me-1"></i>Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <button class="btn btn-outline-warning btn-sm me-1 btn-editar-adeudo" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarAdeudo"
                                                    data-id="<?php echo $ad['id_adeudo']; ?>"
                                                    data-matricula="<?php echo htmlspecialchars($ad['matricula']); ?>"
                                                    data-concepto="<?php echo htmlspecialchars($ad['concepto']); ?>"
                                                    data-monto="<?php echo $ad['monto']; ?>"
                                                    data-fechalimite="<?php echo $ad['fecha_limite']; ?>"
                                                    data-estatus="<?php echo $ad['estatus_pago']; ?>"
                                                    title="Editar Cargo">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_adeudo.php?id=<?php echo $ad['id_adeudo']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro de cargo?');" title="Eliminar Registro">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-file-circle-check fs-2 d-block mb-2"></i>
                                        No se encontraron cargos o adeudos registrados.
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

<!-- Modal: Nuevo Adeudo -->
<div class="modal fade" id="modalNuevoAdeudo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Asignar Nuevo Cargo / Adeudo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_adeudo.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Seleccionar Alumno</label>
                            <select name="matricula" class="form-select" required>
                                <option value="">Selecciona la matrícula o nombre...</option>
                                <?php if($alumnos_list && $alumnos_list->num_rows > 0): ?>
                                    <?php 
                                    mysqli_data_seek($alumnos_list, 0);
                                    while($al = $alumnos_list->fetch_assoc()): 
                                        $nom_comp = trim($al['nombre'] . ' ' . $al['apellido_paterno'] . ' ' . $al['apellido_materno']);
                                    ?>
                                        <option value="<?php echo htmlspecialchars($al['matricula']); ?>">
                                            <?php echo htmlspecialchars($al['matricula']) . ' - ' . htmlspecialchars($nom_comp); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Concepto del Pago</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Ej. Colegiatura Agosto, Inscripción" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Monto ($)</label>
                            <input type="number" step="0.01" name="monto" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Fecha Límite</label>
                            <input type="date" name="fecha_limite" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Estatus de Pago</label>
                            <select name="estatus_pago" class="form-select" required>
                                <option value="Pendiente" selected>Pendiente</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cargo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Adeudo -->
<div class="modal fade" id="modalEditarAdeudo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Registro de Adeudo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_adeudo.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_adeudo" id="edit_adeudo_id">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Matrícula del Alumno</label>
                            <select name="matricula" id="edit_adeudo_matricula" class="form-select" required>
                                <option value="">Selecciona alumno...</option>
                                <?php if($alumnos_list && $alumnos_list->num_rows > 0): ?>
                                    <?php 
                                    mysqli_data_seek($alumnos_list, 0);
                                    while($al = $alumnos_list->fetch_assoc()): 
                                        $nom_comp = trim($al['nombre'] . ' ' . $al['apellido_paterno'] . ' ' . $al['apellido_materno']);
                                    ?>
                                        <option value="<?php echo htmlspecialchars($al['matricula']); ?>">
                                            <?php echo htmlspecialchars($al['matricula']) . ' - ' . htmlspecialchars($nom_comp); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Concepto</label>
                            <input type="text" name="concepto" id="edit_adeudo_concepto" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Monto ($)</label>
                            <input type="number" step="0.01" name="monto" id="edit_adeudo_monto" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Fecha Límite</label>
                            <input type="date" name="fecha_limite" id="edit_adeudo_fechalimite" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Estatus de Pago</label>
                            <select name="estatus_pago" id="edit_adeudo_estatus" class="form-select" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Actualizar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar-adeudo').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('edit_adeudo_id').value = this.dataset.id;
        document.getElementById('edit_adeudo_matricula').value = this.dataset.matricula;
        document.getElementById('edit_adeudo_concepto').value = this.dataset.concepto;
        document.getElementById('edit_adeudo_monto').value = this.dataset.monto;
        document.getElementById('edit_adeudo_fechalimite').value = this.dataset.fechalimite;
        document.getElementById('edit_adeudo_estatus').value = this.dataset.estatus;
    });
});
</script>
</body>
</html>