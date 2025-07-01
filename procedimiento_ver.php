<?php
include 'db.php';
$id = intval($_GET['id']);
$proc = $conn->query("SELECT * FROM procedimientos WHERE id=$id")->fetch_assoc();
$resP = $conn->query("SELECT * FROM procedimientos_pasos WHERE procedimiento_id=$id ORDER BY orden ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($proc['nombre']) ?> - Procedimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-2xl mx-auto my-8 bg-white rounded shadow p-6">
    <a href="procedimientos.php" class="text-blue-600 underline">&larr; Volver</a>
    <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($proc['nombre']) ?></h1>
    <div class="mb-2"><span class="font-semibold">Descripción:</span> <?= htmlspecialchars($proc['descripcion']) ?></div>
    <div class="mb-4"><span class="font-semibold">Categoría:</span> <?= htmlspecialchars($proc['categoria']) ?></div>
    <div class="mb-2 font-semibold">Pasos:</div>
    <ol class="list-decimal list-inside">
        <?php while($p = $resP->fetch_assoc()): ?>
    <li class="mb-2 bg-blue-50 rounded p-2">
        <?= nl2br(htmlspecialchars($p['texto'])) ?>
        <?php if ($p['imagen']): ?>
            <div><img src="uploads/<?= htmlspecialchars($p['imagen']) ?>" class="max-h-32 rounded shadow mt-2" alt="Imagen paso"></div>
        <?php endif; ?>
    </li>
<?php endwhile; ?>
    </ol>
</div>
</body>
</html>
