<?php
include 'db.php';
if (!empty($_POST['id']) && !empty($_POST['nombre']) && !empty($_POST['estado'])) {
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $estado = $_POST['estado'];
    $conn->query("UPDATE hitos SET nombre='$nombre', estado='$estado' WHERE id=$id");
}
header('Location: index.php');