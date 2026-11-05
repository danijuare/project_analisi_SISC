<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Pago.php";
$u = usuarioActual();
$esAdmin = ($u["rol_nombre"] === 'ADMINISTRADOR');

$pagoModel = new Pago();
$instId = $esAdmin ? 0 : $u["institucion_id"];

$metricas = $pagoModel->obtenerMetricas($instId, $esAdmin);
$pagos = $pagoModel->listarPorInstitucion($instId, '', '', $esAdmin);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Recibidos | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior Funcionario -->
    <?php include "nav_funcionario.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 10) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900">
                    Pagos recibidos · <?php echo htmlspecialchars($u["institucion_codigo"] ?: 'SISC'); ?>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Control financiero y conciliación de aranceles captados mediante pasarelas autorizadas
                </p>
            </div>

            <a href="../ajax/pago.php?op=exportar_csv" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold rounded-2xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv text-emerald-600 text-sm"></i>
                <span>Exportar conciliación</span>
            </a>
        </div>

        <!-- Tarjetas de Métricas Financieras (Fase 3 Pantalla 10) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Cobros aprobados -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                    Cobros aprobados
                </span>
                <h3 class="text-3xl font-black text-slate-900">
                    <?php echo $metricas['cobros_aprobados']; ?>
                </h3>
                <span class="text-[11px] text-emerald-600 font-semibold block pt-1">
                    <i class="fa-solid fa-arrow-trend-up me-1"></i> Acreditados en cuenta única
                </span>
            </div>

            <!-- Pendientes de conciliar -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                    Pendientes de conciliar
                </span>
                <h3 class="text-3xl font-black text-slate-900">
                    <?php echo $metricas['pendientes_conciliar']; ?>
                </h3>
                <span class="text-[11px] text-amber-600 font-semibold block pt-1">
                    En corte bancario
                </span>
            </div>

            <!-- Total del periodo -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">
                    Total del periodo
                </span>
                <h3 class="text-3xl font-black text-[#1d4289]">
                    Q <?php echo number_format($metricas['total_periodo'], 2); ?>
                </h3>
                <span class="text-[11px] text-slate-400 block pt-1">
                    Recaudación neta SISC GT
                </span>
            </div>

        </div>

        <!-- Tabla de Pagos Recibidos (Fase 3 Pantalla 10) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Fecha</th>
                            <th class="py-3.5 px-6">Solicitud</th>
                            <th class="py-3.5 px-6">Pasarela</th>
                            <th class="py-3.5 px-6">Monto</th>
                            <th class="py-3.5 px-6">Autorización</th>
                            <th class="py-3.5 px-6 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($pagos && $pagos->num_rows > 0): ?>
                            <?php while ($p = $pagos->fetch_assoc()): ?>
                                <?php
                                    $fechaTexto = date('d M', strtotime($p['fecha_pago']));
                                    $solTexto = $p['numero_seguimiento'] ?: 'Recarga Directa';
                                    $authTexto = $p['referencia_pasarela'] ?: '—';
                                    $estado = $p['estado'];

                                    $badge = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                                    if ($estado === 'Rechazado') {
                                        $badge = 'bg-rose-100 text-rose-800 border-rose-300';
                                    } elseif ($estado === 'Anulado') {
                                        $badge = 'bg-slate-100 text-slate-600 border-slate-300';
                                    }
                                ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-semibold text-slate-600 whitespace-nowrap">
                                        <?php echo $fechaTexto; ?>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-blue-900 whitespace-nowrap">
                                        <?php echo htmlspecialchars($solTexto); ?>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-700 whitespace-nowrap">
                                        <?php echo htmlspecialchars($p['metodo_pago']); ?>
                                    </td>
                                    <td class="py-4 px-6 font-black text-slate-900 text-sm whitespace-nowrap">
                                        Q <?php echo number_format($p['monto'], 2); ?>
                                    </td>
                                    <td class="py-4 px-6 font-mono text-slate-600 whitespace-nowrap">
                                        <?php echo htmlspecialchars($authTexto); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                    No hay registros de pagos para mostrar.
                                </td>
                            </tr>
                        <?php endif; ?>
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

