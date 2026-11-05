<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Usuario.php";
$u = usuarioActual();

// Obtener datos fiscales del ciudadano
$ciudadano = ejecutarConsultaSimpleFila("SELECT * FROM ciudadanos WHERE ciudadano_id = {$u['id']}");
$nitActual = $ciudadano['nit'] ?? '1234567-8';
$direccionActual = $ciudadano['direccion'] ?? '12 calle 5-30, zona 2, Villa Nueva';
$telefonoActual = $ciudadano['telefono'] ?? '5512 3456';
$emailActual = $ciudadano['email'] ?? 'carlos.ramirez@correo.com';

// Buscar si ya tiene una solicitud previa de RTU
$solSat = ejecutarConsultaSimpleFila("SELECT s.* FROM solicitudes s 
                                      INNER JOIN flujos f ON s.flujo_id = f.flujo_id 
                                      WHERE s.ciudadano_id = {$u['id']} AND f.tipo_proceso = 'ACT_RTU_SAT' 
                                      ORDER BY s.solicitud_id DESC LIMIT 1");
$datosForm = $solSat ? (json_decode($solSat['datos_formulario'], true) ?: []) : [];
$actividadActual = $datosForm['actividad_economica'] ?? 'Servicios de Consultoría y Desarrollo de Software';
$regimenActual = $datosForm['regimen'] ?? 'Pequeño Contribuyente (Tarifa 5%)';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios Tributarios SAT | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal -->
    <main class="flex-grow max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            TRÁMITES › SAT · SUPERINTENDENCIA DE ADMINISTRACIÓN TRIBUTARIA
        </div>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900">
                    Servicios Tributarios SAT
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Gestión de RTU Digital, Solvencia Fiscal y Régimen Tributario en línea
                </p>
            </div>

            <!-- Tabs de Navegación -->
            <div class="bg-white p-1 rounded-2xl border border-slate-200 shadow-sm flex gap-1 text-xs">
                <button type="button" onclick="cambiarTab('actualizar')" id="tabBtnActualizar" class="px-4 py-2 rounded-xl font-bold bg-[#1d4289] text-white transition-all">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Actualizar RTU
                </button>
                <button type="button" onclick="cambiarTab('constancia')" id="tabBtnConstancia" class="px-4 py-2 rounded-xl font-semibold text-slate-600 hover:text-slate-900 transition-all">
                    <i class="fa-solid fa-file-invoice text-blue-900 me-1"></i> Constancia RTU Digital
                </button>
            </div>
        </div>

        <!-- SECCIÓN 1: FORMULARIO DE ACTUALIZACIÓN DE RTU -->
        <div id="seccionActualizar" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Actualización de Registro Tributario Unificado</h2>
                        <p class="text-xs text-slate-500">Modifica tu actividad comercial, régimen o dirección fiscal</p>
                    </div>
                    <span class="text-xs font-mono font-bold bg-blue-100 text-blue-900 px-2.5 py-1 rounded-lg">
                        NIT: <?php echo htmlspecialchars($nitActual ?: 'Pendiente'); ?>
                    </span>
                </div>

                <form id="formSatRtu" class="space-y-5">
                    <input type="hidden" name="tipo_proceso" value="ACT_RTU_SAT">
                    <input type="hidden" name="prioridad" value="NORMAL">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Número de Identificación Tributaria (NIT)
                            </label>
                            <input type="text" name="nit" id="nit" required
                                   value="<?php echo htmlspecialchars($nitActual); ?>"
                                   placeholder="Ej. 1234567-8"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-sm font-mono font-bold transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                CUI / DPI Asociado
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($u['cui']); ?>" readonly
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-100 text-sm font-mono font-bold text-slate-500 cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Actividad Económica -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Actividad Económica Principal
                        </label>
                        <select name="actividad_economica" id="actividad_economica" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-xs font-semibold transition-all">
                            <option value="Servicios de Consultoría y Desarrollo de Software" <?php echo ($actividadActual === 'Servicios de Consultoría y Desarrollo de Software') ? 'selected' : ''; ?>>Servicios de Consultoría y Desarrollo de Software</option>
                            <option value="Servicios Profesionales Técnicos y Científicos" <?php echo ($actividadActual === 'Servicios Profesionales Técnicos y Científicos') ? 'selected' : ''; ?>>Servicios Profesionales Técnicos y Científicos</option>
                            <option value="Comercio al por Mayor y Menor" <?php echo ($actividadActual === 'Comercio al por Mayor y Menor') ? 'selected' : ''; ?>>Comercio al por Mayor y Menor</option>
                            <option value="Transporte y Logística" <?php echo ($actividadActual === 'Transporte y Logística') ? 'selected' : ''; ?>>Transporte y Logística</option>
                            <option value="Servicios Médicos y de Salud" <?php echo ($actividadActual === 'Servicios Médicos y de Salud') ? 'selected' : ''; ?>>Servicios Médicos y de Salud</option>
                        </select>
                    </div>

                    <!-- Régimen Tributario -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Régimen del Impuesto al Valor Agregado (IVA / ISR)
                        </label>
                        <select name="regimen" id="regimen" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-xs font-semibold transition-all">
                            <option value="Pequeño Contribuyente (Tarifa 5%)" <?php echo ($regimenActual === 'Pequeño Contribuyente (Tarifa 5%)') ? 'selected' : ''; ?>>Pequeño Contribuyente (Tarifa 5% mensual simplificada)</option>
                            <option value="Régimen Opcional Simplificado Sobre Ingresos (5% y 7%)" <?php echo ($regimenActual === 'Régimen Opcional Simplificado Sobre Ingresos (5% y 7%)') ? 'selected' : ''; ?>>Régimen Opcional Simplificado Sobre Ingresos (5% y 7%)</option>
                            <option value="Régimen Sobre las Utilidades de Actividades Lucrativas (25%)" <?php echo ($regimenActual === 'Régimen Sobre las Utilidades de Actividades Lucrativas (25%)') ? 'selected' : ''; ?>>Régimen Sobre las Utilidades de Actividades Lucrativas (25%)</option>
                        </select>
                    </div>

                    <!-- Dirección Fiscal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Dirección Fiscal Declarada
                        </label>
                        <input type="text" name="direccion_fiscal" id="direccion_fiscal" required
                               value="<?php echo htmlspecialchars($direccionActual); ?>"
                               placeholder="Ej. 12 calle 5-30, zona 2, Villa Nueva"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-sm font-semibold transition-all">
                    </div>

                    <!-- Teléfono y Correo Fiscal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono Fiscal</label>
                            <input type="text" name="telefono" required value="<?php echo htmlspecialchars($telefonoActual); ?>"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white text-sm font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Notificaciones SAT</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($emailActual); ?>"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white text-sm font-semibold">
                        </div>
                    </div>

                    <!-- Consentimiento -->
                    <div class="p-4 bg-blue-50 rounded-2xl border border-blue-200 text-xs text-blue-950 flex items-start gap-3">
                        <input type="checkbox" name="consentimiento" required checked class="w-4 h-4 text-blue-900 rounded border-slate-300 mt-0.5">
                        <span class="leading-relaxed">
                            Declaro bajo juramento que los datos tributarios consignados son verdaderos y autorizo a la <strong>SAT</strong> a sincronizar mi domicilio fiscal con las demás entidades del Estado a través del SISC.
                        </span>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <a href="inicio_ciudadano.php" class="flex-1 py-3 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition-all">
                            Cancelar
                        </a>
                        <button type="submit" id="btnEnviarSat" class="flex-1 py-3 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                            <span>Actualizar RTU</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                </form>

            </div>

            <!-- Columna Derecha: Estado y Beneficios SAT -->
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Estado de Solvencia Fiscal</span>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-sm">Contribuyente Solvente</h4>
                            <span class="text-xs text-emerald-600 font-semibold">Sin omisos ni adeudos</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed pt-2 border-t border-slate-100">
                        Tu información tributaria está interconectada con el IGSS, RENAP y Municipalidades mediante el ESB del SISC.
                    </p>
                    <button type="button" onclick="cambiarTab('constancia')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all">
                        Ver Constancia Oficial
                    </button>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3 text-xs">
                    <h4 class="font-bold text-slate-900">Operaciones Disponibles SAT:</h4>
                    <ul class="space-y-2 text-slate-600">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Actualización de datos RTU digital</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Descarga de Constancia con código QR</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Consulta de estado de solvencia</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Trazabilidad interinstitucional</li>
                    </ul>
                </div>

            </div>

        </div>

        <!-- SECCIÓN 2: CONSTANCIA DE RTU DIGITAL OFICIAL -->
        <div id="seccionConstancia" class="hidden max-w-3xl mx-auto space-y-6">
            
            <div class="bg-white p-8 sm:p-10 rounded-3xl border-2 border-slate-300 shadow-xl space-y-6 text-slate-800 relative" id="printRtu">
                
                <!-- Encabezado Oficial SAT -->
                <div class="flex items-center justify-between pb-6 border-b-2 border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-900 text-white flex items-center justify-center text-2xl font-black">
                            SAT
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-base leading-tight">SUPERINTENDENCIA DE ADMINISTRACIÓN TRIBUTARIA</h3>
                            <p class="text-xs text-slate-500">Registro Tributario Unificado · República de Guatemala</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-300">
                            ACTIVO Y SOLVENTE
                        </span>
                        <p class="text-[10px] text-slate-400 mt-1">SISC Interoperabilidad</p>
                    </div>
                </div>

                <!-- Datos del Contribuyente -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">NIT</span>
                        <span class="font-bold text-base text-blue-950"><?php echo htmlspecialchars($nitActual); ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">CUI / DPI</span>
                        <span class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($u['cui']); ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">Nombre del Contribuyente</span>
                        <span class="font-sans font-bold text-base text-slate-900"><?php echo htmlspecialchars($u['nombre_completo']); ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">Domicilio Fiscal</span>
                        <span class="font-sans font-semibold text-slate-800"><?php echo htmlspecialchars($direccionActual); ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">Actividad Económica</span>
                        <span class="font-sans font-semibold text-slate-800"><?php echo htmlspecialchars($actividadActual); ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-[10px] text-slate-400 uppercase font-sans font-bold block">Régimen Afiliado</span>
                        <span class="font-sans font-bold text-blue-900"><?php echo htmlspecialchars($regimenActual); ?></span>
                    </div>
                </div>

                <!-- Código QR y Validación Digital -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-4 border-t border-slate-100 text-xs">
                    <div class="space-y-1">
                        <p class="font-bold text-slate-900">Validación Digital Criptográfica</p>
                        <p class="text-slate-500 text-[11px] max-w-md">
                            Esta constancia ha sido emitida a través del Sistema Interinstitucional de Servicios Ciudadanos (SISC) y cuenta con plena validez legal conforme al Decreto 47-2008.
                        </p>
                        <p class="font-mono text-[10px] text-slate-400">Emisión: <?php echo date('d/m/Y H:i'); ?> · UUID: SAT-<?php echo strtoupper(bin2hex(random_bytes(6))); ?></p>
                    </div>

                    <div class="bg-white p-2 border border-slate-300 rounded-xl shadow-sm shrink-0">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=SAT_RTU:NIT:<?php echo $nitActual; ?>:CUI:<?php echo $u['cui']; ?>:ESTADO:ACTIVO" alt="QR SAT" class="w-24 h-24">
                    </div>
                </div>

            </div>

            <!-- Botones de Acción -->
            <div class="flex gap-3 justify-center">
                <button type="button" onclick="window.print()" class="px-6 py-3 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Imprimir Constancia de RTU
                </button>
                <button type="button" onclick="cambiarTab('actualizar')" class="px-6 py-3 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-xs font-bold rounded-2xl transition-all">
                    Volver al Formulario
                </button>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function cambiarTab(tab) {
        if (tab === 'actualizar') {
            document.getElementById('seccionActualizar').classList.remove('hidden');
            document.getElementById('seccionConstancia').classList.add('hidden');
            document.getElementById('tabBtnActualizar').className = 'px-4 py-2 rounded-xl font-bold bg-[#1d4289] text-white transition-all';
            document.getElementById('tabBtnConstancia').className = 'px-4 py-2 rounded-xl font-semibold text-slate-600 hover:text-slate-900 transition-all';
        } else {
            document.getElementById('seccionActualizar').classList.add('hidden');
            document.getElementById('seccionConstancia').classList.remove('hidden');
            document.getElementById('tabBtnConstancia').className = 'px-4 py-2 rounded-xl font-bold bg-[#1d4289] text-white transition-all';
            document.getElementById('tabBtnActualizar').className = 'px-4 py-2 rounded-xl font-semibold text-slate-600 hover:text-slate-900 transition-all';
        }
    }

    document.getElementById('formSatRtu').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnEnviarSat');
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Conectando con SAT...`;

        const formData = new FormData(this);

        fetch('../ajax/solicitud.php?op=guardar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡Trámite de RTU ante la SAT registrado exitosamente!\nNúmero de seguimiento: ${data.numero_seguimiento}`);
                window.location.href = `seguimiento.php?id=${encodeURIComponent(data.numero_seguimiento)}`;
            } else {
                alert('Error al actualizar RTU: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<span>Actualizar RTU</span> <i class="fa-solid fa-arrow-right"></i>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de comunicación con el servidor.');
            btn.disabled = false;
            btn.innerHTML = `<span>Actualizar RTU</span> <i class="fa-solid fa-arrow-right"></i>`;
        });
    });
    </script>

</body>
</html>

