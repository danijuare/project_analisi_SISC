<?php
require_once "validar_sesion.php";
verificarAcceso(['CIUDADANO']);

require_once __DIR__ . "/../Modelos/Solicitud.php";
$u = usuarioActual();

$solicitudNum = $_GET["solicitud"] ?? ($_GET["id"] ?? '');
$tipoParam = $_GET["tipo"] ?? '';

$monto = 480.00;
$concepto = 'Hostal IRTRA · Habitación doble (14-16 nov 2026, 2 personas)';
$institucionNombre = 'IRTRA · HOSTAL';
$solicitudId = null;

if (!empty($solicitudNum)) {
    $solModel = new Solicitud();
    $sol = $solModel->mostrar($solicitudNum);
    if ($sol) {
        $solicitudId = $sol['solicitud_id'];
        $institucionNombre = "{$sol['institucion_codigo']} · " . strtoupper($sol['flujo_nombre']);
        $datosSol = json_decode($sol['datos_formulario'], true) ?: [];
        $concepto = $sol['flujo_nombre'] . " (" . $sol['numero_seguimiento'] . ")";
        if (isset($datosSol['monto']) && floatval($datosSol['monto']) > 0) {
            $monto = floatval($datosSol['monto']);
        }
    }
} elseif ($tipoParam === 'transmetro') {
    $institucionNombre = 'TRANSMETRO · TRANSPORTE URBANO';
    $concepto = 'Compra de Saldo Ciudadano - Tarjeta Virtual Transmetro';
    $monto = 100.00;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Servicio | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#f6f5ee] min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <?php include "nav_ciudadano.php"; ?>

    <!-- Contenido Principal (Mockup Pantalla 05) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">
        
        <!-- Breadcrumb / Migas de Pan -->
        <div class="text-[11px] font-bold tracking-wider text-blue-900 uppercase">
            PAGOS › <?php echo htmlspecialchars($institucionNombre); ?>
        </div>

        <h1 class="text-3xl font-black text-slate-900">
            <?php echo ($tipoParam === 'transmetro') ? 'Comprar saldo Transmetro' : 'Pagar reservación'; ?>
        </h1>

        <form id="formPago" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <input type="hidden" name="solicitud_id" value="<?php echo $solicitudId; ?>">
            <input type="hidden" name="tipo_servicio" value="<?php echo htmlspecialchars($tipoParam); ?>">
            <input type="hidden" name="monto" id="inputMonto" value="<?php echo $monto; ?>">
            <input type="hidden" name="concepto" value="<?php echo htmlspecialchars($concepto); ?>">
            <input type="hidden" name="metodo_pago" id="inputPasarela" value="VISANET">

            <!-- Columna Izquierda: Formulario de Pago y Pasarela -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                
                <!-- Selector de Pasarela -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                        Pagar con tarjeta
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="seleccionarPasarela('VISANET', this)" class="btn-pasarela py-3 px-4 rounded-2xl border-2 border-blue-900 bg-blue-50 text-blue-950 text-xs font-bold transition-all flex items-center justify-center gap-2">
                            <i class="fa-brands fa-cc-visa text-base"></i> VISANET
                        </button>
                        <button type="button" onclick="seleccionarPasarela('BAC Credomatic', this)" class="btn-pasarela py-3 px-4 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-credit-card text-base"></i> BAC Credomatic
                        </button>
                    </div>
                </div>

                <!-- Tarjeta Guardada -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 uppercase">Tarjeta guardada</span>
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Preferida</span>
                    </div>

                    <div class="flex items-center justify-between bg-white p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-3">
                            <i class="fa-brands fa-cc-visa text-2xl text-blue-700"></i>
                            <div>
                                <p class="text-xs font-mono font-bold text-slate-800">•••• •••• •••• 4821</p>
                                <span class="text-[11px] text-slate-400">Vence 09/28 · Carlos Ramírez</span>
                            </div>
                        </div>
                        <label class="cursor-pointer">
                            <input type="radio" name="tarjeta_opcion" value="guardada" checked onchange="toggleNuevaTarjeta(false)" class="w-4 h-4 text-blue-900 focus:ring-blue-700">
                        </label>
                    </div>

                    <div class="text-right">
                        <button type="button" onclick="toggleNuevaTarjeta(true)" class="text-xs font-bold text-blue-900 hover:underline">
                            Usar otra tarjeta
                        </button>
                    </div>
                </div>

                <!-- Sección Otra Tarjeta (Desplegable) -->
                <div id="seccionNuevaTarjeta" class="space-y-4 hidden p-5 bg-slate-50 rounded-2xl border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Número de Tarjeta</label>
                        <input type="text" name="tarjeta" id="inputTarjeta" placeholder="4000 1234 5678 9010" maxlength="19"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-sm font-mono font-semibold">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vencimiento</label>
                            <input type="text" placeholder="MM/AA" maxlength="5"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CVV</label>
                            <input type="password" placeholder="123" maxlength="4"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-sm font-mono">
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 bg-slate-100 p-3.5 rounded-xl border border-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600 text-sm"></i>
                    <span>Los datos de tu tarjeta los procesa la pasarela segura. El SISC solo guarda un token y tu comprobante.</span>
                </div>

            </div>

            <!-- Columna Derecha: Resumen de Compra y Botón de Pago -->
            <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                <h3 class="font-extrabold text-slate-900 text-base">
                    Resumen
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Concepto</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5"><?php echo htmlspecialchars($concepto); ?></p>
                    </div>

                    <?php if ($solicitudNum): ?>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">No. Solicitud</span>
                        <p class="font-mono font-bold text-blue-900 mt-0.5"><?php echo htmlspecialchars($solicitudNum); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-base">
                        <span class="font-bold text-slate-600">Total a pagar</span>
                        <span class="font-black text-[#1d4289] text-xl">Q <?php echo number_format($monto, 2); ?></span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Al confirmarse el cobro, tu solicitud se envía automáticamente a <?php echo htmlspecialchars($institucionNombre); ?>. Recibirás el comprobante en Pagos y por correo.
                </p>

                <div class="pt-2 space-y-2">
                    <button type="submit" id="btnProcesarPago" class="w-full py-3.5 bg-[#1d4289] hover:bg-blue-950 text-white text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                        <span>Pagar Q <?php echo number_format($monto, 2); ?></span>
                        <i class="fa-solid fa-lock text-[10px]"></i>
                    </button>
                    <a href="inicio_ciudadano.php" class="block text-center w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition-all">
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
    function seleccionarPasarela(pasarela, el) {
        document.getElementById('inputPasarela').value = pasarela;
        document.querySelectorAll('.btn-pasarela').forEach(b => {
            b.className = 'btn-pasarela py-3 px-4 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all flex items-center justify-center gap-2';
        });
        el.className = 'btn-pasarela py-3 px-4 rounded-2xl border-2 border-blue-900 bg-blue-50 text-blue-950 text-xs font-bold transition-all flex items-center justify-center gap-2';
    }

    function toggleNuevaTarjeta(mostrar) {
        const sec = document.getElementById('seccionNuevaTarjeta');
        if (mostrar) {
            sec.classList.remove('hidden');
            document.querySelector('input[name="tarjeta_opcion"][value="guardada"]').checked = false;
        } else {
            sec.classList.add('hidden');
        }
    }

    document.getElementById('formPago').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnProcesarPago');
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Conectando con pasarela segura...`;

        const formData = new FormData(this);

        fetch('../ajax/pago.php?op=procesar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`¡Pago Autorizado con Éxito!\nNo. Autorización: ${data.autorizacion}\nMonto: Q ${data.monto.toFixed(2)}`);
                window.location.href = 'historial_pagos.php';
            } else {
                alert('Error al procesar el pago: ' + data.mensaje);
                btn.disabled = false;
                btn.innerHTML = `<span>Pagar Q ${parseFloat(document.getElementById('inputMonto').value).toFixed(2)}</span> <i class="fa-solid fa-lock text-[10px]"></i>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con la pasarela bancaria.');
            btn.disabled = false;
            btn.innerHTML = `<span>Reintentar Pago</span>`;
        });
    });
    </script>

</body>
</html>

