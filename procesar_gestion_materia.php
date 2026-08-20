<?php
session_start();
require_once 'conexion.php';

// Validar sesión del administrador (flexible para evitar que reboote al login)
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
$rol        = strtolower($_SESSION['rol'] ?? $_SESSION['tipo_usuario'] ?? '');

if (!$usuario_id || ($rol !== 'admin' && $rol !== 'administrador')) {
    header("Location: login.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// --- PROCESAR HORARIOS ENVIADOS DESDE EL MODAL ---
function obtenerHorarioFormateado() {
    $dias = $_POST['dias'] ?? [];
    $horas_inicio = $_POST['hora_inicio'] ?? [];
    $horas_fin = $_POST['hora_fin'] ?? [];

    $horarios_formateados = [];
    for ($i = 0; $i < count($dias); $i++) {
        if (!empty($dias[$i]) && !empty($horas_inicio[$i]) && !empty($horas_fin[$i])) {
            $horarios_formateados[] = $dias[$i] . " de " . $horas_inicio[$i] . " a " . $horas_fin[$i];
        }
    }

    return !empty($horarios_formateados) ? implode("\n", $horarios_formateados) : null;
}

// -------------------------------------------------------------
// 1. CREAR NUEVA MATERIA
// -------------------------------------------------------------
if ($accion === 'crear') {
    $codigo_materia = trim($_POST['codigo_materia'] ?? '');
    $nombre_materia = trim($_POST['nombre_materia'] ?? '');
    $aula           = trim($_POST['aula'] ?? '');
    $creditos       = intval($_POST['creditos'] ?? 0);
    $id_docente     = !empty($_POST['id_docente']) ? intval($_POST['id_docente']) : NULL;
    $horario        = obtenerHorarioFormateado();

    $stmt = $conn->prepare("INSERT INTO materias (codigo_materia, nombre_materia, horario, aula, id_docente, creditos) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $codigo_materia, $nombre_materia, $horario, $aula, $id_docente, $creditos);

    if ($stmt->execute()) {
        header("Location: gestion_materias.php?mensaje=creada");
    } else {
        echo "Error al registrar la materia: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// -------------------------------------------------------------
// 2. EDITAR MATERIA EXISTENTE
// -------------------------------------------------------------
if ($accion === 'editar') {
    $id_materia     = intval($_POST['id_materia'] ?? 0);
    $codigo_materia = trim($_POST['codigo_materia'] ?? '');
    $nombre_materia = trim($_POST['nombre_materia'] ?? '');
    $aula           = trim($_POST['aula'] ?? '');
    $creditos       = intval($_POST['creditos'] ?? 0);
    $id_docente     = !empty($_POST['id_docente']) ? intval($_POST['id_docente']) : NULL;
    $horario        = obtenerHorarioFormateado();

    $stmt = $conn->prepare("UPDATE materias SET codigo_materia = ?, nombre_materia = ?, horario = ?, aula = ?, id_docente = ?, creditos = ? WHERE id_materia = ?");
    $stmt->bind_param("ssssiii", $codigo_materia, $nombre_materia, $horario, $aula, $id_docente, $creditos, $id_materia);

    if ($stmt->execute()) {
        header("Location: gestion_materias.php?mensaje=actualizada");
    } else {
        echo "Error al actualizar la materia: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// -------------------------------------------------------------
// 3. ELIMINAR MATERIA
// -------------------------------------------------------------
if ($accion === 'eliminar') {
    $id_materia = intval($_GET['id'] ?? 0);

    if ($id_materia > 0) {
        $stmt = $conn->prepare("DELETE FROM materias WHERE id_materia = ?");
        $stmt->bind_param("i", $id_materia);

        if ($stmt->execute()) {
            header("Location: gestion_materias.php?mensaje=eliminada");
        } else {
            echo "Error al eliminar la materia: " . $conn->error;
        }
        $stmt->close();
    } else {
        header("Location: gestion_materias.php");
    }
    exit();
}

// Redirección por defecto si no coincide ninguna acción
header("Location: gestion_materias.php");
exit();