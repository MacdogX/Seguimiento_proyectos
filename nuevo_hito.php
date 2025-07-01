<?php
include 'db.php';
if (!empty($_POST['id_proyecto']) && !empty($_POST['nombre'])) {
    $idp = intval($_POST['id_proyecto']);
    $nombre = $_POST['nombre'];
    $conn->query("INSERT INTO hitos (id_proyecto, nombre) VALUES ($idp, '$nombre')");
}
header('Location: index.php');