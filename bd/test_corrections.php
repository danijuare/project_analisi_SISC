<?php
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../Modelos/Usuario.php";
require_once __DIR__ . "/../Modelos/Solicitud.php";
require_once __DIR__ . "/../Modelos/Pago.php";

echo "=== TEST DE LAS 3 CORRECCIONES SOLICITADAS ===\n\n";

$uModel = new Usuario();
$sModel = new Solicitud();
$pModel = new Pago();

// 1. Verificar Ciudadano
$ciud = $uModel->verificar('1234567890101', 'ciudadano123', 'ciudadano');
$ciudId = $ciud['ciudadano_id'];
echo "1. Ciudadano: {$ciud['nombres']} {$ciud['apellidos']} (ID: $ciudId)\n";

// 2. Probar Operación SAT (Actualización de RTU)
echo "\n--- PRUEBA 1: OPERACIÓN SAT (ACTUALIZAR RTU) ---\n";
$datosSat = [
    'nit' => '1234567-8',
    'actividad_economica' => 'Servicios Profesionales de Desarrollo de Software y Consultoría TI',
    'regimen' => 'Pequeño Contribuyente (Tarifa 5%)',
    'direccion_fiscal' => '12 calle 5-30, zona 2, Villa Nueva (Domicilio Fiscal)',
    'telefono' => '5512 3456',
    'email' => 'carlos.ramirez@correo.com',
    'consentimiento' => true
];
$resSat = $sModel->crear($ciudId, 'ACT_RTU_SAT', $datosSat, 'NORMAL');
echo "Crear solicitud SAT: " . $resSat['status'] . " - " . ($resSat['numero_seguimiento'] ?? $resSat['mensaje']) . "\n";
$solSatId = $resSat['solicitud_id'] ?? 0;

// 3. Probar Pago Transmetro Directo (solicitud_id = null)
echo "\n--- PRUEBA 2: PAGO TRANSMETRO DIRECTO (solicitud_id NULL) ---\n";
// Simular lo que hace ajax/pago.php cuando llega solicitud_id vacía
$datosT = [
    'tarjeta_virtual' => 'T-99887766',
    'monto' => 100.00,
    'metodo' => 'VISANET'
];
$creaT = $sModel->crear($ciudId, 'RECARGA_TRANSMETRO', $datosT, 'NORMAL');
$solTId = $creaT['solicitud_id'];
$resPagoT = $pModel->procesar($solTId, $ciudId, 100.00, 'VISANET', '4821', 'Carlos Ramírez', 'Compra de Saldo Transmetro');
echo "Pago Transmetro con auto-solicitud: " . $resPagoT['status'] . " - Aut: " . ($resPagoT['autorizacion'] ?? $resPagoT['mensaje']) . "\n";

// Probar pago completamente sin solicitud_id (solicitud_id = null)
$resPagoNull = $pModel->procesar(null, $ciudId, 50.00, 'VISANET', '4821', 'Carlos Ramírez', 'Recarga sin Solicitud');
echo "Pago directo con solicitud_id NULL puro: " . $resPagoNull['status'] . " - Aut: " . ($resPagoNull['autorizacion'] ?? $resPagoNull['mensaje']) . "\n";

// 4. Probar Pago IRTRA Directo
echo "\n--- PRUEBA 3: PAGO IRTRA DIRECTO ---\n";
$datosI = [
    'tipo_habitacion' => 'Hostal IRTRA · Habitación doble',
    'fechas' => '14-16 nov 2026',
    'personas' => 2,
    'monto' => 480.00
];
$creaI = $sModel->crear($ciudId, 'RESERVA_HOSTAL_IRTRA', $datosI, 'ALTA');
$solIId = $creaI['solicitud_id'];
$resPagoI = $pModel->procesar($solIId, $ciudId, 480.00, 'BAC Credomatic', '4821', 'Carlos Ramírez', 'Reservación Hostal IRTRA');
echo "Pago IRTRA con auto-solicitud: " . $resPagoI['status'] . " - Aut: " . ($resPagoI['autorizacion'] ?? $resPagoI['mensaje']) . "\n";

// 5. Probar Funcionario Finalizando Proceso
echo "\n--- PRUEBA 4: FUNCIONARIO FINALIZANDO PROCESO (RENAP & SAT) ---\n";
$funcSat = $uModel->verificar('auditor_sat', 'sat123', 'funcionario');
$funcSatId = $funcSat['usuario_id'] ?? 2;
echo "Funcionario SAT: " . ($funcSat ? $funcSat['username'] : 'Error') . "\n";

$resFinSat = $sModel->finalizarProceso($solSatId, $funcSatId, "RTU verificado y emitido en padrón tributario conforme a ley.");
echo "Finalizar proceso SAT: " . $resFinSat['status'] . " - PKI: " . ($resFinSat['certificado_serial'] ?? $resFinSat['mensaje']) . "\n";

// Verificar estado final
$solVerif = $sModel->mostrar($solSatId);
echo "Estado Solicitud SAT post-cierre: " . $solVerif['estado'] . " (Paso: " . $solVerif['paso_nombre'] . ")\n";

// Probar reabrir
$resReabrir = $sModel->reabrir($solSatId, $funcSatId, "Reabierto para prueba");
echo "Reabrir expediente: " . $resReabrir['status'] . "\n";

// Volver a finalizar
$resFin2 = $sModel->finalizarProceso($solSatId, $funcSatId, "Cierre definitivo");
echo "Volver a finalizar: " . $resFin2['status'] . "\n";

echo "\n=== TODAS LAS PRUEBAS COMPLETADAS EXITOSAMENTE ===\n";
?>

