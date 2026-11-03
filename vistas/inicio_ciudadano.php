<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

$u = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ciudadano | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <header class="bg-[#1d4289] text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-black tracking-widest">SISC</span>
                <span class="text-xs bg-blue-800 text-blue-200 px-2 py-0.5 rounded font-mono font-bold hidden sm:inline">CIUDADANO</span>
                <span class="text-xs text-blue-200 hidden md:inline border-l border-blue-700 pl-3">Portal Interinstitucional de Servicios Ciudadanos</span>
            </div>

            <!-- Datos de Usuario y Salir -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-white"><?php echo htmlspecialchars($u["nombre_completo"]); ?></p>
                    <p class="text-[11px] text-blue-200 font-mono">CUI: <?php echo htmlspecialchars($u["cui"]); ?></p>
                </div>
                <div class="w-9 h-9 rounded-full bg-blue-700 border-2 border-blue-400 flex items-center justify-center font-bold text-sm text-white">
                    <i class="fa-solid fa-user"></i>
                </div>
                <a href="../ajax/usuario.php?op=salir" class="text-xs bg-rose-600 hover:bg-rose-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">
        
        <!-- Tarjeta de Identidad Ciudadana -->
        <div class="bg-gradient-to-r from-[#1d4289] to-[#2563eb] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <div class="absolute right-0 top-0 bottom-0 opacity-10 pointer-events-none flex items-center pr-10">
                <i class="fa-solid fa-id-card text-9xl"></i>
            </div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 text-xs px-3 py-1 rounded-full font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i> Identidad Verificada por RENAP
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <div class="md:col-span-8">
                        <h1 class="text-2xl sm:text-3xl font-black">
                            Bienvenido, <?php echo htmlspecialchars($u["nombre_completo"]); ?>
                        </h1>
                        <p class="text-sm text-blue-100 mt-1">
                            Tu expediente único ciudadano está activo y conectado con RENAP, SAT, IGSS, IRTRA y Transmetro.
                        </p>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 text-xs">
                            <div class="bg-blue-950/40 p-2.5 rounded-xl border border-blue-400/20">
                                <span class="text-[10px] text-blue-300 block uppercase">CUI / DPI</span>
                                <span class="font-mono font-bold text-white"><?php echo htmlspecialchars($u["cui"] ?: 'No asignado'); ?></span>
                            </div>
                            <div class="bg-blue-950/40 p-2.5 rounded-xl border border-blue-400/20">
                                <span class="text-[10px] text-blue-300 block uppercase">NIT SAT</span>
                                <span class="font-mono font-bold text-white"><?php echo htmlspecialchars($u["nit"] ?: 'Sin NIT'); ?></span>
                            </div>
                            <div class="bg-blue-950/40 p-2.5 rounded-xl border border-blue-400/20">
                                <span class="text-[10px] text-blue-300 block uppercase">Afiliación IGSS</span>
                                <span class="font-mono font-bold text-white"><?php echo htmlspecialchars($u["igss"] ?: 'Sin IGSS'); ?></span>
                            </div>
                            <div class="bg-blue-950/40 p-2.5 rounded-xl border border-blue-400/20">
                                <span class="text-[10px] text-blue-300 block uppercase">Rol Activo</span>
                                <span class="font-mono font-bold text-amber-300"><?php echo htmlspecialchars($u["rol_nombre"]); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4 flex flex-col sm:flex-row md:flex-col gap-2 justify-center">
                        <button onclick="alert('Credencial Digital SISC: Código CUI <?php echo $u['cui']; ?> verificado');" class="bg-white text-blue-950 font-bold px-4 py-2.5 rounded-xl text-xs hover:bg-blue-50 transition-all shadow flex items-center justify-center gap-2">
                            <i class="fa-solid fa-qrcode text-base"></i> Ver Credencial Digital Móvil
                        </button>
                        <button onclick="alert('Expediente Ciudadano Unificado: Sin anomalías');" class="bg-blue-900/80 hover:bg-blue-900 text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition-all border border-blue-400/30 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-file-contract"></i> Mi Ficha Interinstitucional
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos Exclusivos del Ciudadano -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Módulos de Servicios Ciudadanos</h2>
                    <p class="text-xs text-slate-500">Accede directamente a tus gestiones y servicios del Estado</p>
                </div>
                <span class="text-xs font-semibold bg-blue-100 text-blue-900 px-3 py-1 rounded-full">
                    6 Módulos Disponibles
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Módulo 1: Mis Trámites -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Mis Trámites en Curso</h3>
                    <p class="text-xs text-slate-500 mb-4">Consulta el estado, historial y trazabilidad en tiempo real de tus solicitudes.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">1 En Revisión</span>
                        <a href="#" onclick="alert('Listado de trámites del ciudadano'); return false;" class="text-blue-900 font-bold hover:underline">Ver detalle →</a>
                    </div>
                </div>

                <!-- Módulo 2: Iniciar Nuevo Trámite -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Iniciar Nuevo Trámite</h3>
                    <p class="text-xs text-slate-500 mb-4">Solicita reposición de DPI, solvencias SAT, carné IGSS, afiliación IRTRA o recarga Transmetro.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">5 Instituciones</span>
                        <a href="#" onclick="alert('Catálogo de trámites interinstitucionales disponible en la siguiente entrega de flujos'); return false;" class="text-emerald-700 font-bold hover:underline">Solicitar →</a>
                    </div>
                </div>

                <!-- Módulo 3: Identidad Digital -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Identidad Digital Móvil</h3>
                    <p class="text-xs text-slate-500 mb-4">Tu documento de identificación oficial en formato digital avalado por RENAP con código criptográfico.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-purple-700 bg-purple-50 px-2 py-0.5 rounded">DPI Activo</span>
                        <a href="#" onclick="alert('Visualizador de Credencial Ciudadana Móvil'); return false;" class="text-purple-700 font-bold hover:underline">Abrir →</a>
                    </div>
                </div>

                <!-- Módulo 4: Pagos y Aranceles -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Pagos y Aranceles</h3>
                    <p class="text-xs text-slate-500 mb-4">Paga en línea los aranceles de tus trámites institucionales con tarjeta de débito/crédito o transferencia.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">Pasarela Segura</span>
                        <a href="#" onclick="alert('Módulo de pasarela de pagos interinstitucionales'); return false;" class="text-amber-700 font-bold hover:underline">Pagar →</a>
                    </div>
                </div>

                <!-- Módulo 5: Bóveda de Documentos -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Bóveda de Documentos</h3>
                    <p class="text-xs text-slate-500 mb-4">Descarga tus certificados de nacimiento, constancias de RTU, solvencias y resoluciones oficiales.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded">Firmas Digitales</span>
                        <a href="#" onclick="alert('Bóveda digital de documentos oficiales con validez jurídica'); return false;" class="text-sky-700 font-bold hover:underline">Explorar →</a>
                    </div>
                </div>

                <!-- Módulo 6: Notificaciones -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Notificaciones</h3>
                    <p class="text-xs text-slate-500 mb-4">Alertas automáticas por correo y SMS sobre resoluciones y vencimiento de trámites.</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded">0 No leídas</span>
                        <a href="#" onclick="alert('Centro de notificaciones ciudadanas'); return false;" class="text-rose-700 font-bold hover:underline">Ver avisos →</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Trámites Recientes (Mockup Dinámico) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-900"></i> Historial de Actividad Ciudadana
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">No. Seguimiento</th>
                            <th class="py-3 px-4">Institución</th>
                            <th class="py-3 px-4">Trámite</th>
                            <th class="py-3 px-4">Fecha Inicio</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-900">SISC-2026-0001</td>
                            <td class="py-3.5 px-4">
                                <span class="bg-blue-50 text-blue-900 font-semibold px-2 py-0.5 rounded">RENAP</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium">Validación de Identidad y Registro CUI</td>
                            <td class="py-3.5 px-4 text-slate-500">Hoy</td>
                            <td class="py-3.5 px-4">
                                <span class="bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1 rounded-full text-[10px]">
                                    ✔ COMPLETADO
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button onclick="alert('Certificado de Registro en SISC emitido correctamente');" class="text-blue-900 font-bold hover:underline">Descargar Constancia</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

</body>
</html>

