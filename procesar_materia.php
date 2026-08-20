<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_materia = intval($_POST['id_materia'] ?? 0);
    $codigo_materia = trim($_POST['codigo_materia'] ?? '');
    $nombre_materia = trim($_POST['nombre_materia'] ?? '');
    $creditos = intval($_POST['creditos'] ?? 5);

    if (empty($codigo_materia) || empty($nombre_materia)) {
        header("Location: gestion_materias.php?status=error");
        exit();
    }

    $cod = $conn->real_escape_string($codigo_materia);
    $nom = $conn->real_escape_string($nombre_materia);

    if ($accion === 'crear') {
        $sql = "INSERT INTO materias (`codigo_materia`, `nombre_materia`, `creditos`) 
                VALUES ('$cod', '$nom', $creditos)";
        if ($conn->query($sql)) {
            header("Location: gestion_materias.php?status=creado");
            exit();
        }
    } elseif ($accion === 'editar' && $id_materia > 0) {
        $sql = "UPDATE materias SET 
                    `codigo_materia` = '$cod',
                    `nombre_materia` = '$nom',
                    `creditos` = $creditos 
                WHERE `id_materia` = $id_materia";
        if ($conn->query($sql)) {
            header("Location: gestion_materias.php?status=editado");
            exit();
        }
    }

    header("Location: gestion_materias.php?status=error");
    exit();
} else {
    header("Location: gestion_materias.php");
    exit();
}