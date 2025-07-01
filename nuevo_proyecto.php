<?php
include 'db.php';
if (!empty($_POST['nombre']) && !empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin']) && !empty($_POST['categoria']) && !empty($_POST['importancia'])) {
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $responsables = '';
    $stmt = $conn->prepare("INSERT INTO proyectos (nombre, fecha_inicio, fecha_fin, categoria, descripcion, importancia, responsables) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $_POST['nombre'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['categoria'], $descripcion, $_POST['importancia'], $responsables);
    $stmt->execute();
}
header('Location: index.php');