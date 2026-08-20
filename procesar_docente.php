<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || !in_array(strtolower($_SESSION['rol']), ['administrador', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_maestro = intval($_POST['id_maestro'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (empty($nombre) || empty($apellido_paterno)) {
        header("Location: gestion_docentes.php?status=error");
        exit();
    }

    $nom = $conn->real_escape_string($nombre);
    $pat = $conn->real_escape_string($apellido_paterno);
    $mat = $conn->real_escape_string($apellido_materno);
    $corr = $conn->real_escape_string($correo);

    if ($accion === 'crear') {
        $sql = "INSERT INTO maestros (`nombre`, `apellido_paterno`, `apellido_materno`, `correo`) 
                VALUES ('$nom', '$pat', '$mat', '$corr')";
        if ($conn->query($sql)) {
            header("Location: gestion_docentes.php?status=creado");
            exit();
        }
    } elseif ($accion === 'editar' && $id_maestro > 0) {
        $sql = "UPDATE maestros SET 
                    `nombre` = '$nom',
                    `apellido_paterno` = '$pat',
                    `apellido_materno` = '$mat',
                    `correo` = '$corr' 
                WHERE `id_maestro` = $id_maestro";
        if ($conn->query($sql)) {
            header("Location: gestion_docentes.php?status=editado");
            exit();
        }
    }

    header("Location: gestion_docentes.php?status=error");
    exit();
} else {
    header("Location: gestion_docentes.php");
    exit();
}