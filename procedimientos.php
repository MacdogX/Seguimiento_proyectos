<?php
include "db.php";

// Listar procedimientos
$procs = $conn->query("SELECT * FROM procedimientos ORDER BY fecha_creacion DESC, id DESC");
$procedimientos = [];
while($p = $procs->fetch_assoc()) $procedimientos[] = $p;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procedimientos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jsPDF para exportar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- NAV -->
<nav class="w-full bg-white shadow-sm flex justify-between items-center px-8 py-3 mb-6">
    <a href="index.php" class="text-blue-700 font-bold hover:underline">&larr; Volver a Proyectos</a>
    <span class="font-semibold text-blue-900 text-lg">Procedimientos</span>
    <a href="bodega.php" class="text-blue-700 font-bold hover:underline">Bodega de conexiones</a>
</nav>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-5">
      <button id="btn-nuevo" onclick="abrirModalCrear()" class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 mr-2 shadow transition">
    + Crear procedimiento
</button>
        <input id="searchInput" type="text" class="ml-auto border rounded px-3 py-2 text-sm w-64" placeholder="Buscar...">
    </div>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table id="tablaProc" class="w-full text-sm">
            <thead class="bg-blue-100">
                <tr>
                    <th class="p-3 text-left font-semibold text-gray-700">Nombre</th>
                    <th class="p-3 text-left font-semibold text-gray-700">Descripción</th>
                    <th class="p-3 text-left font-semibold text-gray-700">Categoría</th>
                    <th class="p-3 text-left font-semibold text-gray-700">Fecha</th>
                    <th class="p-3 text-center font-semibold text-gray-700">Acciones</th>
                </tr>
            </thead>
          <tbody>
    <?php foreach($procedimientos as $proc): ?>
    <tr class="hover:bg-blue-50 transition">
        <td class="p-3 font-semibold text-gray-900"><?= htmlspecialchars($proc['nombre']) ?></td>
        <td class="p-3 text-gray-700"><?= htmlspecialchars($proc['descripcion']) ?></td>
        <td class="p-3 text-gray-700"><?= htmlspecialchars($proc['categoria']) ?></td>
        <td class="p-3 text-gray-500"><?= substr($proc['fecha_creacion'],0,10) ?></td>
        <td class="p-3 text-center">
            <div class="flex justify-center gap-2">
                <!-- Ver -->
                <a href="procedimiento_ver.php?id=<?= $proc['id'] ?>"
                   class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-xs font-semibold"
                   title="Ver">Ver</a>
                <!-- Editar -->
                <button onclick="abrirModalEditar(<?= $proc['id'] ?>)"
                        class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition text-xs font-semibold"
                        title="Editar">Editar</button>
                <!-- PDF -->
                <button onclick='generarPDF(<?= $proc["id"] ?>, <?= json_encode($proc["nombre"]) ?>)'
                        class="px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-xs font-semibold flex items-center gap-1"
                        title="PDF">
                    <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="2" d="M12 20h9" /><path stroke-width="2" d="M12 4v16m0 0H3" /></svg>
                    PDF
                </button>
                <!-- Eliminar -->
                <button onclick="confirmarEliminarProcedimiento(<?= $proc['id'] ?>)"
                        class="px-2 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-xs font-semibold"
                        title="Eliminar">Eliminar</button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>
</div>

<!-- MODAL EDITAR (scrollable y elegante) -->
<div id="modal-editar" class="fixed inset-0 z-50 bg-black bg-opacity-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
        <button onclick="cerrarModalEditar()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 id="modal-editar-titulo" class="text-2xl font-bold mb-3">Editar procedimiento</h2>
        <form id="form-editar-proc" method="post" action="procedimiento_guardar.php" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="id" id="editar-id">
            <div class="mb-3">
                <label class="block font-semibold text-gray-800">Nombre:</label>
                <input type="text" id="editar-nombre" name="nombre" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div class="mb-3">
                <label class="block font-semibold text-gray-800">Descripción:</label>
                <textarea id="editar-desc" name="descripcion" class="border rounded px-3 py-2 w-full" required></textarea>
            </div>
            <div class="mb-3">
                <label class="block font-semibold text-gray-800">Categoría:</label>
                <select id="editar-cat-select" name="categoria" class="border rounded px-3 py-2 w-full" required>
                    <option value="">Seleccionar...</option>
                    <option>Asistencial</option>
                    <option>Financiero</option>
                    <option>Tecnologías de la información</option>
                    <option>Seguridad</option>
                    <option>Otros</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-semibold text-gray-800 mb-1">Pasos:</label>
                <div id="pasos-lista"></div>
                <button type="button" onclick="agregarPasoEditar()" class="mt-2 px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition text-xs font-semibold">+ Agregar paso</button>
            </div>
            <div class="flex justify-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 font-semibold">Guardar</button>
                <button type="button" onclick="cerrarModalEditar()" class="bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 font-semibold">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal eliminar (básico) -->
<div id="modal-eliminar" class="fixed inset-0 z-50 bg-black bg-opacity-30 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <h3 class="text-lg font-bold mb-4">¿Eliminar procedimiento?</h3>
        <form method="POST" action="procedimiento_eliminar.php">
            <input type="hidden" name="id" id="id-eliminar">
            <div class="flex justify-end gap-3">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold">Sí, eliminar</button>
                <button type="button" onclick="cerrarModalEliminar()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 font-semibold">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- Buscador rápido ---
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', () => {
    const val = searchInput.value.toLowerCase();
    document.querySelectorAll('#tablaProc tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
function abrirModalCrear() {
    document.getElementById('modal-editar-titulo').innerText = 'Nuevo procedimiento';
    document.getElementById('form-editar-proc').reset();
    document.getElementById('editar-id').value = '';
    pasosActuales = []; // <--- AQUÍ, array vacío, ¡NO un paso inicial!
    renderPasosEditar();
    document.getElementById('modal-editar').classList.remove('hidden');
    document.getElementById('modal-editar').classList.add('flex');
}
// ---- Modal Eliminar ----
function confirmarEliminarProcedimiento(id) {
    document.getElementById('modal-eliminar').classList.remove('hidden');
    document.getElementById('id-eliminar').value = id;
}
function cerrarModalEliminar() {
    document.getElementById('modal-eliminar').classList.add('hidden');
}

// --- Modal Editar ---
let pasosActuales = [];
let modoEdicion = true;

function abrirModalEditar(id) {
    modoEdicion = true;
    fetch('procedimiento_api_detalle.php?id=' + id)
        .then(res => res.json())
        .then(proc => {
            document.getElementById('modal-editar-titulo').innerText = 'Editar procedimiento';
            document.getElementById('editar-id').value = proc.id;
            document.getElementById('editar-nombre').value = proc.nombre;
            document.getElementById('editar-desc').value = proc.descripcion;
            document.getElementById('editar-cat-select').value = proc.categoria;
            // Cargar pasos:
            pasosActuales = (proc.pasos && proc.pasos.length) ? proc.pasos : [];
            renderPasosEditar();
            document.getElementById('modal-editar').classList.remove('hidden');
            document.getElementById('modal-editar').classList.add('flex');
        });
}
function cerrarModalEditar() {
    document.getElementById('modal-editar').classList.add('hidden');
    document.getElementById('modal-editar').classList.remove('flex');
    pasosActuales = [];
}

// --- Render Pasos ---
function renderPasosEditar() {
    const pasosDiv = document.getElementById('pasos-lista');
    pasosDiv.innerHTML = '';
    if(pasosActuales.length === 0) {
        pasosDiv.innerHTML = '<div class="text-gray-400">Agrega los pasos aquí…</div>';
        return;
    }
    pasosActuales.forEach((paso, i) => {
        let imagenHtml = '';
        if (paso.imagen) {
            imagenHtml = `<div class="my-2">
                <img src="uploads/${paso.imagen}" alt="Imagen paso" class="max-h-24 rounded shadow mb-1"/>
                <label class="inline-flex items-center text-xs ml-1">
                    <input type="checkbox" name="eliminar_imagen[${i}]" value="1"> Eliminar imagen
                </label>
            </div>
            <input type="hidden" name="pasos_imagen_actual[]" value="${paso.imagen??''}">`;
        } else {
            imagenHtml = `<input type="hidden" name="pasos_imagen_actual[]" value="">`;
        }
        pasosDiv.innerHTML += `
        <div class="step-card flex flex-col gap-2 mb-4 bg-gray-50 border rounded p-2">
            <div class="flex items-center gap-2">
                <div class="font-bold text-lg">${i+1}.</div>
                <div class="flex-grow">
                    <textarea class="border rounded p-2 w-full" name="pasos_texto[]" required placeholder="Escribe el paso">${paso.texto || ''}</textarea>
                </div>
                <div class="step-controls flex flex-col gap-1">
                    <button type="button" onclick="moverPasoEditar(${i}, -1)" title="Subir" ${i==0?'disabled':''}>↑</button>
                    <button type="button" onclick="moverPasoEditar(${i}, 1)" title="Bajar" ${i==pasosActuales.length-1?'disabled':''}>↓</button>
                    <button type="button" onclick="eliminarPasoEditar(${i})" title="Eliminar">🗑️</button>
                </div>
            </div>
            ${imagenHtml}
            <div>
                <input type="file" name="pasos_imagen[]">
            </div>
        </div>`;
    });
}

function agregarPasoEditar(txt='', img='') {
    // Actualizar pasosActuales con lo que hay en los textareas antes de agregar
    const textos = Array.from(document.querySelectorAll('textarea[name="pasos_texto[]"]')).map(t=>t.value);
    const imagenes = Array.from(document.querySelectorAll('input[name="pasos_imagen_actual[]"]')).map(i=>i.value);
    pasosActuales = textos.map((texto, i) => ({
        texto,
        imagen: imagenes[i] || ''
    }));
    // Agrega el nuevo paso vacío al final
    pasosActuales.push({ texto: txt, imagen: img });
    renderPasosEditar();
}
function eliminarPasoEditar(i) {
    pasosActuales.splice(i, 1);
    renderPasosEditar();
}
function moverPasoEditar(i, dir) {
    if ((i+dir)<0 || (i+dir)>=pasosActuales.length) return;
    [pasosActuales[i], pasosActuales[i+dir]] = [pasosActuales[i+dir], pasosActuales[i]];
    renderPasosEditar();
}

// Guardar pasos antes de submit
document.getElementById('form-editar-proc').onsubmit = function() {
    // Actualizar pasos desde los inputs (textareas y file inputs)
    const textos = Array.from(document.querySelectorAll('textarea[name="pasos_texto[]"]')).map(t=>t.value);
    const imagenes = Array.from(document.querySelectorAll('input[name="pasos_imagen_actual[]"]')).map(i=>i.value);
    pasosActuales = textos.map((texto, i) => ({
        texto,
        imagen: imagenes[i] || ''
    }));
    // Aquí podrías pasar pasosActuales a un campo oculto si los necesitas en JSON.
    return true;
};

// ---- Generar PDF (simple, igual a la vista) ----
function generarPDF(id, nombre) {
    fetch('procedimiento_api_detalle.php?id=' + id).then(r=>r.json()).then(proc=>{
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        let y = 20;
        doc.setFontSize(18);
        doc.text(proc.nombre, 14, y);
        y += 10;
        doc.setFontSize(12);
        doc.text('Descripción: ' + proc.descripcion, 14, y); y+=8;
        doc.text('Categoría: ' + proc.categoria, 14, y); y+=8;
        doc.setFontSize(14); y+=4;
        doc.text('Pasos:', 14, y); y+=8;
        doc.setFontSize(11);
        proc.pasos.forEach((paso, i) => {
            doc.setFont(undefined, 'bold');
            doc.text((i+1)+'.', 14, y);
            doc.setFont(undefined, 'normal');
            let lines = doc.splitTextToSize(paso.texto || '', 170);
            doc.text(lines, 22, y);
            y += 7 + 5*(lines.length-1);
            if(paso.imagen){
                doc.setFontSize(10); doc.setTextColor(100);
                doc.text('[Contiene imagen: '+paso.imagen+']', 22, y);
                doc.setFontSize(11); doc.setTextColor(0); y+=6;
            }
            y+=2;
            if(y > 265){ doc.addPage(); y = 20; }
        });
        doc.save(proc.nombre.replace(/\s/g,'_')+"_procedimiento.pdf");
    });
}
</script>

</body>
</html>
