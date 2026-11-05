<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Pago.php";
$u = usuarioActual();

$filtro = $_GET["filtro"] ?? 'Todos';
$pagoModel = new Pago();
$pagos = $pagoModel->listarPorCiudadano($u['id'], $filtro);
//print_r($u['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 07) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            MIS PAGOS
        </div>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <h1 class="text-3xl font-black text-slate-900">
                Historial de pagos
            </h1>

            <div class="flex gap-2">
                <a href="pago_servicio.php?tipo=transmetro" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Recargar Transmetro
                </a>
                <a href="pago_servicio.php?tipo=irtra" class="px-4 py-2 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-hotel"></i> Pagar Hostal IRTRA
                </a>
            </div>
        </div>

        <!-- Filtros tipo píldora -->
        <div class="flex items-center gap-2">
            <a href="historial_pagos.php?filtro=Todos" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?php echo ($filtro === 'Todos') ? 'bg-[#1d4289] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">
                Todos
            </a>
            <a href="historial_pagos.php?filtro=Aprobados" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?php echo ($filtro === 'Aprobados') ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">
                Aprobados
            </a>
            <a href="historial_pagos.php?filtro=Rechazados" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?php echo ($filtro === 'Rechazados') ? 'bg-rose-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'; ?>">
                Rechazados
            </a>
        </div>

        <!-- Tabla de Pagos Estilo Fase 3 -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Concepto</th>
                            <th class="py-3.5 px-6">Monto</th>
                            <th class="py-3.5 px-6">Estado</th>
                            <th class="py-3.5 px-6 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($pagos && $pagos->num_rows > 0): ?>
                            <?php while ($p = $pagos->fetch_assoc()): ?>
                                <?php
                                    $det = json_decode($p['detalle_respuesta'], true) ?: [];
                                    $conceptoTexto = $det['concepto'] ?? ($p['flujo_nombre'] ?: 'Pago de Trámite');
                                    $fechaTexto = date('d M Y', strtotime($p['fecha_pago']));
                                    $esAprobado = ($p['estado'] === 'Aprobado');
                                    $badge = $esAprobado ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300';
                                ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900 text-sm">
                                            <?php echo htmlspecialchars($conceptoTexto); ?>
                                        </p>
                                        <p class="text-slate-400 text-xs mt-0.5">
                                            <?php echo $fechaTexto; ?> · 
                                            <span class="font-semibold text-slate-600"><?php echo htmlspecialchars($p['metodo_pago']); ?></span>
                                            <?php if (!empty($p['numero_seguimiento'])): ?>
                                                · <span class="font-mono text-blue-900 font-semibold"><?php echo htmlspecialchars($p['numero_seguimiento']); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                    <td class="py-4 px-6 font-black text-slate-900 text-sm whitespace-nowrap">
                                        Q <?php echo number_format($p['monto'], 2); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($p['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if ($esAprobado): ?>
                                            <button onclick="verComprobante(<?php echo $p['pago_id']; ?>)" class="px-4 py-1.5 rounded-full border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold transition-all shadow-sm">
                                                Comprobante
                                            </button>
                                        <?php else: ?>
                                            <a href="pago_servicio.php?tipo=transmetro" class="px-4 py-1.5 rounded-full border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-all shadow-sm inline-block">
                                                Reintentar
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400 text-xs">
                                    No se encontraron pagos con el filtro seleccionado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal Comprobante Oficial de Pago SISC -->
    <div id="modalComprobante" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-200 text-slate-800" id="printArea">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black text-[#1d4289]">SISC</span>
                    <span class="text-[10px] bg-blue-100 text-blue-900 px-2 py-0.5 rounded font-mono font-bold">COMPROBANTE OFICIAL</span>
                </div>
                <button onclick="cerrarComprobante()" class="text-slate-400 hover:text-slate-600 no-print">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="text-center space-y-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Monto Total Pagado</span>
                <h3 class="text-3xl font-black text-slate-900" id="cmpMonto">Q 0.00</h3>
                <span class="inline-flex items-center gap-1 text-xs text-emerald-700 bg-emerald-50 px-3 py-0.5 rounded-full border border-emerald-200 font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i> Transacción Aprobada
                </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs space-y-2 font-mono">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Autorización:</span>
                    <span class="font-bold text-slate-900" id="cmpAuth">AUT-000000</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pasarela:</span>
                    <span class="font-bold text-slate-900" id="cmpPasarela">VISANET</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Fecha y hora:</span>
                    <span class="font-bold text-slate-900" id="cmpFecha">--</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Solicitud:</span>
                    <span class="font-bold text-blue-900" id="cmpSol">--</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Ciudadano CUI:</span>
                    <span class="font-bold text-slate-900" id="cmpCui">--</span>
                </div>
            </div>

            <div class="pt-2 text-center text-[10px] text-slate-400 space-y-1">
                <p>Comprobante válido y certificado ante la SAT y entidades públicas guatemaltecas.</p>
                <p class="font-mono text-[9px]">Sello Digital: 9f82bc4e71a04d6582a981c2f90123ef</p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex gap-2 no-print">
                <button onclick="window.print()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
                <button onclick="cerrarComprobante()" class="flex-1 py-2.5 bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold rounded-xl transition-all">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function verComprobante(pagoId) {
        fetch(`../ajax/pago.php?op=comprobante&id=${pagoId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const p = data.data;
                document.getElementById('cmpMonto').innerText = `Q ${parseFloat(p.monto).toFixed(2)}`;
                document.getElementById('cmpAuth').innerText = p.referencia_pasarela || 'AUT-123456';
                document.getElementById('cmpPasarela').innerText = p.metodo_pago;
                document.getElementById('cmpFecha').innerText = p.fecha_pago;
                document.getElementById('cmpSol').innerText = p.numero_seguimiento || 'N/A (Recarga Directa)';
                document.getElementById('cmpCui').innerText = p.cui || '•••• ••••';
                document.getElementById('modalComprobante').classList.remove('hidden');
            } else {
                alert('No se pudo cargar el comprobante: ' + data.mensaje);
            }
        });
    }

    function cerrarComprobante() {
        document.getElementById('modalComprobante').classList.add('hidden');
    }
    </script>

    <style>
    @media print {
        body * { visibility: hidden; }
        #modalComprobante, #printArea, #printArea * { visibility: visible; }
        #modalComprobante { position: absolute; left: 0; top: 0; width: 100%; background: none; }
        .no-print { display: none !important; }
    }
    </style>

</body>
</html>

