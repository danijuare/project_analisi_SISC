<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

require_once __DIR__ . "/../Modelos/Esb.php";
$u = usuarioActual();

$esbModel = new Esb();
$adaptadores = $esbModel->listarAdaptadores();
$mensajes = $esbModel->listarMensajes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integraciones ESB | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior Funcionario / Administrador -->
    <?php include "nav_funcionario.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 12) -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">
        
        <div>
            <h1 class="text-3xl font-black text-slate-900">
                Integraciones (ESB)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Bus de Servicios Empresariales centralizado para la interoperabilidad de instituciones públicas guatemaltecas
            </p>
        </div>

        <!-- Sección 1: Adaptadores de Servicios (Fase 3 Pantalla 12) -->
        <div class="space-y-3">
            <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
                Adaptadores
            </h2>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Adaptador</th>
                                <th class="py-3.5 px-6">Nivel</th>
                                <th class="py-3.5 px-6">Operaciones</th>
                                <th class="py-3.5 px-6 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            <?php foreach ($adaptadores as $ad): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-900">
                                        <?php echo htmlspecialchars($ad['adaptador']); ?>
                                        <span class="font-sans font-normal text-slate-400 text-[11px] block">
                                            <?php echo htmlspecialchars($ad['nombre']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-slate-600">
                                        <?php echo htmlspecialchars($ad['nivel']); ?>
                                    </td>
                                    <td class="py-4 px-6 font-sans text-slate-700">
                                        <?php echo htmlspecialchars($ad['operaciones']); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-sans">
                                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full border <?php echo $ad['badge']; ?>">
                                            <?php echo htmlspecialchars($ad['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sección 2: Mensajes con Instituciones (Fase 3 Pantalla 12) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
                    Mensajes con instituciones
                </h2>
                <span class="text-xs font-semibold text-slate-500">
                    Tráfico en tiempo real vía ESB
                </span>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-6">Correlación</th>
                                <th class="py-3.5 px-6">Institución</th>
                                <th class="py-3.5 px-6">Operación</th>
                                <th class="py-3.5 px-6">Solicitud</th>
                                <th class="py-3.5 px-6">Estado</th>
                                <th class="py-3.5 px-6 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($mensajes && $mensajes->num_rows > 0): ?>
                                <?php while ($m = $mensajes->fetch_assoc()): ?>
                                    <?php
                                        $payload = json_decode($m['payload'], true) ?: [];
                                        $operacion = $payload['operacion'] ?? 'actualizar-datos';
                                        $correlacion = substr(md5($m['mensaje_id'] . $m['fecha']), 0, 8);
                                        $solNum = $m['numero_seguimiento'] ?: 'SOL-000123';
                                        $inst = $m['adaptador_nombre'] ?: 'renap';

                                        $esError = strpos($m['estado'], 'Error') !== false;
                                        $badge = 'bg-amber-100 text-amber-800 border-amber-300';
                                        if ($esError) {
                                            $badge = 'bg-rose-100 text-rose-800 border-rose-300';
                                        } elseif (strpos($m['estado'], 'Sincronizado') !== false || $m['estado'] === 'Operativo') {
                                            $badge = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                                        }
                                    ?>
                                    <tr id="fila-msg-<?php echo $m['mensaje_id']; ?>" class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-4 px-6 font-mono font-bold text-slate-800 whitespace-nowrap">
                                            <?php echo htmlspecialchars($correlacion); ?>
                                        </td>
                                        <td class="py-4 px-6 font-mono text-slate-700 whitespace-nowrap">
                                            <?php echo htmlspecialchars($inst); ?>
                                        </td>
                                        <td class="py-4 px-6 font-mono text-slate-700 whitespace-nowrap">
                                            <?php echo htmlspecialchars($operacion); ?>
                                        </td>
                                        <td class="py-4 px-6 font-mono font-bold text-blue-900 whitespace-nowrap">
                                            <?php echo htmlspecialchars($solNum); ?>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <span id="badge-msg-<?php echo $m['mensaje_id']; ?>" class="inline-block text-xs font-semibold px-3 py-1 rounded-full border <?php echo $badge; ?>">
                                                <?php echo htmlspecialchars($m['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <?php if ($esError): ?>
                                                <button id="btn-reintentar-<?php echo $m['mensaje_id']; ?>" onclick="reintentarESB(<?php echo $m['mensaje_id']; ?>)" class="px-4 py-1.5 rounded-full border border-rose-300 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition-all shadow-sm">
                                                    Reintentar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs font-mono">
                                                    <?php echo $m['tiempo_respuesta_ms'] ? $m['tiempo_respuesta_ms'] . 'ms' : 'OK'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                        No hay mensajes registrados en la cola del ESB.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

    <script>
    function reintentarESB(mensajeId) {
        const btn = document.getElementById(`btn-reintentar-${mensajeId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i>`;
        }

        const formData = new FormData();
        formData.append('mensaje_id', mensajeId);

        fetch('../ajax/esb.php?op=reintentar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const badge = document.getElementById(`badge-msg-${mensajeId}`);
                if (badge) {
                    badge.className = 'inline-block text-xs font-semibold px-3 py-1 rounded-full border bg-emerald-100 text-emerald-800 border-emerald-300';
                    badge.innerText = 'Sincronizado exitoso';
                }
                if (btn) {
                    btn.parentNode.innerHTML = `<span class="text-emerald-700 font-bold text-xs"><i class="fa-solid fa-check me-1"></i> 115ms</span>`;
                }
                alert(data.mensaje);
            } else {
                alert('Error al reintentar: ' + data.mensaje);
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Reintentar';
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al comunicar con el Enterprise Service Bus.');
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Reintentar';
            }
        });
    }
    </script>

</body>
</html>

