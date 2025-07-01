<?php
include 'db.php';
if (!empty($_POST['id']) && !empty($_POST['nombre'])) {
    $id = intval($_POST['id']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $conn->query("UPDATE proyectos SET nombre='$nombre' WHERE id=$id");
}
header('Location: index.php');