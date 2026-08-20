<?php
session_start();
require_once 'conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$busqueda = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM maestros";

if (!empty($busqueda)) {
    $b = $conn->real_escape_string($busqueda);
    $sql .= " WHERE nombre LIKE '%$b%' 
               OR apellido_paterno LIKE '%$b%' 
               OR apellido_materno LIKE '%$b%' 
               OR correo LIKE '%$b%'";
}

$sql .= " ORDER BY id_maestro DESC";
$docentes_res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Docentes - UPMP</title>
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
            <a href="gestion_docentes.php" class="active rounded mb-1"><i class="fa-solid fa-chalkboard-user me-2"></i>Gestión Docentes</a>
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
                <h2 class="fw-bold mb-0">Gestión de Docentes</h2>
                <p class="text-muted mb-0">Administra la plantilla de profesores y sus cuentas.</p>
            </div>
            <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>

        <!-- Alertas de Estado -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'creado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Docente registrado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'editado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Datos del docente actualizados correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif($_GET['status'] == 'eliminado'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Docente eliminado del sistema.
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
                <form action="gestion_docentes.php" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o correo..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="gestion_docentes.php" class="btn btn-outline-secondary" title="Limpiar filtro"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoDocente">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Docente
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>ID Maestro</th>
                                <th>Nombre Completo</th>
                                <th>Correo Electrónico</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($docentes_res && $docentes_res->num_rows > 0): ?>
                                <?php $i = 1; while($doc = $docentes_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><span class="badge bg-secondary">MSTR-<?php echo sprintf('%04d', $doc['id_maestro']); ?></span></td>
                                        <td class="fw-semibold">
                                            <?php 
                                                echo htmlspecialchars(
                                                    trim(($doc['nombre'] ?? '') . ' ' . 
                                                    ($doc['apellido_paterno'] ?? '') . ' ' . 
                                                    ($doc['apellido_materno'] ?? ''))
                                                ); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['correo'] ?? 'Sin correo'); ?></td>
                                        <td class="text-center">
                                            <!-- Botón Editar -->
                                            <button class="btn btn-outline-warning btn-sm me-1 btn-editar-docente" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarDocente"
                                                    data-id="<?php echo $doc['id_maestro']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($doc['nombre'] ?? ''); ?>"
                                                    data-paterno="<?php echo htmlspecialchars($doc['apellido_paterno'] ?? ''); ?>"
                                                    data-materno="<?php echo htmlspecialchars($doc['apellido_materno'] ?? ''); ?>"
                                                    data-correo="<?php echo htmlspecialchars($doc['correo'] ?? ''); ?>"
                                                    title="Editar Docente">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <!-- Botón Eliminar -->
                                            <a href="eliminar_docente.php?id=<?php echo $doc['id_maestro']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este docente?');" title="Eliminar Docente">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open fs-2 d-block mb-2"></i>
                                        No se encontraron docentes registrados.
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

<!-- Modal: Nuevo Docente -->
<div class="modal fade" id="modalNuevoDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Registrar Nuevo Docente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_docente.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body p-4">
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
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Docente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Docente -->
<div class="modal fade" id="modalEditarDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Editar Datos del Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_docente.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_maestro" id="edit_docente_id">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nombre(s)</label>
                            <input type="text" name="nombre" id="edit_docente_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="edit_docente_paterno" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="edit_docente_materno" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" id="edit_docente_correo" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Actualizar Docente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-editar-docente').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('edit_docente_id').value = this.dataset.id;
        document.getElementById('edit_docente_nombre').value = this.dataset.nombre;
        document.getElementById('edit_docente_paterno').value = this.dataset.paterno;
        document.getElementById('edit_docente_materno').value = this.dataset.materno;
        document.getElementById('edit_docente_correo').value = this.dataset.correo;
    });
});
</script>
</body>
</html>