<?php
include "db.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---- 1. Recoger datos principales del procedimiento ----
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

if(!$nombre || !$descripcion || !$categoria){
    die("Todos los campos son obligatorios.");
}

// ---- 2. Insertar o actualizar el procedimiento ----
if($id) {
    // Actualizar
    $stmt = $conn->prepare("UPDATE procedimientos SET nombre=?, descripcion=?, categoria=? WHERE id=?");
    if (!$stmt) die("Error en prepare update: ".$conn->error);
    $stmt->bind_param("sssi", $nombre, $descripcion, $categoria, $id);
    if(!$stmt->execute()) die("Error al actualizar: ".$stmt->error);
    $proc_id = $id;
} else {
    // Crear nuevo
    $stmt = $conn->prepare("INSERT INTO procedimientos (nombre, descripcion, categoria, fecha_creacion) VALUES (?, ?, ?, NOW())");
    if (!$stmt) die("Error en prepare insert: ".$conn->error);
    $stmt->bind_param("sss", $nombre, $descripcion, $categoria);
    if(!$stmt->execute()) die("Error al insertar: ".$stmt->error);
    $proc_id = $conn->insert_id;
}

// ---- 3. Pasos: limpiar y volver a insertar todos los pasos ----
$conn->query("DELETE FROM procedimientos_pasos WHERE procedimiento_id=$proc_id");

// Recoge arrays de texto e imagen actual para los pasos
$pasos_texto = $_POST['pasos_texto'] ?? [];
$pasos_imagen_actual = $_POST['pasos_imagen_actual'] ?? [];
$eliminar_imagen = $_POST['eliminar_imagen'] ?? [];
$total_pasos = count($pasos_texto);

// Manejar archivos subidos
$pasos_imagen_files = $_FILES['pasos_imagen'] ?? null;

for($i=0; $i<$total_pasos; $i++) {
    $texto = trim($pasos_texto[$i]);
    $nombreImagen = '';

    // ---- Lógica de imágenes para cada paso ----
    // 1. Si pidió eliminar imagen
    if (isset($eliminar_imagen[$i]) && $eliminar_imagen[$i]) {
        $nombreImagen = '';
    }
    // 2. Si se sube una nueva imagen
    elseif ($pasos_imagen_files && isset($pasos_imagen_files['name'][$i]) && $pasos_imagen_files['name'][$i]) {
        $ext = strtolower(pathinfo($pasos_imagen_files['name'][$i], PATHINFO_EXTENSION));
        $nombreImagen = "paso_" . uniqid() . "." . $ext;
        move_uploaded_file($pasos_imagen_files['tmp_name'][$i], __DIR__ . "/uploads/" . $nombreImagen);
    }
    // 3. Si ya había imagen y no se eliminó ni reemplazó
    elseif (isset($pasos_imagen_actual[$i]) && $pasos_imagen_actual[$i]) {
        $nombreImagen = $pasos_imagen_actual[$i];
    }

    // ---- Guardar paso ----
    $sqlPaso = "INSERT INTO procedimientos_pasos (procedimiento_id, orden, texto, imagen) VALUES (?, ?, ?, ?)";
    $stmtPaso = $conn->prepare($sqlPaso);
    if (!$stmtPaso) die("Error en prepare paso: " . $conn->error . " - SQL: " . $sqlPaso);
    $stmtPaso->bind_param('iiss', $proc_id, $i, $texto, $nombreImagen);
    if (!$stmtPaso->execute()) die("Error al guardar paso: ".$stmtPaso->error);
    $stmtPaso->close();
}

// ---- 4. Redireccionar de vuelta a la lista ----
header("Location: procedimientos.php");
exit;
?>
