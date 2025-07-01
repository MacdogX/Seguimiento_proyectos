<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bodega de Conexiones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px 6px;
        }
        .icon-btn:focus { outline: none; }
    </style>
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow mb-6">
    <div class="container mx-auto flex justify-between items-center p-4">
        <a href="index.php" class="font-bold text-xl text-blue-700">← Volver a Proyectos</a>
        <span class="text-lg font-bold text-blue-900">Bodega de Conexiones</span>
    </div>
</nav>

<div class="container mx-auto p-4">

    <!-- Agregar conexión -->
    <form method="POST" action="nueva_conexion.php" class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-2 gap-2">
        <input type="text" name="ip" class="border p-2 rounded" placeholder="IP" required>
        <input type="text" name="hostname" class="border p-2 rounded" placeholder="Hostname" required>
        <input type="text" name="usuario" class="border p-2 rounded" placeholder="Usuario" required>
        <input type="text" name="contrasena" class="border p-2 rounded" placeholder="Contraseña" required>
        <select name="tipo" class="border p-2 rounded" required>
            <option value="Base de datos">Base de datos</option>
            <option value="RDP">RDP</option>
            <option value="Aplicacion">Aplicación</option>
            <option value="Otro">Otro</option>
        </select>
        <input type="text" name="observacion" class="border p-2 rounded col-span-2" placeholder="Observación">
        <div class="col-span-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 w-full md:w-auto">Agregar Conexión</button>
        </div>
    </form>

    <!-- Filtro de búsqueda -->
    <div class="mb-4">
        <input type="text" id="buscador" class="border p-2 rounded w-full md:w-1/2" placeholder="Buscar conexión por IP, Hostname, Usuario, Observación...">
    </div>

    <!-- Lista de conexiones en grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="lista-conexiones">
        <?php
        $res = $conn->query("SELECT * FROM conexiones ORDER BY id DESC");
        while($row = $res->fetch_assoc()):
            // Elige el ícono según tipo
            $icono = '';
            if($row['tipo']=='Base de datos'){
                $icono = '<i data-lucide="database" class="w-7 h-7 text-blue-500"></i>';
            } elseif($row['tipo']=='RDP'){
                $icono = '<i data-lucide="monitor" class="w-7 h-7 text-teal-500"></i>';
            } elseif($row['tipo']=='Aplicacion'){
                $icono = '<i data-lucide="app-window" class="w-7 h-7 text-violet-500"></i>';
            } else {
                $icono = '<i data-lucide="tag" class="w-7 h-7 text-gray-400"></i>';
            }
        ?>
        <div class="relative bg-white p-4 rounded-xl shadow hover:shadow-lg transition flex flex-col gap-2 card-conexion"
             data-ip="<?= htmlspecialchars($row['ip']) ?>"
             data-hostname="<?= htmlspecialchars($row['hostname']) ?>"
             data-usuario="<?= htmlspecialchars($row['usuario']) ?>"
             data-contrasena="<?= htmlspecialchars($row['contrasena']) ?>"
             data-tipo="<?= htmlspecialchars($row['tipo']) ?>"
             data-observacion="<?= htmlspecialchars($row['observacion']) ?>"
        >
            <!-- Icono en la esquina superior derecha -->
            <div class="absolute right-4 top-4 z-10 opacity-80">
                <?= $icono ?>
            </div>

            <!-- Vista -->
          <!-- Vista -->
<div class="conexion-vista" id="vista-<?= $row['id'] ?>">
    <div><b>IP:</b> <?= htmlspecialchars($row['ip']) ?></div>
    <div><b>Hostname:</b> <?= htmlspecialchars($row['hostname']) ?></div>
    <div><b>Usuario:</b> <?= htmlspecialchars($row['usuario']) ?></div>
    <div><b>Contraseña:</b> <?= htmlspecialchars($row['contrasena']) ?></div>
    <div><b>Tipo:</b> <?= htmlspecialchars($row['tipo']) ?></div>
    <?php if($row['observacion']): ?>
        <div><b>Observación:</b> <?= htmlspecialchars($row['observacion']) ?></div>
    <?php endif; ?>
    <div class="flex gap-2 mt-2">
        <button class="icon-btn text-blue-700" title="Editar" onclick="editarCard(<?= $row['id'] ?>)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 019 17H5v-4a2 2 0 012-2h2z" />
            </svg>
        </button>
        <!-- Eliminar (form POST para seguridad) -->
        <form method="POST" action="eliminar_conexion.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta conexión?');" class="inline">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" class="icon-btn text-red-500" title="Eliminar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </form>
    </div>
</div>
<!-- Formulario de edición oculto -->
<form method="POST" action="actualizar_conexion.php" class="conexion-editar hidden" id="editar-<?= $row['id'] ?>">
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <div class="grid grid-cols-1 gap-2">
        <input type="text" name="ip" class="border p-2 rounded" value="<?= htmlspecialchars($row['ip']) ?>" required>
        <input type="text" name="hostname" class="border p-2 rounded" value="<?= htmlspecialchars($row['hostname']) ?>" required>
        <input type="text" name="usuario" class="border p-2 rounded" value="<?= htmlspecialchars($row['usuario']) ?>" required>
        <input type="text" name="contrasena" class="border p-2 rounded" value="<?= htmlspecialchars($row['contrasena']) ?>" required>
        <select name="tipo" class="border p-2 rounded" required>
            <option value="Base de datos" <?= $row['tipo']=='Base de datos'?'selected':'' ?>>Base de datos</option>
            <option value="RDP" <?= $row['tipo']=='RDP'?'selected':'' ?>>RDP</option>
            <option value="Aplicacion" <?= $row['tipo']=='Aplicacion'?'selected':'' ?>>Aplicación</option>
            <option value="Otro" <?= $row['tipo']=='Otro'?'selected':'' ?>>Otro</option>
        </select>
        <input type="text" name="observacion" class="border p-2 rounded" value="<?= htmlspecialchars($row['observacion']) ?>">
    </div>
    <div class="mt-2 flex gap-2">
        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">Guardar</button>
        <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded" onclick="cancelarEdicion(<?= $row['id'] ?>)">Cancelar</button>
        <!-- Eliminar también en modo edición -->
       
    </div>
</form>
            <!-- Formulario de edición oculto -->
            <form method="POST" action="actualizar_conexion.php" class="conexion-editar hidden" id="editar-<?= $row['id'] ?>">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <div class="grid grid-cols-1 gap-2">
                    <input type="text" name="ip" class="border p-2 rounded" value="<?= htmlspecialchars($row['ip']) ?>" required>
                    <input type="text" name="hostname" class="border p-2 rounded" value="<?= htmlspecialchars($row['hostname']) ?>" required>
                    <input type="text" name="usuario" class="border p-2 rounded" value="<?= htmlspecialchars($row['usuario']) ?>" required>
                    <input type="text" name="contrasena" class="border p-2 rounded" value="<?= htmlspecialchars($row['contrasena']) ?>" required>
                    <select name="tipo" class="border p-2 rounded" required>
                        <option value="Base de datos" <?= $row['tipo']=='Base de datos'?'selected':'' ?>>Base de datos</option>
                        <option value="RDP" <?= $row['tipo']=='RDP'?'selected':'' ?>>RDP</option>
                        <option value="Aplicacion" <?= $row['tipo']=='Aplicacion'?'selected':'' ?>>Aplicación</option>
                        <option value="Otro" <?= $row['tipo']=='Otro'?'selected':'' ?>>Otro</option>
                    </select>
                    <input type="text" name="observacion" class="border p-2 rounded" value="<?= htmlspecialchars($row['observacion']) ?>">
                </div>
                <div class="mt-2 flex gap-2">
                    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">Guardar</button>
                    <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded" onclick="cancelarEdicion(<?= $row['id'] ?>)">Cancelar</button>
                </div>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
lucide.createIcons(); // Renderiza los íconos

// Edición dinámica de cards
function editarCard(id) {
    document.getElementById('vista-' + id).classList.add('hidden');
    document.getElementById('editar-' + id).classList.remove('hidden');
}
function cancelarEdicion(id) {
    document.getElementById('editar-' + id).classList.add('hidden');
    document.getElementById('vista-' + id).classList.remove('hidden');
}

// Filtro/búsqueda rápida en vivo
document.getElementById('buscador').addEventListener('input', function() {
    let texto = this.value.trim().toLowerCase();
    document.querySelectorAll('.card-conexion').forEach(function(card){
        let match = false;
        Array.from(card.attributes).forEach(function(attr){
            if(attr.name.startsWith('data-') && attr.value.toLowerCase().includes(texto)) match = true;
        });
        card.style.display = (texto=='' || match) ? '' : 'none';
    });
});
</script>
</body>
</html>
