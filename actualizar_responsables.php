<?php
include 'db.php';
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $responsables = $conn->real_escape_string($_POST['responsables']);
    $conn->query("UPDATE proyectos SET responsables='$responsables' WHERE id=$id");
}
header('Location: index.php');