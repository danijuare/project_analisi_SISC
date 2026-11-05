<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Solicitud.php";
$u = usuarioActual();
$solModel = new Solicitud();
$misTramites = $solModel->listarPorCiudadano($u['id']);
//print_r($u['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Ciudadano | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 02) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Encabezado de Identidad -->
        <div class="space-y-1">
            <span class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">SISC · TU IDENTIDAD CIUDADANA</span>
            <h1 class="text-3xl font-black text-slate-900">
                Hola, <?php echo htmlspecialchars(explode(' ', $u["nombre_completo"])[0]); ?>
            </h1>
        </div>

        <!-- Rejilla Principal: Credencial Izquierda y Servicios Derecha -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Credencial Electrónica Móvil -->
            <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm text-center space-y-4">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                    Credencial electrónica
                </span>

                <!-- QR Generado con datos del ciudadano -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=SISC_GT:CUI:<?php echo $u['cui']; ?>:<?php echo urlencode($u['nombre_completo']); ?>" 
                         alt="QR Credencial SISC" 
                         class="w-44 h-44 mx-auto rounded-xl">
                </div>

                <div class="space-y-1">
                    <h3 class="font-extrabold text-slate-900 text-lg">
                        <?php echo htmlspecialchars($u["nombre_completo"]); ?>
                    </h3>
                    <p class="text-xs font-mono font-bold text-slate-500 tracking-wider">
                        CUI •••• •••• <?php echo substr($u['cui'], -4) ?: '0123'; ?>
                    </p>
                    <div class="pt-1">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Vigente
                        </span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 pt-2 leading-relaxed">
                    El código QR se renueva automáticamente
                </p>

                <div class="pt-2 border-t border-slate-100 flex gap-2">
                    <button onclick="abrirModalCredencial()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                        <i class="fa-solid fa-expand me-1"></i> Ampliar
                    </button>
                    <a href="tramite_renap.php" class="flex-1 py-2 bg-[#1d4289] hover:bg-blue-900 text-white rounded-xl text-xs font-bold transition-all text-center">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Actualizar
                    </a>
                </div>
            </div>

            <!-- Columna Derecha: Instituciones y Mis Trámites -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Sección Instituciones -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Instituciones</h2>
                        <span class="text-xs text-blue-900 font-semibold">Integradas en SISC</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        
                        <!-- RENAP -->
                        <a href="tramite_renap.php" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left">
                            <span class="font-bold text-slate-900 text-sm block group-hover:text-blue-900 transition-colors">RENAP</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Identidad</span>
                            <span class="text-[11px] text-blue-600 font-semibold mt-2 inline-flex items-center gap-1">
                                Actualizar datos <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                        <!-- SAT -->
                        <a href="tramite_sat.php" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left">
                            <span class="font-bold text-slate-900 text-sm block group-hover:text-blue-900 transition-colors">SAT</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Tributario</span>
                            <span class="text-[11px] text-blue-600 font-semibold mt-2 inline-flex items-center gap-1">
                                RTU y Solvencia <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                        <!-- IGSS -->
                        <a href="tramite_igss.php" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left">
                            <span class="font-bold text-slate-900 text-sm block group-hover:text-blue-900 transition-colors">IGSS</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Salud</span>
                            <span class="text-[11px] text-blue-600 font-semibold mt-2 inline-flex items-center gap-1">
                                Pedir cita <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                        <!-- IRTRA -->
                        <a href="pago_servicio.php?tipo=irtra" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left">
                            <span class="font-bold text-slate-900 text-sm block group-hover:text-blue-900 transition-colors">IRTRA</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Recreación</span>
                            <span class="text-[11px] text-amber-600 font-semibold mt-2 inline-flex items-center gap-1">
                                Reservar hostal <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                        <!-- Transmetro -->
                        <a href="pago_servicio.php?tipo=transmetro" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left">
                            <span class="font-bold text-slate-900 text-sm block group-hover:text-blue-900 transition-colors">Transmetro</span>
                            <span class="text-xs text-slate-400 block mt-0.5">Transporte</span>
                            <span class="text-[11px] text-emerald-600 font-semibold mt-2 inline-flex items-center gap-1">
                                Comprar saldo <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                        <!-- Ver todas -->
                        <a href="control_cruzado.php" class="bg-white p-4 rounded-2xl border border-dashed border-slate-300 hover:border-blue-400 shadow-sm hover:shadow transition-all group text-left flex flex-col justify-between">
                            <div>
                                <span class="font-bold text-slate-700 text-sm block">Ver todas</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Directorio</span>
                            </div>
                            <span class="text-[11px] text-slate-500 font-semibold mt-2 inline-flex items-center gap-1">
                                6 instituciones <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </span>
                        </a>

                    </div>
                </div>

                <!-- Sección Mis Trámites (En vivo desde la Base de Datos) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Mis trámites</h2>
                        <a href="tramite_renap.php" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-circle-plus"></i> Iniciar nuevo trámite
                        </a>
                    </div>

                    <div class="space-y-3">
                        <?php if ($misTramites && $misTramites->num_rows > 0): ?>
                            <?php while ($t = $misTramites->fetch_assoc()): ?>
                                <?php
                                    $estado = $t['estado'];
                                    $badgeClase = "bg-amber-100 text-amber-800 border-amber-200";
                                    if ($estado === 'Finalizada') {
                                        $badgeClase = "bg-emerald-100 text-emerald-800 border-emerald-200";
                                    } elseif ($estado === 'Pendiente de pago') {
                                        $badgeClase = "bg-blue-100 text-blue-800 border-blue-200";
                                    } elseif ($estado === 'Rechazada') {
                                        $badgeClase = "bg-rose-100 text-rose-800 border-rose-200";
                                    }

                                    // URL destino según estado
                                    $linkDestino = "seguimiento.php?id=" . urlencode($t['numero_seguimiento']);
                                    if ($estado === 'Pendiente de pago') {
                                        $linkDestino = "pago_servicio.php?solicitud=" . urlencode($t['numero_seguimiento']);
                                    }
                                ?>
                                <a href="<?php echo $linkDestino; ?>" class="block bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 hover:shadow-md transition-all group">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-blue-900 transition-colors">
                                                <?php echo htmlspecialchars($t['flujo_nombre']); ?>
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($t['institucion_codigo']); ?></span> · 
                                                <span class="font-mono"><?php echo htmlspecialchars($t['numero_seguimiento']); ?></span>
                                                <span class="text-slate-400 text-[11px] ml-2">Iniciado: <?php echo date('d M Y', strtotime($t['fecha_inicio'])); ?></span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badgeClase; ?>">
                                                <?php echo htmlspecialchars($estado); ?>
                                            </span>
                                            <?php if ($estado === 'Pendiente de pago'): ?>
                                                <span class="bg-blue-900 hover:bg-blue-950 text-white text-xs font-bold px-3 py-1 rounded-xl shadow-sm">
                                                    Pagar <i class="fa-solid fa-credit-card ms-1"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 group-hover:text-blue-900 transition-colors text-xs">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                                No tienes trámites registrados actualmente. 
                                <a href="tramite_renap.php" class="text-blue-900 font-bold underline ml-1">Inicia tu primer trámite aquí</a>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

</body>
</html>
