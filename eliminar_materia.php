<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$id_materia = intval($_GET['id'] ?? 0);

if ($id_materia > 0) {
    $sql = "DELETE FROM materias WHERE `id_materia` = $id_materia";
    if ($conn->query($sql)) {
        header("Location: gestion_materias.php?status=eliminado");
        exit();
    }
}

header("Location: gestion_materias.php?status=error");
exit();