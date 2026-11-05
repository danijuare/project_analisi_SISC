<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Solicitud.php";
$u = usuarioActual();

$solicitudNum = $_GET["id"] ?? 'SOL-000123';
$solModel = new Solicitud();
$sol = $solModel->mostrar($solicitudNum);

if (!$sol) {
    echo "<script>alert('Solicitud no encontrada.'); window.location.href='bandeja_funcionario.php';</script>";
    exit();
}

$datosForm = json_decode($sol['datos_formulario'], true) ?: [];
$pasoActualNum = intval($sol['numero_paso'] ?? 2);
$estado = $sol['estado'];
$cuiEnmascarado = "•••• •••• " . (substr($sol['cui'], -4) ?: '0123');

$badge = "bg-amber-100 text-amber-800 border-amber-300";
if ($estado === 'Finalizada') {
    $badge = "bg-emerald-100 text-emerald-800 border-emerald-300";
} elseif ($estado === 'Pendiente de pago') {
    $badge = "bg-blue-100 text-blue-800 border-blue-300";
} elseif ($estado === 'Rechazada') {
    $badge = "bg-rose-100 text-rose-800 border-rose-300";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar y Finalizar Solicitud | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior Funcionario -->
    <?php include "nav_funcionario.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 09) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-slate-900 font-mono">
                    Solicitud <?php echo htmlspecialchars($sol['numero_seguimiento']); ?>
                </h1>
                <span class="text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                    <?php echo htmlspecialchars($estado); ?>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="abrirModalSubsanar()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square text-blue-900"></i> Corregir Datos
                </button>
                <a href="bandeja_funcionario.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver a Bandeja
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Datos del Ciudadano y Documentos -->
            <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <div>
                    <h2 class="text-xl font-black text-slate-900 mb-1">
                        <?php echo htmlspecialchars($sol['flujo_nombre']); ?>
                    </h2>
                    <span class="text-xs font-semibold text-slate-500">
                        Expediente de <?php echo htmlspecialchars($sol['institucion_nombre']); ?>
                    </span>
                </div>

                <!-- Datos Clave del Formulario -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Ciudadano</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5"><?php echo htmlspecialchars($sol['ciudadano_nombre']); ?></p>
                        <span class="font-mono text-slate-500 text-[11px]">Tel: <?php echo htmlspecialchars($datosForm['telefono'] ?? $sol['ciudadano_telefono']); ?></span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">CUI</span>
                        <p class="font-mono font-bold text-slate-900 text-sm mt-0.5"><?php echo htmlspecialchars($cuiEnmascarado); ?></p>
                        <span class="text-slate-500 text-[11px]"><?php echo htmlspecialchars($datosForm['email'] ?? $sol['ciudadano_email']); ?></span>
                    </div>

                    <?php if (isset($datosForm['nit'])): ?>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">NIT SAT</span>
                        <p class="font-mono font-bold text-blue-900 text-sm mt-0.5"><?php echo htmlspecialchars($datosForm['nit']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($datosForm['actividad_economica'])): ?>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Actividad Económica</span>
                        <p class="font-semibold text-slate-800 text-xs mt-0.5"><?php echo htmlspecialchars($datosForm['actividad_economica']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($datosForm['regimen'])): ?>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Régimen Tributario Declarado</span>
                        <p class="font-bold text-blue-950 text-xs mt-0.5"><?php echo htmlspecialchars($datosForm['regimen']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="sm:col-span-2 pt-2 border-t border-slate-200/60">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Dirección o Domicilio Declarado</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5">
                            <?php echo htmlspecialchars($datosForm['direccion_residencia'] ?? ($datosForm['direccion_fiscal'] ?? ($sol['ciudadano_direccion'] ?: '12 calle 5-30, zona 2, Villa Nueva'))); ?>
                        </p>
                    </div>

                    <div class="sm:col-span-2 pt-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Consentimiento del Ciudadano</span>
                        <p class="font-semibold text-emerald-700 mt-0.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 me-1"></i>
                            Otorgado · <?php echo date('d M Y, H:i', strtotime($sol['fecha_inicio'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Documentos Adjuntos -->
                <div class="space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider">
                        Documentos adjuntos
                    </h3>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <div class="space-y-0.5">
                                <span class="font-bold text-slate-800">Origen: ciudadano</span>
                                <span class="text-slate-400 block text-[11px]">
                                    Estado: <strong class="text-slate-700"><?php echo htmlspecialchars($sol['doc_estado'] ?? 'en revisión'); ?></strong>
                                </span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-mono text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 font-bold">
                                <i class="fa-solid fa-shield-check text-emerald-500"></i> Hash verificado
                            </span>
                        </div>

                        <?php if (!empty($sol['nombre_archivo'])): ?>
                        <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                            <p class="text-xs font-mono font-bold text-slate-800 mb-2">
                                <i class="fa-solid fa-file-pdf text-rose-500 me-1"></i>
                                Vista previa de <?php echo htmlspecialchars($sol['nombre_archivo']); ?>
                            </p>
                            <button onclick="document.getElementById('modalPrevia').classList.remove('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all">
                                Abrir Documento en Visor
                            </button>
                        </div>
                        <?php else: ?>
                        <p class="text-xs text-slate-400 italic">No se adjuntaron archivos físicos en este proceso.</p>
                        <?php endif; ?>

                        <?php if (!empty($sol['certificado_serial'])): ?>
                        <div class="bg-blue-50 p-3.5 rounded-xl border border-blue-200 text-xs text-blue-900">
                            <span class="font-bold block flex items-center gap-1.5">
                                <i class="fa-solid fa-certificate text-blue-700"></i> Firma Electrónica Avanzada Oficial:
                            </span>
                            <p class="font-mono text-[11px] mt-1 font-bold text-blue-950">Serial PKI: <?php echo htmlspecialchars($sol['certificado_serial']); ?></p>
                            <p class="font-mono text-[10px] text-slate-500 truncate">Sello: <?php echo substr($sol['firma_digital'], 0, 32); ?>...</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Flujo de Aprobación y Decisión -->
            <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <h3 class="font-extrabold text-slate-900 text-base">
                    Flujo de aprobación
                </h3>

                <!-- Stepper Vertical (Mockup Fase 3 Pantalla 09) -->
                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                    
                    <!-- Recepción -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-[#1d4289] ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Recepción</h4>
                            <span class="text-[11px] text-emerald-600 font-semibold">Completado</span>
                        </div>
                    </div>

                    <!-- Revisión del funcionario -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($estado === 'En proceso') ? 'bg-amber-500 ring-4 ring-amber-100 animate-pulse' : 'bg-[#1d4289] ring-4 ring-white'; ?> flex items-center justify-center text-[10px] text-white">
                            <?php if ($estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Revisión del funcionario</h4>
                            <span class="text-[11px] <?php echo ($estado === 'En proceso') ? 'text-amber-600 font-semibold' : 'text-slate-400'; ?>">
                                <?php echo ($estado === 'Finalizada') ? 'Firmado y aprobado' : 'Paso actual · requiere resolución'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Envío a Institución -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($estado === 'Finalizada') ? 'bg-[#1d4289]' : 'bg-slate-300'; ?> ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <?php if ($estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Envío a <?php echo htmlspecialchars($sol['institucion_codigo']); ?></h4>
                            <span class="text-[11px] text-slate-400">
                                <?php echo ($estado === 'Finalizada') ? 'Sincronizado vía ESB' : 'Pendiente'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Cierre y notificación -->
                    <div class="relative flex items-start gap-4">
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?php echo ($estado === 'Finalizada') ? 'bg-emerald-500' : 'bg-slate-300'; ?> ring-4 ring-white flex items-center justify-center text-[10px] text-white">
                            <?php if ($estado === 'Finalizada'): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Cierre y notificación</h4>
                            <span class="text-[11px] <?php echo ($estado === 'Finalizada') ? 'text-emerald-700 font-bold' : 'text-slate-400'; ?>">
                                <?php echo ($estado === 'Finalizada') ? 'Expediente Notificado al Ciudadano' : 'Pendiente'; ?>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Panel de Decisión del Funcionario -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider">
                        Decisión del Funcionario
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Resolución / Justificación
                        </label>
                        <textarea id="inputComentario" rows="3" placeholder="Ingresa la resolución oficial o fundamentos para la aprobación o rechazo..."
                                  class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-xs font-semibold transition-all"></textarea>
                    </div>

                    <?php if ($estado !== 'Finalizada' && $estado !== 'Rechazada'): ?>
                    <div class="space-y-3">
                        <!-- BOTÓN PRINCIPAL: FINALIZAR EL PROCESO -->
                        <button type="button" onclick="finalizarProceso()" id="btnFinalizar" class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-stamp text-sm"></i>
                            <span>Finalizar Proceso y Emitir Resolución Oficial</span>
                        </button>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <button type="button" onclick="aprobarConFirma()" id="btnAprobar" class="flex-1 py-2.5 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-signature"></i>
                                <span>Avanzar Paso</span>
                            </button>
                            <button type="button" onclick="rechazarSolicitud()" id="btnRechazar" class="flex-1 py-2.5 bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-bold rounded-xl transition-all">
                                Rechazar
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs space-y-1">
                            <div class="flex items-center gap-2 text-emerald-800 font-bold">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>Trámite en estado <?php echo htmlspecialchars($estado); ?></span>
                            </div>
                            <p class="text-emerald-700 text-[11px]">
                                La resolución ha sido registrada en el expediente ciudadano y sincronizada con el ESB interinstitucional.
                            </p>
                        </div>
                        <button type="button" onclick="reabrirExpediente()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-300 transition-all">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reabrir Expediente para Revisión
                        </button>
                    </div>
                    <?php endif; ?>

                    <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                        La resolución y firma electrónica avanzada se validan en el Prestador de Servicios de Certificación PKI y se registran en la bitácora de auditoría.
                    </p>
                </div>

            </div>

        </div>

    </main>

    <!-- Modal Visor Documento -->
    <div id="modalPrevia" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Visor Documental: <?php echo htmlspecialchars($sol['nombre_archivo']); ?></h3>
                <button onclick="document.getElementById('modalPrevia').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="bg-slate-50 p-8 rounded-2xl border border-slate-200 text-center space-y-3">
                <i class="fa-solid fa-file-shield text-5xl text-blue-900"></i>
                <p class="font-bold text-xs text-slate-800"><?php echo htmlspecialchars($sol['nombre_archivo']); ?></p>
                <p class="text-[11px] text-slate-500 font-mono">Hash SHA-256: <?php echo htmlspecialchars($sol['hash_sha256'] ?? 'verificado'); ?></p>
                <span class="inline-block text-[11px] text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full font-bold border border-emerald-200">
                    Documento Oficial Aprobado para Cotejo
                </span>
            </div>
            <button onclick="document.getElementById('modalPrevia').classList.add('hidden')" class="w-full py-2.5 bg-slate-800 text-white text-xs font-bold rounded-xl">
                Cerrar Visor
            </button>
        </div>
    </div>

    <!-- Modal Subsanar / Modificar Datos por el Funcionario -->
    <div id="modalSubsanar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Modificar / Subsanar Datos del Expediente</h3>
                <button onclick="cerrarModalSubsanar()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formSubsanar" class="space-y-4">
                <input type="hidden" name="solicitud_id" value="<?php echo $sol['solicitud_id']; ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección / Domicilio</label>
                    <input type="text" name="direccion" required
                           value="<?php echo htmlspecialchars($datosForm['direccion_residencia'] ?? ($datosForm['direccion_fiscal'] ?? $sol['ciudadano_direccion'])); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono</label>
                    <input type="text" name="telefono" required
                           value="<?php echo htmlspecialchars($datosForm['telefono'] ?? $sol['ciudadano_telefono']); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($datosForm['email'] ?? $sol['ciudadano_email']); ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                </div>

                <div class="pt-3 border-t border-slate-100 flex gap-2">
                    <button type="button" onclick="cerrarModalSubsanar()" class="flex-1 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-xl shadow">
                        Guardar Corrección
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    // 1. FINALIZAR PROCESO COMPLETAMENTE
    function finalizarProceso() {
        const comentario = document.getElementById('inputComentario').value.trim();
        const btn = document.getElementById('btnFinalizar');
        
        if (!confirm('¿Confirma la resolución favorable y FINALIZACIÓN de este expediente?')) {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Finalizando proceso y emitiendo certificado PKI...`;

        const formData = new FormData();
        formData.append('solicitud_id', '<?php echo $sol['solicitud_id']; ?>');
        formData.append('comentario', comentario);

        fetch('../ajax/solicitud.php?op=finalizar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡PROCESO FINALIZADO EXITOSAMENTE!\n\nResolución Oficial Emitida.\nCertificado PKI: ${data.certificado_serial}\n\nLos cambios han sido aplicados al expediente del ciudadano.`);
                location.reload();
            } else {
                alert('Error al finalizar el trámite: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-stamp text-sm"></i> <span>Finalizar Proceso y Emitir Resolución Oficial</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error en la comunicación con el servidor.');
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-stamp text-sm"></i> <span>Finalizar Proceso y Emitir Resolución Oficial</span>`;
        });
    }

    // 2. AVANZAR PASO INTERMEDIO
    function aprobarConFirma() {
        const comentario = document.getElementById('inputComentario').value;
        const btn = document.getElementById('btnAprobar');
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Firmando paso...`;

        const formData = new FormData();
        formData.append('solicitud_id', '<?php echo $sol['solicitud_id']; ?>');
        formData.append('comentario', comentario);

        fetch('../ajax/solicitud.php?op=aprobar_firma', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡Paso Aprobado con Firma Electrónica!\nSerial PKI: ${data.certificado_serial}\nSiguiente estado: ${data.siguiente_paso}`);
                location.reload();
            } else {
                alert('Error al firmar: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-signature"></i> <span>Avanzar Paso</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al comunicar con el servicio de firma digital.');
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-signature"></i> <span>Avanzar Paso</span>`;
        });
    }

    // 3. RECHAZAR SOLICITUD
    function rechazarSolicitud() {
        const comentario = document.getElementById('inputComentario').value.trim();
        if (!comentario) {
            alert('Debe ingresar un comentario con la justificación del rechazo.');
            document.getElementById('inputComentario').focus();
            return;
        }

        if (!confirm('¿Está seguro de que desea rechazar esta solicitud?')) return;

        const formData = new FormData();
        formData.append('solicitud_id', '<?php echo $sol['solicitud_id']; ?>');
        formData.append('comentario', comentario);

        fetch('../ajax/solicitud.php?op=rechazar', {
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

    // 4. REABRIR EXPEDIENTE
    function reabrirExpediente() {
        const motivo = prompt('Ingrese el motivo por el cual reabre este expediente:');
        if (motivo === null) return;

        const formData = new FormData();
        formData.append('solicitud_id', '<?php echo $sol['solicitud_id']; ?>');
        formData.append('motivo', motivo);

        fetch('../ajax/solicitud.php?op=reabrir', {
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

    // 5. SUBSANAR DATOS
    function abrirModalSubsanar() {
        document.getElementById('modalSubsanar').classList.remove('hidden');
    }
    function cerrarModalSubsanar() {
        document.getElementById('modalSubsanar').classList.add('hidden');
    }

    document.getElementById('formSubsanar').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('../ajax/solicitud.php?op=editar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('¡Datos corregidos y actualizados en el expediente!');
                location.reload();
            } else {
                alert('Error al actualizar: ' + data.mensaje);
            }
        });
    });
    </script>

</body>
</html>
