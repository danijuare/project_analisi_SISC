<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Usuario.php";
$u = usuarioActual();

// Obtener datos actuales del ciudadano desde la BD
$ciudadano = ejecutarConsultaSimpleFila("SELECT * FROM ciudadanos WHERE ciudadano_id = {$u['id']}");
$direccionActual = $ciudadano['direccion'] ?? '12 calle 5-30, zona 2, Villa Nueva';
$telefonoActual = $ciudadano['telefono'] ?? '5512 3456';
$emailActual = $ciudadano['email'] ?? 'carlos.ramirez@correo.com';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Datos RENAP | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 03) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            TRÁMITES › RENAP · PASO 2 DE 3
        </div>

        <h1 class="text-3xl font-black text-slate-900">
            Actualizar datos
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Stepper de Pasos -->
            <div class="md:col-span-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-5">
                
                <div class="space-y-4">
                    <!-- Paso 1 -->
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">1. Verificar identidad</span>
                            <span class="text-[11px] text-emerald-600 font-semibold">Completado por RENAP</span>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-[#1d4289] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 ring-4 ring-blue-100">
                            2
                        </div>
                        <div>
                            <span class="text-xs font-bold text-[#1d4289] block">2. Datos y documento</span>
                            <span class="text-[11px] text-blue-700 font-semibold">Paso actual</span>
                        </div>
                    </div>

                    <!-- Paso 3 -->
                    <div class="flex items-start gap-3 opacity-50">
                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                            3
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">3. Confirmar y enviar</span>
                            <span class="text-[11px] text-slate-500">Pendiente de firma</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100 text-xs text-blue-900 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-blue-700 me-1"></i>
                    Los cambios se registrarán directamente en tu expediente unificado de RENAP y se sincronizarán con SAT e IGSS.
                </div>

            </div>

            <!-- Columna Derecha: Formulario de Modificación de Datos -->
            <div class="md:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
                
                <form id="formActualizarRenap" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="tipo_proceso" value="ACT_DATOS_RENAP">

                    <!-- Dirección de residencia -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Dirección de residencia
                        </label>
                        <input type="text" name="direccion" id="direccion" required
                               value="<?php echo htmlspecialchars($direccionActual); ?>"
                               placeholder="Ej. 12 calle 5-30, zona 2, Villa Nueva"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-100 text-sm font-semibold transition-all">
                    </div>

                    <!-- Teléfono y Correo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Teléfono
                            </label>
                            <input type="text" name="telefono" id="telefono" required
                                   value="<?php echo htmlspecialchars($telefonoActual); ?>"
                                   placeholder="Ej. 5512 3456"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-100 text-sm font-semibold transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Correo electrónico
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="<?php echo htmlspecialchars($emailActual); ?>"
                                   placeholder="correo@ejemplo.com"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-100 text-sm font-semibold transition-all">
                        </div>
                    </div>

                    <!-- Documento de Respaldo -->
                    <div class="p-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 uppercase">
                                    Documento de respaldo
                                </label>
                                <span class="text-xs text-slate-500">
                                    PDF o imagen, con vista previa antes de enviar
                                </span>
                            </div>
                            <span class="text-xs text-blue-900 font-mono font-bold bg-blue-100 px-2 py-0.5 rounded">
                                Recibo de Luz / Agua
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <input type="file" name="documento_respaldo" id="documento_respaldo" accept=".pdf,image/*" class="hidden" onchange="actualizarNombreArchivo(this)">
                            
                            <div class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-mono font-semibold text-slate-700 flex items-center justify-between w-full">
                                <span id="nombreArchivoTxt">
                                    <i class="fa-solid fa-file-pdf text-rose-500 me-2"></i>recibo-domicilio.pdf · 184 KB
                                </span>
                                <span class="text-emerald-600 font-bold text-[10px] uppercase">Listo</span>
                            </div>

                            <div class="flex gap-2 w-full sm:w-auto">
                                <button type="button" onclick="mostrarVistaPrevia()" class="flex-1 sm:flex-none px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition-all">
                                    Ver vista previa
                                </button>
                                <button type="button" onclick="document.getElementById('documento_respaldo').click()" class="flex-1 sm:flex-none px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-xs font-bold rounded-xl transition-all">
                                    Cambiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox de Consentimiento Obligatorio -->
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="consentimiento" id="consentimiento" required checked
                               class="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-700 mt-0.5">
                        <span class="text-xs text-slate-600 leading-normal">
                            Autorizo a <strong>RENAP</strong> a validar estos datos con mi consentimiento interinstitucional. Puedo revocarlo en cualquier momento.
                        </span>
                    </label>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                        <a href="inicio_ciudadano.php" class="flex-1 py-3 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition-all">
                            Atrás
                        </a>
                        <button type="submit" id="btnContinuar" class="flex-1 py-3 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                            <span>Continuar</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </main>

    <!-- Modal de Vista Previa de Documento -->
    <div id="modalVistaPrevia" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-rose-500"></i> Vista previa del documento de respaldo
                </h3>
                <button onclick="cerrarVistaPrevia()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div id="contenedorPrevia" class="bg-slate-100 p-6 rounded-2xl border border-slate-200 text-center min-h-[220px] flex flex-col items-center justify-center">
                <i class="fa-solid fa-file-invoice text-5xl text-blue-900/40 mb-3"></i>
                <p class="font-mono text-xs font-bold text-slate-700" id="previaNombreTxt">recibo-domicilio.pdf</p>
                <p class="text-[11px] text-slate-400 mt-1">Verificado criptográficamente · Hash SHA-256 verificado</p>
                <div class="mt-4 inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    <i class="fa-solid fa-check"></i> Documento legible para validación de RENAP
                </div>
            </div>

            <button onclick="cerrarVistaPrevia()" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition-all">
                Cerrar vista previa
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function actualizarNombreArchivo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeKB = (file.size / 1024).toFixed(0);
            document.getElementById('nombreArchivoTxt').innerHTML = `<i class="fa-solid fa-file text-blue-600 me-2"></i>${file.name} · ${sizeKB} KB`;
            document.getElementById('previaNombreTxt').innerText = file.name;
        }
    }

    function mostrarVistaPrevia() {
        document.getElementById('modalVistaPrevia').classList.remove('hidden');
    }
    function cerrarVistaPrevia() {
        document.getElementById('modalVistaPrevia').classList.add('hidden');
    }

    document.getElementById('formActualizarRenap').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnContinuar');
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Registrando trámite...`;

        const formData = new FormData(this);

        fetch('../ajax/solicitud.php?op=guardar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡Trámite creado exitosamente! Número de seguimiento: ${data.numero_seguimiento}`);
                window.location.href = `seguimiento.php?id=${encodeURIComponent(data.numero_seguimiento)}`;
            } else {
                alert('Error: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<span>Continuar</span> <i class="fa-solid fa-arrow-right"></i>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión al procesar la solicitud.');
            btn.disabled = false;
            btn.innerHTML = `<span>Continuar</span> <i class="fa-solid fa-arrow-right"></i>`;
        });
    });
    </script>

</body>
</html>

