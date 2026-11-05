<?php
if (!function_exists('usuarioActual')) {
    require_once "validar_sesion.php";
}
$user = usuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!-- Navbar Superior Ciudadano (Diseño Fase 3) -->
<header class="bg-[#1d4289] text-white shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Marca y Enlaces de Navegación -->
        <div class="flex items-center space-x-6">
            <a href="inicio_ciudadano.php" class="flex items-center space-x-2 text-white no-underline">
                <span class="text-2xl font-black tracking-widest">SISC</span>
                <span class="text-[10px] bg-blue-800 text-blue-200 px-2 py-0.5 rounded font-mono font-bold hidden sm:inline">GT</span>
            </a>

            <nav class="hidden md:flex items-center space-x-2 text-xs font-semibold">
                <a href="inicio_ciudadano.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'inicio_ciudadano.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Inicio
                </a>

                <!-- Dropdown Trámites -->
                <div class="relative group">
                    <button class="px-3 py-1.5 rounded-full text-blue-100 hover:bg-blue-800 hover:text-white flex items-center gap-1.5 transition-all <?php echo in_array($paginaActual, ['tramite_renap.php', 'tramite_sat.php', 'tramite_igss.php', 'seguimiento.php']) ? 'bg-blue-900 text-white font-bold' : ''; ?>">
                        <span>Trámites</span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div class="absolute left-0 mt-1 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 hidden group-hover:block z-50 text-slate-800">
                        <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Iniciar Solicitud</div>
                        <a href="tramite_renap.php" class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-address-card text-blue-600"></i> Actualizar datos · RENAP
                        </a>
                        <a href="tramite_sat.php" class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-receipt text-indigo-600"></i> RTU y Tributario · SAT
                        </a>
                        <a href="tramite_igss.php" class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-notes-medical text-teal-600"></i> Pedir cita médica · IGSS
                        </a>
                        <a href="pago_servicio.php?tipo=irtra" class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-hotel text-amber-600"></i> Reservación · IRTRA
                        </a>
                        <a href="pago_servicio.php?tipo=transmetro" class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 transition-colors">
                            <i class="fa-solid fa-bus text-emerald-600"></i> Recarga · Transmetro
                        </a>
                    </div>
                </div>

                <a href="historial_pagos.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'historial_pagos.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Pagos
                </a>

                <button onclick="abrirModalCredencial()" class="px-3 py-1.5 rounded-full text-blue-100 hover:bg-blue-800 hover:text-white transition-all flex items-center gap-1">
                    <i class="fa-solid fa-qrcode text-xs"></i> Credencial
                </button>
            </nav>
        </div>

        <!-- Usuario y Salir -->
        <div class="flex items-center space-x-3 text-xs">
            <span class="text-blue-100 font-semibold hidden sm:inline">
                <?php echo htmlspecialchars($user["nombre_completo"]); ?>
            </span>
            <span class="text-blue-300 hidden sm:inline">·</span>
            <a href="../ajax/usuario.php?op=salir" class="text-blue-200 hover:text-white font-semibold transition-colors flex items-center gap-1">
                <span>Salir</span>
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>
</header>

<!-- Modal de Credencial Electrónica Móvil -->
<div id="modalCredencial" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl relative border border-slate-200">
        <button onclick="cerrarModalCredencial()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <span class="text-[10px] font-bold tracking-wider text-blue-900 uppercase block mb-1">REPÚBLICA DE GUATEMALA</span>
        <h3 class="text-lg font-black text-slate-900 mb-4">Credencial Electrónica Ciudadana</h3>

        <!-- Código QR Dinámico -->
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 inline-block mb-4 shadow-inner">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=SISC_GT:CUI:<?php echo $user['cui']; ?>:EXP:2030" alt="QR Credencial" class="w-40 h-40 mx-auto rounded-lg">
        </div>

        <div class="space-y-1 text-center">
            <h4 class="font-extrabold text-slate-900 text-base"><?php echo htmlspecialchars($user["nombre_completo"]); ?></h4>
            <p class="text-xs font-mono font-bold text-slate-600">
                CUI •••• •••• <?php echo substr($user['cui'], -4) ?: '0123'; ?>
            </p>
            <span class="inline-block text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-3 py-0.5 rounded-full border border-emerald-200 mt-2">
                <i class="fa-solid fa-circle-check text-emerald-500 me-1"></i> Vigente
            </span>
        </div>

        <p class="text-[11px] text-slate-400 mt-4 leading-relaxed">
            El código QR se renueva automáticamente y es válido ante todas las entidades del Estado.
        </p>

        <button onclick="cerrarModalCredencial()" class="w-full mt-5 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-xl transition-all shadow">
            Cerrar Credencial
        </button>
    </div>
</div>

<script>
function abrirModalCredencial() {
    document.getElementById('modalCredencial').classList.remove('hidden');
}
function cerrarModalCredencial() {
    document.getElementById('modalCredencial').classList.add('hidden');
}
</script>

