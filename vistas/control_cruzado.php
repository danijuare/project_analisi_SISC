<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Esb.php";
$u = usuarioActual();

$instFiltro = $_GET["institucion"] ?? 'Todas';
$estadoFiltro = $_GET["estado"] ?? 'Todos';
$busqueda = $_GET["q"] ?? '';

$esbModel = new Esb();
$registros = $esbModel->controlCruzado($instFiltro, $estadoFiltro, $busqueda);

$instituciones = ejecutarConsulta("SELECT codigo, nombre FROM instituciones WHERE activa = 1 ORDER BY institucion_id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Cruzado de Servicios | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior Funcionario / Administrador -->
    <?php include "nav_funcionario.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 11) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6" id="reportArea">
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900">
                    Control cruzado de servicios
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Matriz de trazabilidad interinstitucional: interoperabilidad unificada entre RENAP, SAT, IGSS, IRTRA y Transmetro
                </p>
            </div>

            <div class="flex items-center gap-2 no-print">
                <button onclick="window.print()" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold rounded-2xl shadow-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-rose-600 text-sm"></i>
                    <span>Exportar PDF</span>
                </button>
                <a href="../ajax/esb.php?op=exportar_csv_cruzado&institucion=<?php echo urlencode($instFiltro); ?>&estado=<?php echo urlencode($estadoFiltro); ?>" class="px-5 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-2xl shadow-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-csv text-emerald-400 text-sm"></i>
                    <span>Exportar CSV</span>
                </a>
            </div>
        </div>

        <!-- Filtros Superiores (Fase 3 Pantalla 11) -->
        <form method="GET" action="control_cruzado.php" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-3 no-print">
            
            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Institución</label>
                <select name="institucion" onchange="this.form.submit()" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold">
                    <option value="Todas" <?php echo ($instFiltro === 'Todas') ? 'selected' : ''; ?>>Todas</option>
                    <?php while ($in = $instituciones->fetch_assoc()): ?>
                        <option value="<?php echo $in['codigo']; ?>" <?php echo ($instFiltro === $in['codigo']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($in['codigo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Periodo</label>
                <select name="periodo" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold">
                    <option value="2026-10">Octubre 2026</option>
                    <option value="2026-11" selected>Noviembre 2026</option>
                    <option value="2026-09">Septiembre 2026</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Estado</label>
                <select name="estado" onchange="this.form.submit()" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold">
                    <option value="Todos" <?php echo ($estadoFiltro === 'Todos') ? 'selected' : ''; ?>>Todos</option>
                    <option value="En proceso" <?php echo ($estadoFiltro === 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                    <option value="Pendiente de pago" <?php echo ($estadoFiltro === 'Pendiente de pago') ? 'selected' : ''; ?>>Pendiente de pago</option>
                    <option value="Finalizada" <?php echo ($estadoFiltro === 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                    Aplicar Filtros
                </button>
            </div>

        </form>

        <!-- Tabla de Control Cruzado (Diseño Fase 3 Pantalla 11) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Ciudadano</th>
                            <th class="py-3.5 px-6">Institución</th>
                            <th class="py-3.5 px-6">Proceso</th>
                            <th class="py-3.5 px-6">Solicitud</th>
                            <th class="py-3.5 px-6">Fecha</th>
                            <th class="py-3.5 px-6 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($registros && $registros->num_rows > 0): ?>
                            <?php while ($r = $registros->fetch_assoc()): ?>
                                <?php
                                    $estado = $r['estado'];
                                    $badge = 'bg-amber-100 text-amber-800 border-amber-300';
                                    if ($estado === 'Finalizada') {
                                        $badge = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                                    } elseif ($estado === 'Pendiente de pago') {
                                        $badge = 'bg-blue-100 text-blue-800 border-blue-300';
                                    } elseif ($estado === 'Rechazada') {
                                        $badge = 'bg-rose-100 text-rose-800 border-rose-300';
                                    }

                                    $cuiEnmascarado = "CUI ••••" . (substr($r['cui'], -4) ?: '0123');
                                    $fechaTexto = date('d M', strtotime($r['fecha_inicio']));
                                ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-slate-900 whitespace-nowrap">
                                        <?php echo htmlspecialchars($cuiEnmascarado); ?>
                                        <span class="font-sans font-normal text-slate-500 text-[11px] block">
                                            <?php echo htmlspecialchars($r['ciudadano_nombre']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-slate-700 whitespace-nowrap">
                                        <?php echo htmlspecialchars($r['institucion_codigo']); ?>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-800">
                                        <?php echo htmlspecialchars($r['proceso_nombre']); ?>
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-blue-900 whitespace-nowrap">
                                        <?php echo htmlspecialchars($r['numero_seguimiento']); ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                        <?php echo $fechaTexto; ?>
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
                                    No se encontraron registros de servicios cruzados para el filtro especificado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto no-print">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <style>
    @media print {
        .no-print { display: none !important; }
        header { display: none !important; }
        body { background: white !important; font-size: 11px; }
        main { max-width: 100% !important; padding: 0 !important; }
        table { border: 1px solid #cbd5e1 !important; }
    }
    </style>

</body>
</html>

