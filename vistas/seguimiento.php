<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO', 'FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Solicitud.php";
$u = usuarioActual();

$solicitudNum = $_GET["id"] ?? 'SOL-000123';
$solModel = new Solicitud();
$sol = $solModel->mostrar($solicitudNum);

if (!$sol) {
    echo "<script>alert('Solicitud no encontrada.'); window.location.href='inicio_ciudadano.php';</script>";
    exit();
}

$datosForm = json_decode($sol['datos_formulario'], true) ?: [];
$pasoActualNum = intval($sol['numero_paso'] ?? 1);
$estado = $sol['estado'];

// Colores de badges según estado
$badgeClase = "bg-amber-100 text-amber-800 border-amber-300";
if ($estado === 'Finalizada') {
    $badgeClase = "bg-emerald-100 text-emerald-800 border-emerald-300";
} elseif ($estado === 'Pendiente de pago') {
    $badgeClase = "bg-blue-100 text-blue-800 border-blue-300";
} elseif ($estado === 'Rechazada') {
    $badgeClase = "bg-rose-100 text-rose-800 border-rose-300";
}

$esCiudadano = ($u["tipo_usuario"] === 'ciudadano');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Solicitud | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar según rol -->
    <?php if ($esCiudadano): ?>
        <?php include "nav_ciudadano.php"; ?>
    <?php else: ?>
        <?php include "nav_funcionario.php"; ?>
    <?php endif; ?>

    <!-- Contenido Principal (Mockup Pantalla 06) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            TRÁMITES › <?php echo htmlspecialchars(strtoupper($sol['flujo_nombre'])); ?> · <?php echo htmlspecialchars($sol['institucion_codigo']); ?>
        </div>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-slate-900 font-mono">
                    <?php echo htmlspecialchars($sol['numero_seguimiento']); ?>
                </h1>
                <span class="text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badgeClase; ?>">
                    <?php echo htmlspecialchars($estado); ?>
                </span>
            </div>

            <div class="flex gap-2">
                <?php if ($esCiudadano && $estado !== 'Finalizada'): ?>
                <button onclick="abrirModalEditar()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-pen"></i> Editar datos
                </button>
                <button onclick="cancelarSolicitud()" class="px-3 py-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition-all">
                    Cancelar
                </button>
                <?php endif; ?>

                <a href="<?php echo $esCiudadano ? 'inicio_ciudadano.php' : 'bandeja_funcionario.php'; ?>" class="px-4 py-2 bg-[#1d4289] text-white text-xs font-bold rounded-xl shadow-sm hover:bg-blue-950 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Línea de Tiempo / Seguimiento -->
            <div class="md:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <h3 class="font-bold text-slate-900 text-base">
                    Seguimiento
                </h3>

                <!-- Stepper Vertical -->
                <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                    
                    <!-- Paso 1: Solicitud recibida -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-[#1d4289] ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Solicitud recibida</h4>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <?php echo date('d M Y · H:i', strtotime($sol['fecha_inicio'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Paso 2: Revisión del funcionario -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($pasoActualNum >= 2 && $estado !== 'Finalizada') ? 'bg-amber-500 ring-4 ring-amber-100 animate-pulse' : ($pasoActualNum > 2 || $estado === 'Finalizada' ? 'bg-[#1d4289]' : 'bg-slate-300'); ?> ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <?php if ($pasoActualNum > 2 || $estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Revisión del funcionario</h4>
                            <p class="text-xs <?php echo ($pasoActualNum == 2 && $estado !== 'Finalizada') ? 'text-amber-600 font-semibold' : 'text-slate-400'; ?> mt-0.5">
                                <?php echo ($pasoActualNum > 2 || $estado === 'Finalizada') ? 'Aprobado por ventanilla' : 'En curso · ' . htmlspecialchars($sol['institucion_codigo']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Paso 3: Enviada a Institución -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($pasoActualNum >= 3 && $estado !== 'Finalizada') ? 'bg-amber-500 ring-4 ring-amber-100' : ($pasoActualNum > 3 || $estado === 'Finalizada' ? 'bg-[#1d4289]' : 'bg-slate-300'); ?> ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <?php if ($pasoActualNum > 3 || $estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Enviada a <?php echo htmlspecialchars($sol['institucion_codigo']); ?></h4>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <?php echo ($pasoActualNum >= 3 || $estado === 'Finalizada') ? 'Sincronizado vía ESB' : 'Consultamos el estatus automáticamente'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Paso 4: Resultado disponible -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($estado === 'Finalizada') ? 'bg-emerald-500' : 'bg-slate-300'; ?> ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <?php if ($estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Resultado disponible</h4>
                            <p class="text-xs <?php echo ($estado === 'Finalizada') ? 'text-emerald-600 font-bold' : 'text-slate-400'; ?> mt-0.5">
                                <?php echo ($estado === 'Finalizada') ? 'Trámite completado satisfactoriamente' : 'Recibirás un aviso por correo y en la app'; ?>
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Columna Derecha: Datos enviados y Documentos -->
            <div class="md:col-span-5 space-y-6">
                
                <!-- Datos enviados -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider">
                        Datos enviados
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Ciudadano</span>
                            <p class="font-bold text-slate-800 text-sm mt-0.5"><?php echo htmlspecialchars($sol['ciudadano_nombre']); ?></p>
                            <span class="font-mono text-[11px] text-slate-500">CUI: <?php echo htmlspecialchars($sol['cui']); ?></span>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Dirección</span>
                            <p class="font-semibold text-slate-700 mt-0.5">
                                <?php echo htmlspecialchars($datosForm['direccion_residencia'] ?? ($sol['ciudadano_direccion'] ?: '12 calle 5-30, zona 2, Villa Nueva')); ?>
                            </p>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Teléfono · Correo</span>
                            <p class="font-semibold text-slate-700 mt-0.5">
                                <?php echo htmlspecialchars($datosForm['telefono'] ?? $sol['ciudadano_telefono']); ?> · <?php echo htmlspecialchars($datosForm['email'] ?? $sol['ciudadano_email']); ?>
                            </p>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Consentimiento</span>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="text-emerald-700 font-semibold text-[11px]">
                                    <i class="fa-solid fa-check me-1"></i> Otorgado · <?php echo date('d M Y, H:i', strtotime($sol['fecha_inicio'])); ?>
                                </span>
                                <button onclick="alert('Consentimiento digital verificado.');" class="text-blue-900 text-[11px] font-bold hover:underline">
                                    Detalle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos adjuntos -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider">
                        Documentos
                    </h3>

                    <?php if (!empty($sol['nombre_archivo'])): ?>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono font-bold text-slate-700 truncate max-w-[180px]">
                                <i class="fa-solid fa-file-pdf text-rose-500 me-1"></i> <?php echo htmlspecialchars($sol['nombre_archivo']); ?>
                            </span>
                            <span class="text-[10px] text-emerald-700 font-bold uppercase bg-emerald-50 px-2 py-0.5 rounded">
                                Enviado
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-mono truncate">
                            SHA: <?php echo substr($sol['hash_sha256'] ?? 'c838e4a9', 0, 16); ?>...
                        </p>
                        <button onclick="document.getElementById('modalPreviaDoc').classList.remove('hidden')" class="w-full mt-2 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition-all">
                            Ver vista previa
                        </button>
                    </div>
                    <?php else: ?>
                    <p class="text-xs text-slate-400">Este trámite no requirió documentos adjuntos adicionales.</p>
                    <?php endif; ?>

                    <?php if (!empty($sol['certificado_serial'])): ?>
                    <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200 text-xs text-emerald-900">
                        <span class="font-bold block flex items-center gap-1">
                            <i class="fa-solid fa-certificate text-emerald-600"></i> Firma Electrónica Avanzada:
                        </span>
                        <span class="font-mono text-[11px] text-emerald-700 block mt-0.5">
                            Certificado: <?php echo htmlspecialchars($sol['certificado_serial']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </main>

    <!-- Modal Editar Datos Enviados -->
    <div id="modalEditar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Editar datos de la solicitud</h3>
                <button onclick="cerrarModalEditar()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formEdicionSolicitud" class="space-y-4">
                <input type="hidden" name="solicitud_id" value="<?php echo $sol['solicitud_id']; ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección de residencia</label>
                    <input type="text" name="direccion" required
                           value="<?php echo htmlspecialchars($datosForm['direccion_residencia'] ?? $sol['ciudadano_direccion']); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono</label>
                    <input type="text" name="telefono" required
                           value="<?php echo htmlspecialchars($datosForm['telefono'] ?? $sol['ciudadano_telefono']); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo electrónico</label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($datosForm['email'] ?? $sol['ciudadano_email']); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div class="pt-3 border-t border-slate-100 flex gap-2">
                    <button type="button" onclick="cerrarModalEditar()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-xl transition-all shadow">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Vista Previa Documento -->
    <div id="modalPreviaDoc" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Vista previa: <?php echo htmlspecialchars($sol['nombre_archivo']); ?></h3>
                <button onclick="document.getElementById('modalPreviaDoc').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="bg-slate-100 p-8 rounded-2xl border border-slate-200 text-center">
                <i class="fa-solid fa-file-pdf text-6xl text-rose-500 mb-3"></i>
                <p class="font-bold text-xs text-slate-800"><?php echo htmlspecialchars($sol['nombre_archivo']); ?></p>
                <p class="text-[11px] text-slate-400 mt-1">Hash SHA-256 verificado por el expediente institucional</p>
            </div>
            <button onclick="document.getElementById('modalPreviaDoc').classList.add('hidden')" class="w-full py-2 bg-slate-800 text-white text-xs font-bold rounded-xl">
                Cerrar
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function abrirModalEditar() {
        document.getElementById('modalEditar').classList.remove('hidden');
    }
    function cerrarModalEditar() {
        document.getElementById('modalEditar').classList.add('hidden');
    }

    document.getElementById('formEdicionSolicitud').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('../ajax/solicitud.php?op=editar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('¡Datos de la solicitud actualizados con éxito!');
                location.reload();
            } else {
                alert('Error: ' + data.mensaje);
            }
        });
    });

    function cancelarSolicitud() {
        if (!confirm('¿Estás seguro de que deseas cancelar este trámite?')) return;
        const formData = new FormData();
        formData.append('solicitud_id', '<?php echo $sol['solicitud_id']; ?>');

        fetch('../ajax/solicitud.php?op=cancelar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje);
            if (data.status === 'success') {
                location.reload();
            }
        });
    }
    </script>

</body>
</html>

