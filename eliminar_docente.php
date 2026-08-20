<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

$id_maestro = intval($_GET['id'] ?? 0);

if ($id_maestro > 0) {
    $sql = "DELETE FROM maestros WHERE `id_maestro` = $id_maestro";
    if ($conn->query($sql)) {
        header("Location: gestion_docentes.php?status=eliminado");
        exit();
    }
}

header("Location: gestion_docentes.php?status=error");
exit();