<?php
include 'db.php';
if (!empty($_POST['id'])) {
    $stmt = $conn->prepare("UPDATE conexiones SET ip=?, hostname=?, usuario=?, contrasena=?, tipo=?, observacion=? WHERE id=?");
    $stmt->bind_param("ssssssi",
        $_POST['ip'], $_POST['hostname'], $_POST['usuario'], $_POST['contrasena'], $_POST['tipo'], $_POST['observacion'], $_POST['id']
    );
    $stmt->execute();
}
header('Location: bodega.php');
