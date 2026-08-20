<?php
session_start();
require_once 'conexion.php';

// Validar sesión del administrador
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
$rol        = strtolower($_SESSION['rol'] ?? $_SESSION['tipo_usuario'] ?? '');

if (!$usuario_id || ($rol !== 'admin' && $rol !== 'administrador')) {
    header("Location: login.php");
    exit();
}

$mensaje = $_GET['mensaje'] ?? '';

// --- CONSULTAS ---

// 1. Obtener lista de materias con el nombre del docente asignado
$query_materias = "SELECT m.*, u.usuario AS nombre_docente 
                   FROM materias m 
                   LEFT JOIN usuarios u ON m.id_docente = u.id 
                   ORDER BY m.id_materia ASC";
$result_mat = $conn->query($query_materias);
$materias = $result_mat ? $result_mat->fetch_all(MYSQLI_ASSOC) : [];

// 2. Obtener lista de docentes para la asignación
$query_docentes = "SELECT id, usuario FROM usuarios ORDER BY usuario ASC";
$result_doc = $conn->query($query_docentes);
$docentes = $result_doc ? $result_doc->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materias - UPMP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #212529; min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); transition: all 0.2s; padding: 12px 16px; border-radius: 6px; }
        .sidebar .nav-link:hover { color: white; background-color: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { color: white; background-color: #0d6efd; font-weight: 500; }
        .card-custom { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <div class="d-flex align-items-center gap-2 my-3 px-2">
                <i class="fa-solid fa-sliders fs-3 text-warning"></i>
                <h5 class="fw-bold mb-0 text-white">UPMP Admin</h5>
            </div>
            <hr class="text-secondary">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_admin.php?seccion=inicio">
                        <i class="fa-solid fa-chart-line me-2"></i> Inicio / Métricas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_admin.php?seccion=alumnos">
                        <i class="fa-solid fa-user-graduate me-2"></i> Gestión Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_admin.php?seccion=docentes">
                        <i class="fa-solid fa-chalkboard-user me-2"></i> Gestión Docentes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="gestion_materias.php">
                        <i class="fa-solid fa-book me-2"></i> Materias
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link text-danger fw-bold" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <div>
                    <h2 class="fw-bold mb-0">Gestión de Materias</h2>
                    <p class="text-muted mb-0">Administra el plan de estudios, asigna docentes, aulas y horarios correspondientes.</p>
                </div>
                <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevaMateria">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Materia
                </button>
            </div>

            <!-- Notificaciones -->
            <?php if ($mensaje === 'creada'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Materia registrada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($mensaje === 'actualizada'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Asignación y datos de la materia actualizados.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($mensaje === 'eliminada'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-trash me-2"></i> Materia eliminada del sistema.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Buscador Rápido -->
            <div class="card card-custom mb-4">
                <div class="card-body bg-light">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" id="inputBuscarMateria" class="form-control" placeholder="Buscar por código, materia, horario, aula o docente..." onkeyup="filtrarMaterias()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Materias -->
            <div class="card card-custom">
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle" id="tablaMaterias">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Nombre de la Materia</th>
                                <th>Horario</th>
                                <th>Aula / Salón</th>
                                <th>Docente Asignado</th>
                                <th class="text-center">Créditos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materias)): ?>
                                <?php $i = 1; foreach ($materias as $m): ?>
                                    <tr class="fila-materia">
                                        <td><?php echo $i++; ?></td>
                                        <td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($m['codigo_materia']); ?></span></td>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($m['nombre_materia']); ?></td>
                                        <td>
                                            <i class="fa-regular fa-clock me-1 text-muted"></i>
                                            <span class="small"><?php echo !empty($m['horario']) ? nl2br(htmlspecialchars($m['horario'])) : 'Por asignar'; ?></span>
                                        </td>
                                        <td><i class="fa-solid fa-door-open me-1 text-muted"></i><?php echo htmlspecialchars(!empty($m['aula']) ? $m['aula'] : 'Por asignar'); ?></td>
                                        <td>
                                            <?php if (!empty($m['nombre_docente'])): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-user-tie me-1"></i><?php echo htmlspecialchars($m['nombre_docente']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Por Asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($m['creditos']); ?> pts</span></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarMateria<?php echo $m['id_materia']; ?>" title="Editar / Asignar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <a href="procesar_gestion_materia.php?accion=eliminar&id=<?php echo $m['id_materia']; ?>" class="btn btn-outline-danger" onclick="return confirm('¿Deseas eliminar esta materia?');" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- MODAL EDITAR / ASIGNAR MATERIA -->
                                    <div class="modal fade" id="modalEditarMateria<?php echo $m['id_materia']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="procesar_gestion_materia.php" method="POST">
                                                    <input type="hidden" name="accion" value="editar">
                                                    <input type="hidden" name="id_materia" value="<?php echo $m['id_materia']; ?>">
                                                    
                                                    <div class="modal-header bg-dark text-white">
                                                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Editar / Asignar Materia</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Código / Clave:</label>
                                                                <input type="text" name="codigo_materia" class="form-control" value="<?php echo htmlspecialchars($m['codigo_materia']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Nombre de la Materia:</label>
                                                                <input type="text" name="nombre_materia" class="form-control" value="<?php echo htmlspecialchars($m['nombre_materia']); ?>" required>
                                                            </div>
                                                        </div>

                                                        <!-- SECCIÓN DE HORARIOS POR DÍAS -->
                                                        <div class="mb-3 border p-3 rounded bg-light">
                                                            <label class="form-label fw-bold d-block">
                                                                <i class="fa-regular fa-calendar-days me-1 text-primary"></i> Configurar Horarios por Día:
                                                            </label>
                                                            <div id="contenedor-horarios-edit-<?php echo $m['id_materia']; ?>">
                                                                <div class="row g-2 mb-2 item-horario">
                                                                    <div class="col-md-4">
                                                                        <select name="dias[]" class="form-select">
                                                                            <option value="Lunes">Lunes</option>
                                                                            <option value="Martes">Martes</option>
                                                                            <option value="Miércoles">Miércoles</option>
                                                                            <option value="Jueves">Jueves</option>
                                                                            <option value="Viernes">Viernes</option>
                                                                            <option value="Sábado">Sábado</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="time" name="hora_inicio[]" class="form-control" value="08:00">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="time" name="hora_fin[]" class="form-control" value="10:00">
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.item-horario').remove()"><i class="fa-solid fa-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="agregarHorario('contenedor-horarios-edit-<?php echo $m['id_materia']; ?>')">
                                                                <i class="fa-solid fa-plus me-1"></i> Agregar otro día
                                                            </button>
                                                            <div class="form-text mt-2">
                                                                <strong>Horario actual registrado:</strong><br>
                                                                <code><?php echo !empty($m['horario']) ? nl2br(htmlspecialchars($m['horario'])) : 'Ninguno'; ?></code>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Aula / Salón:</label>
                                                                <input type="text" name="aula" class="form-control" placeholder="Ej. Lab 2 / Salón 104" value="<?php echo htmlspecialchars($m['aula'] ?? ''); ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Créditos:</label>
                                                                <input type="number" name="creditos" class="form-control" value="<?php echo htmlspecialchars($m['creditos']); ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Docente Asignado:</label>
                                                            <select name="id_docente" class="form-select">
                                                                <option value="">-- Sin Asignar --</option>
                                                                <?php foreach ($docentes as $doc): ?>
                                                                    <option value="<?php echo $doc['id']; ?>" <?php echo ($m['id_docente'] == $doc['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($doc['usuario']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No hay materias registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL CREAR NUEVA MATERIA -->
<div class="modal fade" id="modalNuevaMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="procesar_gestion_materia.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2"></i>Nueva Materia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Código / Clave:</label>
                            <input type="text" name="codigo_materia" class="form-control" placeholder="Ej. ISC-303" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre de la Materia:</label>
                            <input type="text" name="nombre_materia" class="form-control" placeholder="Ej. Arquitectura de Software" required>
                        </div>
                    </div>

                    <!-- SECCIÓN DE HORARIOS DINÁMICOS -->
                    <div class="mb-3 border p-3 rounded bg-light">
                        <label class="form-label fw-bold d-block">
                            <i class="fa-regular fa-calendar-days me-1 text-primary"></i> Asignar Días y Horarios:
                        </label>
                        <div id="contenedor-horarios-nuevo">
                            <div class="row g-2 mb-2 item-horario">
                                <div class="col-md-4">
                                    <select name="dias[]" class="form-select">
                                        <option value="Lunes">Lunes</option>
                                        <option value="Martes">Martes</option>
                                        <option value="Miércoles">Miércoles</option>
                                        <option value="Jueves">Jueves</option>
                                        <option value="Viernes">Viernes</option>
                                        <option value="Sábado">Sábado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="time" name="hora_inicio[]" class="form-control" value="08:00">
                                </div>
                                <div class="col-md-3">
                                    <input type="time" name="hora_fin[]" class="form-control" value="10:00">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.item-horario').remove()"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="agregarHorario('contenedor-horarios-nuevo')">
                            <i class="fa-solid fa-plus me-1"></i> Agregar otro día
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Aula / Salón:</label>
                            <input type="text" name="aula" class="form-control" placeholder="Ej. Salón 104">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Créditos:</label>
                            <input type="number" name="creditos" class="form-control" value="5" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Docente Asignado:</label>
                        <select name="id_docente" class="form-select">
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach ($docentes as $doc): ?>
                                <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['usuario']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Registrar Materia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Filtro rápido de búsqueda en tabla
    function filtrarMaterias() {
        const input = document.getElementById('inputBuscarMateria').value.toLowerCase();
        const filas = document.querySelectorAll('#tablaMaterias .fila-materia');

        filas.forEach(fila => {
            const textoFila = fila.textContent.toLowerCase();
            fila.style.display = textoFila.includes(input) ? '' : 'none';
        });
    }

    // Agregar filas dinámicas de horarios
    function agregarHorario(idContenedor) {
        const contenedor = document.getElementById(idContenedor);
        const nuevoDiv = document.createElement('div');
        nuevoDiv.className = 'row g-2 mb-2 item-horario';
        nuevoDiv.innerHTML = `
            <div class="col-md-4">
                <select name="dias[]" class="form-select">
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="time" name="hora_inicio[]" class="form-control" value="08:00">
            </div>
            <div class="col-md-3">
                <input type="time" name="hora_fin[]" class="form-control" value="10:00">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.item-horario').remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
        contenedor.appendChild(nuevoDiv);
    }
</script>
</body>
</html>