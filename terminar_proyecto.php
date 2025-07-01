<?php
include 'db.php';
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE proyectos SET estado='terminado' WHERE id=$id");
}
header('Location: index.php');