<?php
include "db.php";
$id = intval($_GET['id'] ?? 0);

$res_proc = $conn->query("SELECT * FROM procedimientos WHERE id=$id");
$proc = $res_proc ? $res_proc->fetch_assoc() : null;

$pasos = [];
$res = $conn->query("SELECT texto, imagen FROM procedimientos_pasos WHERE procedimiento_id=$id ORDER BY orden ASC, id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $pasos[] = $r;
}
header('Content-Type: application/json');
if ($proc) {
    echo json_encode([
        'id' => $proc['id'],
        'nombre' => $proc['nombre'],
        'descripcion' => $proc['descripcion'],
        'categoria' => $proc['categoria'],
        'pasos' => $pasos
    ]);
} else {
    echo json_encode(['error' => 'Procedimiento no encontrado', 'pasos' => []]);
}
