<?php
include 'db.php';
if (!empty($_POST['id'])) {
    $stmt = $conn->prepare("DELETE FROM conexiones WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
}
header('Location: bodega.php');