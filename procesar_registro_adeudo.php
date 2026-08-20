<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumno_ref = trim($_POST['alumno_ref'] ?? '');
    $concepto = trim($_POST['concepto'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);

    if (empty($alumno_ref) || empty($concepto) || $monto <= 0) {
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }

    // Inspeccionar columnas reales de la tabla 'adeudos'
    $columnas = [];
    $res = $conn->query("SHOW COLUMNS FROM adeudos");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $columnas[] = strtolower($row['Field']);
        }
    }

    // Identificar coincidencias de nombres de columnas
    $col_monto = in_array('monto', $columnas) ? 'monto' : (in_array('total', $columnas) ? 'total' : null);
    $col_concepto = in_array('concepto', $columnas) ? 'concepto' : (in_array('descripcion', $columnas) ? 'descripcion' : null);
    $col_alumno = in_array('alumno_id', $columnas) ? 'alumno_id' : (in_array('matricula', $columnas) ? 'matricula' : (in_array('id_alumno', $columnas) ? 'id_alumno' : null));
    $col_estado = in_array('estado', $columnas) ? 'estado' : (in_array('estatus', $columnas) ? 'estatus' : null);

    $fields = [];
    $params = [];
    $types = "";

    if ($col_alumno) {
        $fields[] = "`$col_alumno`";
        $params[] = $alumno_ref;
        $types .= is_numeric($alumno_ref) ? "i" : "s";
    }

    if ($col_concepto) {
        $fields[] = "`$col_concepto`";
        $params[] = $concepto;
        $types .= "s";
    }

    if ($col_monto) {
        $fields[] = "`$col_monto`";
        $params[] = $monto;
        $types .= "d";
    }

    if ($col_estado) {
        $fields[] = "`$col_estado`";
        $params[] = "pendiente";
        $types .= "s";
    }

    if (empty($fields)) {
        header("Location: dashboard_admin.php?status=error_guardado");
        exit();
    }

    $sql = "INSERT INTO adeudos (" . implode(", ", $fields) . ") VALUES (" . implode(", ", array_fill(0, count($fields), "?")) . ")";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header("Location: dashboard_admin.php?status=adeudo_exitoso");
            exit();
        }
    }

    header("Location: dashboard_admin.php?status=error_guardado");
    exit();
} else {
    header("Location: dashboard_admin.php");
    exit();
}