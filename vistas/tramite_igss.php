<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Usuario.php";
$u = usuarioActual();

$ciudadano = ejecutarConsultaSimpleFila("SELECT * FROM ciudadanos WHERE ciudadano_id = {$u['id']}");
$afiliacion = $ciudadano['numero_afiliacion_igss'] ?? '5521';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedir Cita IGSS | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 04) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            TRÁMITES › IGSS · CONTROL DE SALUD
        </div>

        <h1 class="text-3xl font-black text-slate-900">
            Pedir cita
        </h1>

        <form id="formCitaIgss" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <input type="hidden" name="tipo_proceso" value="CITA_MEDICA_IGSS">
            <input type="hidden" name="horario" id="inputHorario" value="09:30">

            <!-- Columna Izquierda: Formulario Principal -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <!-- Afiliación y Fecha -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Afiliación
                        </label>
                        <div class="px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold flex items-center justify-between">
                            <span>Afiliado IGSS · No. •••• <?php echo htmlspecialchars($afiliacion); ?></span>
                            <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        </div>
                        <input type="hidden" name="afiliacion" value="<?php echo htmlspecialchars($afiliacion); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Fecha
                        </label>
                        <input type="date" name="fecha_cita" id="inputFecha" required
                               value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-sm font-semibold transition-all">
                    </div>
                </div>

                <!-- Unidad médica -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Unidad médica
                    </label>
                    <select name="unidad_medica" id="inputUnidad" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-sm font-semibold transition-all">
                        <option value="Consulta externa · Zona 9">Consulta externa · Zona 9</option>
                        <option value="Hospital General Dr. Juan José Arévalo">Hospital General Dr. Juan José Arévalo · Zona 6</option>
                        <option value="Policlínica Central · Zona 1">Policlínica Central · Zona 1</option>
                        <option value="Unidad Asistencial · Villa Nueva">Unidad Asistencial · Villa Nueva</option>
                    </select>
                </div>

                <!-- Horarios disponibles -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                        Horarios disponibles
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button type="button" onclick="seleccionarHorario('08:00', this)" class="btn-hora py-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all text-center">
                            08:00
                        </button>
                        <button type="button" onclick="seleccionarHorario('09:30', this)" class="btn-hora py-3 rounded-2xl border-2 border-blue-900 bg-blue-50 text-blue-950 text-xs font-bold transition-all text-center">
                            09:30
                        </button>
                        <button type="button" onclick="seleccionarHorario('11:00', this)" class="btn-hora py-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all text-center">
                            11:00
                        </button>
                        <button type="button" onclick="seleccionarHorario('14:00', this)" class="btn-hora py-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all text-center">
                            14:00
                        </button>
                    </div>
                </div>

                <!-- Checkbox de Consentimiento -->
                <div class="pt-2">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="consentimiento" required checked
                               class="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-700 mt-0.5">
                        <span class="text-xs text-slate-600 leading-normal">
                            Autorizo a <strong>IGSS</strong> a recibir esta solicitud. Puedo revocarlo en cualquier momento.
                        </span>
                    </label>
                    <p class="text-[11px] text-slate-400 mt-2 pl-7">
                        Tu historial médico se consulta solo bajo demanda y nunca se guarda en el SISC.
                    </p>
                </div>

            </div>

            <!-- Columna Derecha: Tarjeta de Resumen -->
            <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                <h3 class="font-extrabold text-slate-900 text-base">
                    Resumen de tu cita
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Unidad</span>
                        <p id="resumenUnidad" class="font-bold text-slate-800 text-sm mt-0.5">Consulta externa · Zona 9</p>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Fecha y hora</span>
                        <p id="resumenFechaHora" class="font-bold text-slate-800 text-sm mt-0.5">
                            <?php echo date('d M Y', strtotime('+1 day')); ?> · 09:30
                        </p>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Afiliación</span>
                        <p class="font-mono font-bold text-slate-800 mt-0.5">•••• <?php echo htmlspecialchars($afiliacion); ?></p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" id="btnPedirCita" class="w-full py-3.5 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                        <span>Solicitar cita</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <a href="inicio_ciudadano.php" class="block text-center mt-2 text-xs text-slate-500 hover:text-slate-800 font-semibold py-2">
                        Cancelar
                    </a>
                </div>
            </div>

        </form>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function seleccionarHorario(hora, el) {
        document.getElementById('inputHorario').value = hora;
        document.querySelectorAll('.btn-hora').forEach(b => {
            b.className = 'btn-hora py-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all text-center';
        });
        el.className = 'btn-hora py-3 rounded-2xl border-2 border-blue-900 bg-blue-50 text-blue-950 text-xs font-bold transition-all text-center';
        actualizarResumen();
    }

    function actualizarResumen() {
        const unidad = document.getElementById('inputUnidad').value;
        const fecha = document.getElementById('inputFecha').value;
        const hora = document.getElementById('inputHorario').value;

        document.getElementById('resumenUnidad').innerText = unidad;
        document.getElementById('resumenFechaHora').innerText = `${fecha} · ${hora}`;
    }

    document.getElementById('inputUnidad').addEventListener('change', actualizarResumen);
    document.getElementById('inputFecha').addEventListener('change', actualizarResumen);

    document.getElementById('formCitaIgss').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnPedirCita');
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Agendando cita...`;

        const formData = new FormData(this);

        fetch('../ajax/solicitud.php?op=guardar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡Cita médica agendada exitosamente en el IGSS! Código de solicitud: ${data.numero_seguimiento}`);
                window.location.href = `seguimiento.php?id=${encodeURIComponent(data.numero_seguimiento)}`;
            } else {
                alert('Error: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<span>Solicitar cita</span> <i class="fa-solid fa-arrow-right"></i>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión al agendar cita.');
            btn.disabled = false;
            btn.innerHTML = `<span>Solicitar cita</span> <i class="fa-solid fa-arrow-right"></i>`;
        });
    });
    </script>

</body>
</html>

