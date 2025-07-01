<?php
include 'db.php';
if (!empty($_POST['ip']) && !empty($_POST['hostname']) && !empty($_POST['usuario']) && !empty($_POST['contrasena']) && !empty($_POST['tipo'])) {
    $stmt = $conn->prepare("INSERT INTO conexiones (ip, hostname, usuario, contrasena, tipo, observacion) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss",
        $_POST['ip'], $_POST['hostname'], $_POST['usuario'], $_POST['contrasena'], $_POST['tipo'], $_POST['observacion']
    );
    $stmt->execute();
}
header('Location: bodega.php');
