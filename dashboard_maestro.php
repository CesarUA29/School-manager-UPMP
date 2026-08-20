<?php
session_start();
require_once 'conexion.php';

// 1. Validar sesión y rol del maestro
if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['maestro', 'docente'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$username   = $_SESSION['username'];

// Determinar la sección/vista activa
$seccion = $_GET['seccion'] ?? 'inicio';
$mensaje = $_GET['mensaje'] ?? '';

// --- CONSULTAS BASE ---

// A. Obtener materias asignadas específicamente a este maestro desde el Admin
$query_materias = "SELECT id_materia, codigo_materia, nombre_materia, creditos, horario, aula 
                   FROM materias 
                   WHERE id_docente = " . intval($usuario_id);
$result_mat = $conn->query($query_materias);
$materias = $result_mat ? $result_mat->fetch_all(MYSQLI_ASSOC) : [];

// Si el docente no tiene materias asignadas directamente, mostrar todas por defecto (fallback)
if (empty($materias)) {
    $query_materias_all = "SELECT id_materia, codigo_materia, nombre_materia, creditos, horario, aula FROM materias";
    $result_mat_all = $conn->query($query_materias_all);
    $materias = $result_mat_all ? $result_mat_all->fetch_all(MYSQLI_ASSOC) : [];
}

$total_asignaturas = count($materias);

// B. Obtener todos los alumnos
$query_alumnos = "SELECT matricula, nombre, apellido_paterno, apellido_materno, grado, grupo, turno FROM alumnos";
$result_alu = $conn->query($query_alumnos);
$alumnos = $result_alu ? $result_alu->fetch_all(MYSQLI_ASSOC) : [];
$total_alumnos = count($alumnos);

// C. Obtener lista de grupos únicos para los filtros
$grupos_disponibles = [];
foreach ($alumnos as $al) {
    if (!empty($al['grupo']) && !in_array($al['grupo'], $grupos_disponibles)) {
        $grupos_disponibles[] = $al['grupo'];
    }
}
sort($grupos_disponibles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Docente - UPMP</title>
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
        <!-- Sidebar / Menú Lateral -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <div class="d-flex align-items-center gap-2 my-3 px-2">
                <i class="fa-solid fa-chalkboard-user fs-3 text-warning"></i>
                <h5 class="fw-bold mb-0 text-white">UPMP Docente</h5>
            </div>
            <hr class="text-secondary">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($seccion === 'inicio') ? 'active' : ''; ?>" href="dashboard_maestro.php?seccion=inicio">
                        <i class="fa-solid fa-chart-line me-2"></i> Inicio / Métricas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($seccion === 'mis-clases') ? 'active' : ''; ?>" href="dashboard_maestro.php?seccion=mis-clases">
                        <i class="fa-solid fa-graduation-cap me-2"></i> Mis Clases
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($seccion === 'asistencia') ? 'active' : ''; ?>" href="dashboard_maestro.php?seccion=asistencia">
                        <i class="fa-solid fa-list-check me-2"></i> Asistencias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($seccion === 'calificaciones') ? 'active' : ''; ?>" href="dashboard_maestro.php?seccion=calificaciones">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Calificaciones
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
            
            <!-- Alert Notificaciones -->
            <?php if ($mensaje === 'calificaciones_guardadas'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> ¡Las calificaciones se registraron/actualizaron correctamente!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($mensaje === 'asistencias_guardadas'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> ¡Las asistencias fueron registradas/actualizadas correctamente!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ================= VISTA: INICIO ================= -->
            <?php if ($seccion === 'inicio'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                    <div>
                        <h2 class="fw-bold mb-0">Panel de Control Docente</h2>
                        <p class="text-muted mb-0">Bienvenido de nuevo, Prof. <?php echo htmlspecialchars($username); ?></p>
                    </div>
                    <span class="badge bg-success p-2"><i class="fa-solid fa-calendar me-1"></i> Ciclo Activo</span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-custom bg-white p-3 text-center border-start border-success border-4">
                            <div class="text-muted small uppercase font-weight-bold">Asignaturas Asignadas</div>
                            <div class="h3 font-weight-bold text-success my-2"><?php echo $total_asignaturas; ?></div>
                            <small class="text-muted"><i class="fa-solid fa-book"></i> Materias impartidas</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom bg-white p-3 text-center border-start border-info border-4">
                            <div class="text-muted small uppercase font-weight-bold">Total Alumnos</div>
                            <div class="h3 font-weight-bold text-info my-2"><?php echo $total_alumnos; ?></div>
                            <small class="text-muted"><i class="fa-solid fa-users"></i> Alumnos en lista</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom bg-white p-3 text-center border-start border-warning border-4">
                            <div class="text-muted small uppercase font-weight-bold">Estatus del Sistema</div>
                            <div class="h3 font-weight-bold text-warning my-2">Al día</div>
                            <small class="text-muted"><i class="fa-solid fa-check"></i> Capturas habilitadas</small>
                        </div>
                    </div>
                </div>

            <!-- ================= VISTA: MIS CLASES ================= -->
            <?php elseif ($seccion === 'mis-clases'): ?>
                <div class="mb-4 border-bottom pb-2">
                    <h2 class="fw-bold mb-0">Mis Clases y Horarios</h2>
                    <p class="text-muted mb-0">Consulta tus materias asignadas, asignación de aulas y horarios de clase.</p>
                </div>

                <div class="card card-custom">
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Asignatura / Materia</th>
                                    <th>Horario</th>
                                    <th>Aula / Salón</th>
                                    <th class="text-center">Créditos</th>
                                    <th class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($materias)): ?>
                                    <?php foreach ($materias as $mat): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($mat['codigo_materia']); ?></span></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($mat['nombre_materia']); ?></td>
                                            <td><i class="fa-regular fa-clock me-1 text-muted"></i><?php echo htmlspecialchars(!empty($mat['horario']) ? $mat['horario'] : 'Por asignar'); ?></td>
                                            <td><i class="fa-solid fa-door-open me-1 text-muted"></i><?php echo htmlspecialchars(!empty($mat['aula']) ? $mat['aula'] : 'Por asignar'); ?></td>
                                            <td class="text-center"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($mat['creditos'] ?? '5'); ?> Créditos</span></td>
                                            <td class="text-center"><span class="badge bg-success">Activa</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No tienes materias asignadas actualmente.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ================= VISTA: ASISTENCIAS ================= -->
            <?php elseif ($seccion === 'asistencia'): 
                $fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d');
                $grupo_seleccionado = $_GET['grupo'] ?? 'todos';

                $alumnos_filtrados = array_filter($alumnos, function($al) use ($grupo_seleccionado) {
                    return ($grupo_seleccionado === 'todos') || ($al['grupo'] === $grupo_seleccionado);
                });

                $query_asist = "SELECT matricula_alumno, estatus FROM asistencias WHERE fecha = ?";
                $stmt_asist = $conn->prepare($query_asist);
                $asistencias_guardadas = [];

                if ($stmt_asist) {
                    $stmt_asist->bind_param("s", $fecha_seleccionada);
                    $stmt_asist->execute();
                    $res_asist = $stmt_asist->get_result();
                    while ($row = $res_asist->fetch_assoc()) {
                        $asistencias_guardadas[$row['matricula_alumno']] = $row['estatus'];
                    }
                    $stmt_asist->close();
                }

                $query_resumen = "SELECT matricula_alumno,
                                    SUM(CASE WHEN estatus = 'asistencia' THEN 1 ELSE 0 END) as total_asistencias,
                                    SUM(CASE WHEN estatus = 'falta' THEN 1 ELSE 0 END) as total_faltas,
                                    SUM(CASE WHEN estatus = 'retardo' THEN 1 ELSE 0 END) as total_retardos
                                  FROM asistencias GROUP BY matricula_alumno";
                $res_resumen = $conn->query($query_resumen);
                $resumen_asistencias = [];
                if ($res_resumen) {
                    while ($row = $res_resumen->fetch_assoc()) {
                        $resumen_asistencias[$row['matricula_alumno']] = $row;
                    }
                }
            ?>
                <div class="mb-4 border-bottom pb-2">
                    <h2 class="fw-bold mb-0">Control de Asistencias Diarias</h2>
                    <p class="text-muted mb-0">Filtra por fecha o grupo para tomar lista y consulta el acumulado general.</p>
                </div>

                <div class="card card-custom mb-4">
                    <div class="card-body bg-light">
                        <form id="formFiltroAsist" method="GET" action="dashboard_maestro.php" class="row align-items-center g-3">
                            <input type="hidden" name="seccion" value="asistencia">
                            <input type="hidden" id="inputGrupoAsist" name="grupo" value="<?php echo htmlspecialchars($grupo_seleccionado); ?>">
                            
                            <div class="col-md-6 col-sm-6">
                                <label for="fecha" class="form-label fw-bold small">Seleccionar Fecha:</label>
                                <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>" onchange="document.getElementById('formFiltroAsist').submit();">
                            </div>

                            <div class="col-md-6 col-sm-6 d-flex align-items-end">
                                <div class="dropdown w-100">
                                    <button class="btn btn-primary dropdown-toggle w-100 fw-semibold" type="button" id="dropdownFiltroGrupoAsist" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-filter me-2"></i>
                                        <?php echo ($grupo_seleccionado === 'todos') ? 'Todos los Grupos' : 'Grupo ' . htmlspecialchars($grupo_seleccionado); ?>
                                    </button>
                                    <ul class="dropdown-menu w-100 shadow" aria-labelledby="dropdownFiltroGrupoAsist">
                                        <li>
                                            <a class="dropdown-item <?php echo ($grupo_seleccionado === 'todos') ? 'active fw-bold' : ''; ?>" href="#" onclick="filtrarGrupoAsist('todos')">
                                                <i class="fa-solid fa-users me-2"></i> Todos los Grupos
                                            </a>
                                        </li>
                                        <?php if (!empty($grupos_disponibles)): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php foreach ($grupos_disponibles as $grp): ?>
                                                <li>
                                                    <a class="dropdown-item <?php echo ($grupo_seleccionado === $grp) ? 'active fw-bold' : ''; ?>" href="#" onclick="filtrarGrupoAsist('<?php echo htmlspecialchars($grp); ?>')">
                                                        <i class="fa-solid fa-layer-group me-2"></i> Grupo <?php echo htmlspecialchars($grp); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Formulario Pase de Lista -->
                <div class="card card-custom mb-5">
                    <form action="procesar_asistencia.php" method="POST">
                        <input type="hidden" name="fecha_asistencia" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
                        <input type="hidden" name="grupo_filtro" value="<?php echo htmlspecialchars($grupo_seleccionado); ?>">

                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                            <span>
                                <i class="fa-solid fa-list-check me-2 text-success"></i> 
                                Pase de Lista - <?php echo date('d/m/Y', strtotime($fecha_seleccionada)); ?>
                                <span class="badge bg-secondary ms-2"><?php echo ($grupo_seleccionado === 'todos') ? 'Todos los Grupos' : 'Grupo ' . htmlspecialchars($grupo_seleccionado); ?></span>
                            </span>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php if (!empty($alumnos_filtrados)): ?>
                                    <?php foreach ($alumnos_filtrados as $alumno): 
                                        $mat = htmlspecialchars($alumno['matricula']);
                                        $nombreCompleto = trim($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . ($alumno['apellido_materno'] ?? ''));
                                        $estatus_actual = $asistencias_guardadas[$mat] ?? 'asistencia';
                                    ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($nombreCompleto); ?></h6>
                                                <small class="text-muted">Matrícula: <?php echo $mat; ?> | Grupo: <?php echo htmlspecialchars($alumno['grupo']); ?></small>
                                            </div>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="asistencia[<?php echo $mat; ?>]" id="asist_p_<?php echo $mat; ?>" value="asistencia" <?php echo ($estatus_actual === 'asistencia') ? 'checked' : ''; ?> autocomplete="off">
                                                <label class="btn btn-outline-success" for="asist_p_<?php echo $mat; ?>">Asistencia</label>

                                                <input type="radio" class="btn-check" name="asistencia[<?php echo $mat; ?>]" id="asist_f_<?php echo $mat; ?>" value="falta" <?php echo ($estatus_actual === 'falta') ? 'checked' : ''; ?> autocomplete="off">
                                                <label class="btn btn-outline-danger" for="asist_f_<?php echo $mat; ?>">Falta</label>

                                                <input type="radio" class="btn-check" name="asistencia[<?php echo $mat; ?>]" id="asist_r_<?php echo $mat; ?>" value="retardo" <?php echo ($estatus_actual === 'retardo') ? 'checked' : ''; ?> autocomplete="off">
                                                <label class="btn btn-outline-warning" for="asist_r_<?php echo $mat; ?>">Retardo</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">No hay alumnos disponibles.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla Resumen Asistencias -->
                <div class="card card-custom">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fa-solid fa-chart-pie me-2 text-primary"></i> Reporte Acumulado de Asistencias
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th class="text-center">Asistencias</th>
                                    <th class="text-center">Faltas</th>
                                    <th class="text-center">Retardos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($alumnos_filtrados)): ?>
                                    <?php foreach ($alumnos_filtrados as $alumno): 
                                        $mat = htmlspecialchars($alumno['matricula']);
                                        $nombreCompleto = trim($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . ($alumno['apellido_materno'] ?? ''));
                                        $tot_a = $resumen_asistencias[$mat]['total_asistencias'] ?? 0;
                                        $tot_f = $resumen_asistencias[$mat]['total_faltas'] ?? 0;
                                        $tot_r = $resumen_asistencias[$mat]['total_retardos'] ?? 0;
                                    ?>
                                        <tr>
                                            <td><span class="fw-semibold text-secondary"><?php echo $mat; ?></span></td>
                                            <td><?php echo htmlspecialchars($nombreCompleto); ?></td>
                                            <td><span class="badge bg-secondary">Grupo <?php echo htmlspecialchars($alumno['grupo']); ?></span></td>
                                            <td class="text-center"><span class="badge bg-success fs-6"><?php echo $tot_a; ?></span></td>
                                            <td class="text-center"><span class="badge bg-danger fs-6"><?php echo $tot_f; ?></span></td>
                                            <td class="text-center"><span class="badge bg-warning text-dark fs-6"><?php echo $tot_r; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay datos acumulados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ================= VISTA: CALIFICACIONES ================= -->
            <?php elseif ($seccion === 'calificaciones'): 
                $materia_sel = $_GET['id_materia'] ?? ($materias[0]['id_materia'] ?? '');
                $parcial_sel = $_GET['parcial'] ?? '1';
                $grupo_calif = $_GET['grupo'] ?? 'todos';

                // Alumnos filtrados para calificaciones
                $alumnos_calif = array_filter($alumnos, function($al) use ($grupo_calif) {
                    return ($grupo_calif === 'todos') || ($al['grupo'] === $grupo_calif);
                });

                // Cargar calificaciones del parcial actual
                $calificaciones_guardadas = [];
                if (!empty($materia_sel)) {
                    $q_cal = "SELECT matricula_alumno, calificacion FROM calificaciones WHERE id_materia = ? AND parcial = ?";
                    $stmt_c = $conn->prepare($q_cal);
                    if ($stmt_c) {
                        $stmt_c->bind_param("ii", $materia_sel, $parcial_sel);
                        $stmt_c->execute();
                        $r_c = $stmt_c->get_result();
                        while ($row = $r_c->fetch_assoc()) {
                            $calificaciones_guardadas[$row['matricula_alumno']] = $row['calificacion'];
                        }
                        $stmt_c->close();
                    }
                }

                // Cargar TODOS los parciales por alumno para la tabla de desglose
                $desglose_parciales = [];
                if (!empty($materia_sel)) {
                    $q_desglose = "SELECT matricula_alumno, parcial, calificacion FROM calificaciones WHERE id_materia = ?";
                    $stmt_d = $conn->prepare($q_desglose);
                    if ($stmt_d) {
                        $stmt_d->bind_param("i", $materia_sel);
                        $stmt_d->execute();
                        $r_d = $stmt_d->get_result();
                        while ($row = $r_d->fetch_assoc()) {
                            $desglose_parciales[$row['matricula_alumno']][$row['parcial']] = $row['calificacion'];
                        }
                        $stmt_d->close();
                    }
                }
            ?>
                <div class="mb-4 border-bottom pb-2">
                    <h2 class="fw-bold mb-0">Captura de Calificaciones</h2>
                    <p class="text-muted mb-0">Filtra por Asignatura, Parcial, Grupo o busca alumnos por nombre.</p>
                </div>

                <!-- Filtros Principales -->
                <div class="card card-custom mb-4">
                    <div class="card-body bg-light">
                        <form id="formFiltroCalif" method="GET" action="dashboard_maestro.php" class="row align-items-center g-3">
                            <input type="hidden" name="seccion" value="calificaciones">
                            <input type="hidden" id="inputGrupoCalif" name="grupo" value="<?php echo htmlspecialchars($grupo_calif); ?>">

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Asignatura:</label>
                                <select name="id_materia" class="form-select form-select-sm" onchange="document.getElementById('formFiltroCalif').submit();">
                                    <?php foreach ($materias as $m): ?>
                                        <option value="<?php echo $m['id_materia']; ?>" <?php echo ($materia_sel == $m['id_materia']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['codigo_materia']) . ' - ' . htmlspecialchars($m['nombre_materia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Parcial a Capturar:</label>
                                <select name="parcial" class="form-select form-select-sm" onchange="document.getElementById('formFiltroCalif').submit();">
                                    <option value="1" <?php echo ($parcial_sel == '1') ? 'selected' : ''; ?>>1° Parcial</option>
                                    <option value="2" <?php echo ($parcial_sel == '2') ? 'selected' : ''; ?>>2° Parcial</option>
                                    <option value="3" <?php echo ($parcial_sel == '3') ? 'selected' : ''; ?>>3° Parcial / Final</option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="dropdown w-100">
                                    <button class="btn btn-primary dropdown-toggle w-100 fw-semibold btn-sm" type="button" id="dropdownFiltroGrupoCalif" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-filter me-1"></i>
                                        <?php echo ($grupo_calif === 'todos') ? 'Todos' : 'Grupo ' . htmlspecialchars($grupo_calif); ?>
                                    </button>
                                    <ul class="dropdown-menu w-100 shadow" aria-labelledby="dropdownFiltroGrupoCalif">
                                        <li>
                                            <a class="dropdown-item <?php echo ($grupo_calif === 'todos') ? 'active fw-bold' : ''; ?>" href="#" onclick="filtrarGrupoCalif('todos')">
                                                <i class="fa-solid fa-users me-2"></i> Todos los Grupos
                                            </a>
                                        </li>
                                        <?php if (!empty($grupos_disponibles)): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php foreach ($grupos_disponibles as $grp): ?>
                                                <li>
                                                    <a class="dropdown-item <?php echo ($grupo_calif === $grp) ? 'active fw-bold' : ''; ?>" href="#" onclick="filtrarGrupoCalif('<?php echo htmlspecialchars($grp); ?>')">
                                                        <i class="fa-solid fa-layer-group me-2"></i> Grupo <?php echo htmlspecialchars($grp); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Buscador Rápido de Alumnos -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Buscar Alumno:</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" id="inputBuscadorAlumno" class="form-control" placeholder="Nombre, apellido o matrícula..." onkeyup="filtrarAlumnosEnTabla()">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Formulario Captura de Calificaciones -->
                <div class="card card-custom mb-5">
                    <form action="procesar_calificacion.php" method="POST">
                        <input type="hidden" name="id_materia" value="<?php echo htmlspecialchars($materia_sel); ?>">
                        <input type="hidden" name="parcial" value="<?php echo htmlspecialchars($parcial_sel); ?>">
                        <input type="hidden" name="grupo_filtro" value="<?php echo htmlspecialchars($grupo_calif); ?>">

                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                            <span>
                                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> 
                                Captura Parcial <?php echo htmlspecialchars($parcial_sel); ?>
                                <span class="badge bg-secondary ms-2"><?php echo ($grupo_calif === 'todos') ? 'Todos los Grupos' : 'Grupo ' . htmlspecialchars($grupo_calif); ?></span>
                            </span>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Calificaciones
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="tablaCapturaCalif">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Alumno</th>
                                            <th>Grupo</th>
                                            <th style="width: 160px;" class="text-center">Calificación (0-10)</th>
                                            <th class="text-center">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($alumnos_calif)): ?>
                                            <?php foreach ($alumnos_calif as $alumno): 
                                                $mat = htmlspecialchars($alumno['matricula']);
                                                $nombreCompleto = trim($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . ($alumno['apellido_materno'] ?? ''));
                                                $val_calif = $calificaciones_guardadas[$mat] ?? '';
                                            ?>
                                                <tr class="fila-alumno">
                                                    <td><span class="fw-semibold text-secondary"><?php echo $mat; ?></span></td>
                                                    <td class="col-nombre-alumno"><?php echo htmlspecialchars($nombreCompleto); ?></td>
                                                    <td><span class="badge bg-secondary">Grupo <?php echo htmlspecialchars($alumno['grupo']); ?></span></td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.1" min="0" max="10" name="calificaciones[<?php echo $mat; ?>]" class="form-control form-control-sm text-center fw-bold mx-auto" style="max-width: 100px;" value="<?php echo htmlspecialchars($val_calif); ?>" placeholder="N/A">
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($val_calif !== ''): ?>
                                                            <?php if ((float)$val_calif >= 6.0): ?>
                                                                <span class="badge bg-success">Aprobado</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Reprobado</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-dark border">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No se encontraron alumnos.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla: Desglose por Parciales y Promedio General -->
                <div class="card card-custom">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fa-solid fa-chart-line me-2 text-success"></i> Desglose por Parciales y Promedio General
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th class="text-center">Parcial 1</th>
                                    <th class="text-center">Parcial 2</th>
                                    <th class="text-center">Parcial 3</th>
                                    <th class="text-center">Promedio General</th>
                                    <th class="text-center">Estatus Global</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($alumnos_calif)): ?>
                                    <?php foreach ($alumnos_calif as $alumno): 
                                        $mat = htmlspecialchars($alumno['matricula']);
                                        $nombreCompleto = trim($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . ($alumno['apellido_materno'] ?? ''));
                                        
                                        $p1 = $desglose_parciales[$mat][1] ?? null;
                                        $p2 = $desglose_parciales[$mat][2] ?? null;
                                        $p3 = $desglose_parciales[$mat][3] ?? null;

                                        // Calcular Promedio de parciales existentes
                                        $notas = array_filter([$p1, $p2, $p3], function($v) { return $v !== null && $v !== ''; });
                                        $promedio = count($notas) > 0 ? array_sum($notas) / count($notas) : null;
                                    ?>
                                        <tr>
                                            <td><span class="fw-semibold text-secondary"><?php echo $mat; ?></span></td>
                                            <td><?php echo htmlspecialchars($nombreCompleto); ?></td>
                                            <td><span class="badge bg-secondary">Grupo <?php echo htmlspecialchars($alumno['grupo']); ?></span></td>
                                            <td class="text-center fw-semibold"><?php echo ($p1 !== null) ? number_format((float)$p1, 1) : '-'; ?></td>
                                            <td class="text-center fw-semibold"><?php echo ($p2 !== null) ? number_format((float)$p2, 1) : '-'; ?></td>
                                            <td class="text-center fw-semibold"><?php echo ($p3 !== null) ? number_format((float)$p3, 1) : '-'; ?></td>
                                            <td class="text-center fw-bold text-primary fs-6">
                                                <?php echo ($promedio !== null) ? number_format($promedio, 1) : 'N/A'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($promedio !== null): ?>
                                                    <?php if ($promedio >= 6.0): ?>
                                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Aprobatorio</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> En Riesgo</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border">Sin notas</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay datos de calificaciones disponibles.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filtrarGrupoAsist(grupo) {
        document.getElementById('inputGrupoAsist').value = grupo;
        document.getElementById('formFiltroAsist').submit();
    }

    function filtrarGrupoCalif(grupo) {
        document.getElementById('inputGrupoCalif').value = grupo;
        document.getElementById('formFiltroCalif').submit();
    }

    function filtrarAlumnosEnTabla() {
        const input = document.getElementById('inputBuscadorAlumno').value.toLowerCase();
        const filas = document.querySelectorAll('#tablaCapturaCalif .fila-alumno');

        filas.forEach(fila => {
            const textoFila = fila.textContent.toLowerCase();
            if (textoFila.includes(input)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>