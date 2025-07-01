<?php
include 'db.php';
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM hitos WHERE id=$id");
}
header('Location: index.php');