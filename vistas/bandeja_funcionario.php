<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Solicitud.php";
$u = usuarioActual();
$esAdmin = ($u["rol_nombre"] === 'ADMINISTRADOR');

$estadoFiltro = $_GET["estado"] ?? 'Todos';
$busqueda = $_GET["q"] ?? '';
$instFiltro = intval($_GET["institucion"] ?? ($esAdmin ? 0 : $u["institucion_id"]));

$solModel = new Solicitud();
$solicitudes = $solModel->listarParaFuncionario($instFiltro, $estadoFiltro, $busqueda, $esAdmin);

// Lista de instituciones para filtro de admin
$instituciones = ejecutarConsulta("SELECT institucion_id, codigo, nombre FROM instituciones WHERE activa = 1 ORDER BY institucion_id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Solicitudes | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior Funcionario (Diseño Fase 3) -->
    <?php include "nav_funcionario.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 08) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900">
                    Bandeja de solicitudes
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Gestión interinstitucional de expedientes ciudadanos recibidos
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold bg-white border border-slate-200 text-slate-700 px-3 py-1.5 rounded-xl shadow-sm">
                    <i class="fa-solid fa-building-columns text-blue-900 me-1"></i>
                    <?php echo htmlspecialchars($u["institucion_nombre"]); ?>
                </span>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <form method="GET" action="bandeja_funcionario.php" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
            
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3 text-slate-400 text-sm"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>"
                       placeholder="Buscar por número o CUI..."
                       class="w-full pl-11 pr-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-700 text-xs font-semibold transition-all">
            </div>

            <div class="w-full md:w-56">
                <select name="institucion" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-xs font-semibold">
                    <option value="0">Todas las Instituciones</option>
                    <?php while ($in = $instituciones->fetch_assoc()): ?>
                        <option value="<?php echo $in['institucion_id']; ?>" <?php echo ($instFiltro == $in['institucion_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($in['codigo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="w-full md:w-52">
                <select name="estado" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-xs font-semibold">
                    <option value="Todos" <?php echo ($estadoFiltro === 'Todos') ? 'selected' : ''; ?>>Filtrar por estado (Todos)</option>
                    <option value="En proceso" <?php echo ($estadoFiltro === 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                    <option value="Pendiente de pago" <?php echo ($estadoFiltro === 'Pendiente de pago') ? 'selected' : ''; ?>>Pendiente de pago</option>
                    <option value="Finalizada" <?php echo ($estadoFiltro === 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    <option value="Rechazada" <?php echo ($estadoFiltro === 'Rechazada') ? 'selected' : ''; ?>>Rechazada</option>
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none px-5 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white rounded-2xl text-xs font-bold transition-all shadow-sm">
                    Buscar
                </button>
                <?php if ($busqueda || $estadoFiltro !== 'Todos'): ?>
                <a href="bandeja_funcionario.php" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-xs font-bold transition-all">
                    Limpiar
                </a>
                <?php endif; ?>
            </div>

        </form>

        <!-- Tabla de Solicitudes (Diseño Fase 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Número</th>
                            <th class="py-3.5 px-6">Ciudadano</th>
                            <th class="py-3.5 px-6">Proceso</th>
                            <th class="py-3.5 px-6">Paso actual</th>
                            <th class="py-3.5 px-6">Recibida</th>
                            <th class="py-3.5 px-6">Estado</th>
                            <th class="py-3.5 px-6 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($solicitudes && $solicitudes->num_rows > 0): ?>
                            <?php while ($s = $solicitudes->fetch_assoc()): ?>
                                <?php
                                    $estado = $s['estado'];
                                    $badge = "bg-amber-100 text-amber-800 border-amber-300";
                                    if ($estado === 'Finalizada') {
                                        $badge = "bg-emerald-100 text-emerald-800 border-emerald-300";
                                    } elseif ($estado === 'Pendiente de pago') {
                                        $badge = "bg-blue-100 text-blue-800 border-blue-300";
                                    } elseif ($estado === 'Rechazada') {
                                        $badge = "bg-rose-100 text-rose-800 border-rose-300";
                                    }
                                    
                                    $pasoTexto = ($s['numero_paso'] ?? 2) . " · " . ($s['paso_nombre'] ?? 'Revisión');
                                    $fechaTexto = date('d M Y', strtotime($s['fecha_inicio']));
                                    $cuiEnmascarado = "•••• " . (substr($s['cui'], -4) ?: '0123');
                                    $esParaRevisar = ($estado === 'En proceso');
                                ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                        <?php echo htmlspecialchars($s['numero_seguimiento']); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900 text-sm">
                                            <?php echo htmlspecialchars($s['ciudadano_nombre']); ?>
                                        </p>
                                        <span class="font-mono text-slate-400 text-xs">
                                            CUI <?php echo htmlspecialchars($cuiEnmascarado); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-700">
                                        <?php echo htmlspecialchars($s['flujo_nombre']); ?>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-600">
                                        <?php echo htmlspecialchars($pasoTexto); ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                        <?php echo $fechaTexto; ?>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <?php if ($esParaRevisar): ?>
                                            <a href="revisar_solicitud.php?id=<?php echo urlencode($s['numero_seguimiento']); ?>" class="px-4 py-1.5 rounded-full bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold transition-all shadow-sm inline-block">
                                                <i class="fa-solid fa-stamp text-[10px] me-1"></i> Revisar y Finalizar
                                            </a>
                                        <?php else: ?>
                                            <a href="revisar_solicitud.php?id=<?php echo urlencode($s['numero_seguimiento']); ?>" class="px-4 py-1.5 rounded-full border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold transition-all shadow-sm inline-block">
                                                Ver Expediente
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No se encontraron solicitudes con los criterios de búsqueda.
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
