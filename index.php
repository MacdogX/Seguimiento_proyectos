<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento de Proyectos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .chip {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 9999px;
            padding: 2px 10px;
            margin-right: 4px;
            margin-bottom: 3px;
            font-size: 14px;
        }
        .chip-remove {
            cursor: pointer;
            margin-left: 4px;
            color: #dc2626;
            font-weight: bold;
        }
        .chip-remove:hover {
            color: #b91c1c;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Menú superior -->
<nav class="bg-white shadow mb-6">
    <div class="container mx-auto flex justify-between items-center p-4">
        <a href="index.php" class="font-bold text-xl text-blue-700">Backlog de trabajo</a>
        <div>
            <a href="?estadistica=1" class="text-blue-600 hover:underline px-4">Estadística</a>
            <a href="bodega.php" class="text-blue-600 hover:underline px-4">Bodega de Conexiones</a>
            <a href="procedimientos.php" class="text-blue-600 hover:underline px-4">Procedimientos</a>
        </div>
    </div>
</nav>
<div class="container mx-auto p-4">

    <!-- Estadísticas -->
 <?php if(isset($_GET['estadistica'])): ?>
    <?php
    $total = $conn->query("SELECT COUNT(*) AS c FROM proyectos")->fetch_assoc()['c'];
    $terminados = $conn->query("SELECT COUNT(*) AS c FROM proyectos WHERE estado='terminado'")->fetch_assoc()['c'];
    $enprogreso = $conn->query("SELECT COUNT(*) AS c FROM proyectos WHERE estado='en progreso'")->fetch_assoc()['c'];
    $cat = $conn->query("SELECT categoria, COUNT(*) AS c FROM proyectos GROUP BY categoria");
    $categorias = [];
    while($rowc = $cat->fetch_assoc()) {
        $categorias[$rowc['categoria']] = $rowc['c'];
    }
    // Para las gráficas
    $proyectos_estado = [
        "En progreso" => $enprogreso,
        "Terminado" => $terminados,
    ];
    $impAlta = $conn->query("SELECT COUNT(*) AS c FROM proyectos WHERE importancia='Alta'")->fetch_assoc()['c'];
    $impMedia = $conn->query("SELECT COUNT(*) AS c FROM proyectos WHERE importancia='Media'")->fetch_assoc()['c'];
    $impBaja = $conn->query("SELECT COUNT(*) AS c FROM proyectos WHERE importancia='Baja'")->fetch_assoc()['c'];
    $proyectos_importancia = [
        "Alta" => $impAlta,
        "Media" => $impMedia,
        "Baja" => $impBaja
    ];
    ?>
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <h2 class="text-2xl font-bold mb-2 text-blue-800">Estadística general</h2>
        <div class="flex flex-col md:flex-row gap-6">
            <div>
                <div class="font-semibold">Total proyectos:</div>
                <div class="text-3xl"><?= $total ?></div>
            </div>
            <div>
                <div class="font-semibold">Terminados:</div>
                <div class="text-3xl text-green-600"><?= $terminados ?></div>
            </div>
            <div>
                <div class="font-semibold">En progreso:</div>
                <div class="text-3xl text-yellow-600"><?= $enprogreso ?></div>
            </div>
            <div>
                <div class="font-semibold">Por categoría:</div>
                <ul>
                    <?php foreach($categorias as $catn => $catv): ?>
                        <li class="ml-2"><?= htmlspecialchars($catn) ?>: <span class="font-semibold"><?= $catv ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-bold mb-2">Proyectos por estado</h3>
                <canvas id="graficaEstado"></canvas>
            </div>
            <div>
                <h3 class="font-bold mb-2">Proyectos por importancia</h3>
                <canvas id="graficaImportancia"></canvas>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfica de estado
        const ctx1 = document.getElementById('graficaEstado').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($proyectos_estado)) ?>,
                datasets: [{
                    label: 'Cantidad de proyectos',
                    data: <?= json_encode(array_values($proyectos_estado)) ?>,
                    backgroundColor: ['#60a5fa', '#34d399']
                }]
            },
            options: {
                plugins: { legend: { display: false }},
                responsive: true,
                scales: { y: { beginAtZero: true, precision:0 } }
            }
        });

        // Gráfica de importancia
        const ctx2 = document.getElementById('graficaImportancia').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($proyectos_importancia)) ?>,
                datasets: [{
                    label: 'Cantidad de proyectos',
                    data: <?= json_encode(array_values($proyectos_importancia)) ?>,
                    backgroundColor: ['#ef4444','#f59e42','#22c55e']
                }]
            },
            options: {
                plugins: { legend: { display: false }},
                responsive: true,
                scales: { y: { beginAtZero: true, precision:0 } }
            }
        });
    });
    </script>
<?php endif; ?>
    <!-- Filtro por estado -->
    <form method="GET" class="mb-4 flex items-center gap-2">
        <label class="font-semibold">Filtrar por estado:</label>
        <select name="estado" onchange="this.form.submit()" class="border p-2 rounded">
            <option value="">Todos</option>
            <option value="en progreso" <?= (isset($_GET['estado']) && $_GET['estado']=='en progreso')?'selected':'' ?>>En progreso</option>
            <option value="terminado" <?= (isset($_GET['estado']) && $_GET['estado']=='terminado')?'selected':'' ?>>Terminado</option>
        </select>
        <?php if(isset($_GET['estado']) && $_GET['estado']): ?>
            <a href="index.php" class="text-blue-700 underline ml-2">Limpiar filtro</a>
        <?php endif; ?>
    </form>

    <!-- Nuevo Proyecto -->
    <form method="POST" action="nuevo_proyecto.php" class="bg-white p-4 rounded shadow mb-6 flex flex-col md:flex-row gap-2 flex-wrap">
        <input type="text" name="nombre" class="border p-2 rounded flex-1" placeholder="Nombre del Proyecto" required>
        <input type="text" name="descripcion" class="border p-2 rounded flex-1" placeholder="Descripción del Proyecto">
        <select name="categoria" class="border p-2 rounded" required>
            <option value="Asistencial">Asistencial</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Infraestructura">Infraestructura</option>
            <option value="Tercero">Tercero</option>
            <option value="Aplicaciones">Aplicaciones</option>
        </select>
        <select name="importancia" class="border p-2 rounded" required>
            <option value="Baja">Baja</option>
            <option value="Media" selected>Media</option>
            <option value="Alta">Alta</option>
        </select>
        <input type="date" name="fecha_inicio" class="border p-2 rounded" required>
        <input type="date" name="fecha_fin" class="border p-2 rounded" required>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Agregar Proyecto</button>
    </form>

    <!-- Lista de Proyectos -->
    <?php
    // Filtro por estado
    $estadoFiltro = isset($_GET['estado']) && $_GET['estado'] ? $_GET['estado'] : '';
    if ($estadoFiltro) {
        $stmt = $conn->prepare("SELECT * FROM proyectos WHERE estado=? ORDER BY id DESC");
        $stmt->bind_param("s", $estadoFiltro);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query("SELECT * FROM proyectos ORDER BY id DESC");
    }

    while($row = $res->fetch_assoc()):
        $idp = $row['id'];
        $resHitos = $conn->query("SELECT * FROM hitos WHERE id_proyecto=$idp");
        $hitos = [];
        while ($h = $resHitos->fetch_assoc()) $hitos[] = $h;

        $inicio = new DateTime($row['fecha_inicio']);
        $fin = new DateTime($row['fecha_fin']);
        $hoy = new DateTime();
        $dias_total = $inicio->diff($fin)->days;
        $dias_trans = max(0, $inicio->diff($hoy)->days);
        $dias_rest = max(0, $fin->diff($hoy)->invert ? $fin->diff($hoy)->days : 0);

        // Responsables como arreglo
        $responsables = array_filter(array_map('trim', explode(',', $row['responsables'])));
    ?>
    <div class="bg-white p-4 rounded shadow mb-6">
        <div class="flex justify-between items-center">
            <div class="w-full">
                <!-- Nombre de proyecto con lápiz y edición -->
                <div id="nombre-show-<?= $idp ?>" class="mb-1 flex items-center gap-2">
                    <span class="text-xl font-bold text-black"><?= htmlspecialchars($row['nombre']) ?></span>
                    <button onclick="editarNombreProyecto(<?= $idp ?>, '<?= htmlspecialchars(addslashes($row['nombre'])) ?>')" class="inline-block text-gray-700 hover:text-blue-800" title="Editar nombre" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 019 17H5v-4a2 2 0 012-2h2z" />
                        </svg>
                    </button>
                </div>
                <!-- Formulario oculto para editar nombre -->
                <div id="nombre-edit-<?= $idp ?>" class="mb-1 hidden">
                    <form method="POST" action="actualizar_nombre_proyecto.php" class="flex items-center gap-2">
                        <input type="hidden" name="id" value="<?= $idp ?>">
                        <input type="text" name="nombre" id="nombre-input-<?= $idp ?>" class="border p-1 rounded text-xl flex-1" value="<?= htmlspecialchars($row['nombre']) ?>" required>
                        <button class="bg-blue-500 text-white px-2 py-1 rounded" title="Guardar">Guardar</button>
                        <button type="button" onclick="cancelarEdicionNombreProyecto(<?= $idp ?>)" class="bg-gray-400 text-white px-2 py-1 rounded" title="Cancelar">Cancelar</button>
                    </form>
                </div>

                <div class="text-sm text-gray-600">Categoría: <span class="font-semibold"><?= htmlspecialchars($row['categoria']) ?></span></div>
                <div class="text-sm text-pink-600 mb-1">
                    Importancia: 
                    <?php if($row['importancia']=='Alta'): ?>
                        <span class="font-bold bg-red-200 px-2 py-1 rounded">Alta</span>
                    <?php elseif($row['importancia']=='Media'): ?>
                        <span class="font-bold bg-yellow-100 px-2 py-1 rounded">Media</span>
                    <?php else: ?>
                        <span class="font-bold bg-green-100 px-2 py-1 rounded">Baja</span>
                    <?php endif; ?>
                </div>
                <?php if(!empty($row['descripcion'])): ?>
                    <div class="text-sm text-gray-800 italic mb-1">Descripción: <?= htmlspecialchars($row['descripcion']) ?></div>
                <?php endif; ?>
                
                <!-- Responsables visual y edición tipo chips -->
                <div class="mb-2">
                    <span class="text-sm text-blue-800 font-semibold">Responsables:</span>
                    <div id="chips-<?= $idp ?>" class="inline">
                        <?php foreach($responsables as $r): ?>
                            <span class="chip"><?= htmlspecialchars($r) ?>
                                <span class="chip-remove" onclick="eliminarResponsable(<?= $idp ?>, '<?= htmlspecialchars(addslashes($r)) ?>')" title="Eliminar responsable">&times;</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <button onclick="mostrarAgregarResponsable(<?= $idp ?>)" class="inline-block ml-2 text-blue-600 hover:text-blue-800" title="Agregar responsable" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <!-- Formulario oculto para agregar responsable -->
                <div id="agregar-responsable-<?= $idp ?>" class="mb-2 hidden">
                    <form method="POST" action="actualizar_responsables.php" class="flex items-center gap-2" onsubmit="return agregarResponsableSubmit(<?= $idp ?>);">
                        <input type="hidden" name="id" value="<?= $idp ?>">
                        <input type="hidden" name="responsables" id="responsables-hidden-<?= $idp ?>" value="<?= htmlspecialchars($row['responsables']) ?>">
                        <input type="text" id="nuevo-responsable-<?= $idp ?>" class="border p-1 rounded flex-1" placeholder="Nuevo responsable">
                        <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded" title="Agregar">Agregar</button>
                        <button type="button" onclick="cancelarAgregarResponsable(<?= $idp ?>)" class="bg-gray-400 text-white px-2 py-1 rounded" title="Cancelar">Cancelar</button>
                    </form>
                </div>

                <div class="text-sm text-gray-500">Inicio: <?= $row['fecha_inicio'] ?> | Fin: <?= $row['fecha_fin'] ?></div>
                <div class="mt-1">
                    <span class="font-semibold">Días totales:</span> <?= $dias_total ?> |
                    <span class="font-semibold">Transcurridos:</span> <?= $dias_trans ?> |
                    <span class="font-semibold">Restantes:</span> <?= $dias_rest ?>
                </div>
                <div class="mt-2">
                    Estado: 
                    <?php if($row['estado']=='terminado'): ?>
                        <span class="px-2 py-1 bg-green-300 text-green-900 rounded">Terminado</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-yellow-200 text-yellow-900 rounded">En progreso</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex flex-col gap-1 min-w-max ml-4">
                <?php if($row['estado']!='terminado'): ?>
                    <form method="POST" action="terminar_proyecto.php" onsubmit="return confirm('¿Marcar este proyecto como terminado?');">
                        <input type="hidden" name="id" value="<?= $idp ?>">
                        <button class="bg-green-400 px-2 py-1 rounded text-white hover:bg-green-600 mb-1">Terminado</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="eliminar_proyecto.php" onsubmit="return confirm('¿Eliminar proyecto?');">
                    <input type="hidden" name="id" value="<?= $idp ?>">
                    <button class="bg-red-400 px-2 py-1 rounded text-white hover:bg-red-600">Eliminar</button>
                </form>
            </div>
        </div>
        <div class="mt-4">
            <div class="font-semibold mb-1">Hitos / Items:</div>
            <?php foreach($hitos as $h): ?>
                <form method="POST" action="actualizar_hito.php" class="flex items-center gap-2 mb-1">
                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                    <input type="text" name="nombre" class="border p-1 rounded flex-1" value="<?= htmlspecialchars($h['nombre']) ?>" required>
                    <select name="estado" class="border p-1 rounded">
                        <option value="pendiente" <?= $h['estado']=='pendiente'?'selected':'' ?>>Pendiente</option>
                        <option value="progreso" <?= $h['estado']=='progreso'?'selected':'' ?>>En progreso</option>
                        <option value="terminado" <?= $h['estado']=='terminado'?'selected':'' ?>>Terminado</option>
                    </select>
                    <button class="bg-blue-400 px-2 rounded text-white">Actualizar</button>
                    <button type="submit" formaction="eliminar_hito.php" class="text-red-600 bg-transparent">🗑️</button>
                </form>
            <?php endforeach; ?>
            <!-- Nuevo hito -->
            <form method="POST" action="nuevo_hito.php" class="flex gap-2 mt-2">
                <input type="hidden" name="id_proyecto" value="<?= $idp ?>">
                <input type="text" name="nombre" class="border p-1 rounded flex-1" placeholder="Nuevo hito/item" required>
                <button class="bg-green-500 text-white px-2 rounded">Agregar</button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script>
// Edición nombre proyecto (con lápiz)
function editarNombreProyecto(id, nombreActual) {
    document.getElementById('nombre-show-' + id).style.display = 'none';
    document.getElementById('nombre-edit-' + id).style.display = 'block';
    document.getElementById('nombre-input-' + id).focus();
}
function cancelarEdicionNombreProyecto(id) {
    document.getElementById('nombre-edit-' + id).style.display = 'none';
    document.getElementById('nombre-show-' + id).style.display = 'flex';
}

// Edición responsables tipo chips
function mostrarAgregarResponsable(id) {
    document.getElementById('agregar-responsable-' + id).style.display = 'block';
    document.getElementById('nuevo-responsable-' + id).focus();
}
function cancelarAgregarResponsable(id) {
    document.getElementById('agregar-responsable-' + id).style.display = 'none';
    document.getElementById('nuevo-responsable-' + id).value = '';
}
function agregarResponsableSubmit(id) {
    let input = document.getElementById('nuevo-responsable-' + id);
    let value = input.value.trim();
    if (value === '') {
        alert('Ingresa un nombre para el responsable.');
        return false;
    }
    // Agregar responsable al hidden input
    let hidden = document.getElementById('responsables-hidden-' + id);
    let actuales = hidden.value ? hidden.value.split(',').map(e=>e.trim()).filter(e=>e) : [];
    if (actuales.includes(value)) {
        alert('Ese responsable ya está en la lista.');
        return false;
    }
    actuales.push(value);
    hidden.value = actuales.join(', ');
    return true; // Se envía el formulario normal (POST)
}
function eliminarResponsable(id, nombre) {
    if (!confirm("¿Deseas quitar a '" + nombre + "' de los responsables?")) return;
    // Hacemos un POST oculto a actualizar_responsables.php con la nueva lista
    let chipsDiv = document.getElementById('chips-' + id);
    let chips = chipsDiv.querySelectorAll('.chip');
    let nuevos = [];
    chips.forEach(function(chip) {
        let text = chip.childNodes[0].nodeValue.trim();
        if (text !== nombre) nuevos.push(text);
    });
    // Preparamos un form oculto y lo enviamos
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'actualizar_responsables.php';
    form.innerHTML = '<input type="hidden" name="id" value="'+id+'">' +
                     '<input type="hidden" name="responsables" value="'+nuevos.join(', ')+'">';
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>