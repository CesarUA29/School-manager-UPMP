<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_adeudo = intval($_POST['id_adeudo'] ?? 0);
    $matricula = trim($_POST['matricula'] ?? '');
    $concepto = trim($_POST['concepto'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);
    $fecha_limite = $_POST['fecha_limite'] ?? '';
    $estatus_pago = $_POST['estatus_pago'] ?? 'Pendiente';

    if (empty($matricula) || empty($concepto) || $monto <= 0 || empty($fecha_limite)) {
        header("Location: gestion_adeudos.php?status=error");
        exit();
    }

    $mat = $conn->real_escape_string($matricula);
    $con = $conn->real_escape_string($concepto);
    $f_lim = $conn->real_escape_string($fecha_limite);
    $est = $conn->real_escape_string($estatus_pago);

    if ($accion === 'crear') {
        $sql = "INSERT INTO adeudos (`matricula`, `concepto`, `monto`, `fecha_limite`, `estatus_pago`) 
                VALUES ('$mat', '$con', $monto, '$f_lim', '$est')";
        if ($conn->query($sql)) {
            header("Location: gestion_adeudos.php?status=creado");
            exit();
        }
    } elseif ($accion === 'editar' && $id_adeudo > 0) {
        $sql = "UPDATE adeudos SET 
                    `matricula` = '$mat',
                    `concepto` = '$con',
                    `monto` = $monto,
                    `fecha_limite` = '$f_lim',
                    `estatus_pago` = '$est' 
                WHERE `id_adeudo` = $id_adeudo";
        if ($conn->query($sql)) {
            header("Location: gestion_adeudos.php?status=editado");
            exit();
        }
    }

    header("Location: gestion_adeudos.php?status=error");
    exit();
} else {
    header("Location: gestion_adeudos.php");
    exit();
}