<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos permitiendo nombres alternativos desde el formulario HTML
    $clave      = trim($_POST['clave_materia'] ?? $_POST['clave'] ?? $_POST['codigo'] ?? '');
    $nombre     = trim($_POST['nombre_materia'] ?? $_POST['nombre'] ?? '');
    $carrera_id = intval($_POST['carrera_id'] ?? $_POST['id_carrera'] ?? 0);
    $creditos   = intval($_POST['creditos'] ?? 5);

    // Validación flexible: al menos se requiere el nombre de la materia
    if (empty($nombre)) {
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }

    // Obtener las columnas existentes en la tabla 'materias' dinámicamente
    $columnas = [];
    $res = $conn->query("SHOW COLUMNS FROM materias");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $columnas[] = strtolower($row['Field']);
        }
    }

    // Mapear campos disponibles en la base de datos
    $col_nombre   = in_array('nombre_materia', $columnas) ? 'nombre_materia' : (in_array('nombre', $columnas) ? 'nombre' : null);
    $col_clave    = in_array('clave_materia', $columnas) ? 'clave_materia' : (in_array('clave', $columnas) ? 'clave' : (in_array('codigo_materia', $columnas) ? 'codigo_materia' : (in_array('codigo', $columnas) ? 'codigo' : null)));
    $col_carrera  = in_array('carrera_id', $columnas) ? 'carrera_id' : (in_array('id_carrera', $columnas) ? 'id_carrera' : null);
    $col_creditos = in_array('creditos', $columnas) ? 'creditos' : null;

    $fields = [];
    $params = [];
    $types  = "";

    if ($col_nombre && !empty($nombre)) {
        $fields[] = "`$col_nombre`";
        $params[] = $nombre;
        $types   .= "s";
    }

    if ($col_clave && !empty($clave)) {
        $fields[] = "`$col_clave`";
        $params[] = $clave;
        $types   .= "s";
    }

    if ($col_carrera && $carrera_id > 0) {
        $fields[] = "`$col_carrera`";
        $params[] = $carrera_id;
        $types   .= "i";
    }

    if ($col_creditos) {
        $fields[] = "`$col_creditos`";
        $params[] = $creditos;
        $types   .= "i";
    }

    if (empty($fields)) {
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }

    // Construcción de la sentencia SQL dinámica
    $sql = "INSERT INTO materias (" . implode(", ", $fields) . ") VALUES (" . implode(", ", array_fill(0, count($fields), "?")) . ")";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: dashboard_admin.php?status=materia_exitosa");
            exit();
        }
        $stmt->close();
    }

    header("Location: dashboard_admin.php?status=error_guardado");
    exit();
} else {
    header("Location: dashboard_admin.php");
    exit();
}