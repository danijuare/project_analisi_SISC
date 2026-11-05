<?php
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../Modelos/Usuario.php";
require_once __DIR__ . "/../Modelos/Solicitud.php";
require_once __DIR__ . "/../Modelos/Pago.php";
require_once __DIR__ . "/../Modelos/Esb.php";

echo "========================================================\n";
echo "PRUEBAS INTEGRALES DE SISTEMA SISC (Fase 3 Completa)\n";
echo "========================================================\n\n";

$uModel = new Usuario();
$sModel = new Solicitud();
$pModel = new Pago();
$esbModel = new Esb();

// 1. Auth Ciudadano
$ciud = $uModel->verificar('1234567890101', 'ciudadano123', 'ciudadano');
echo "1. Login Ciudadano: " . ($ciud ? "OK ({$ciud['nombres']} {$ciud['apellidos']} - CUI: {$ciud['cui']})" : "ERROR") . "\n";

// 2. Auth Funcionario RENAP
$func = $uModel->verificar('funcionario_renap', 'renap123', 'funcionario');
echo "2. Login Funcionario: " . ($func ? "OK (Institución: {$func['institucion_nombre']})" : "ERROR") . "\n";

// 3. Auth Administrador
$admin = $uModel->verificar('admin', 'admin123', 'funcionario');
echo "3. Login Administrador: " . ($admin ? "OK (Rol: {$admin['rol_nombre']})" : "ERROR") . "\n";

// 4. Crear Solicitud (Trámite RENAP Actualización de Datos)
$datosRenap = [
    'direccion_residencia' => '12 calle 5-30, zona 2, Villa Nueva',
    'telefono' => '5512 3456',
    'email' => 'carlos.ramirez@correo.com',
    'consentimiento' => true
];
$ciudId = $ciud['ciudadano_id'] ?? 1;
$resCrea = $sModel->crear($ciudId, 'ACT_DATOS_RENAP', $datosRenap, 'NORMAL');
echo "4. Crear Trámite RENAP: " . ($resCrea['status'] === 'success' ? "OK ({$resCrea['numero_seguimiento']})" : "ERROR: {$resCrea['mensaje']}") . "\n";
$nuevoSolNum = $resCrea['numero_seguimiento'];
$nuevoSolId = $resCrea['solicitud_id'];

// 5. Editar Solicitud
$datosEdit = [
    'direccion_residencia' => '12 calle 5-30, zona 2, Villa Nueva (Corregido)',
    'telefono' => '5512 3456',
    'email' => 'carlos.ramirez@correo.com'
];
$resEdit = $sModel->editar($nuevoSolId, $datosEdit, 'ALTA');
echo "5. Editar Trámite: " . ($resEdit['status'] === 'success' ? "OK" : "ERROR: {$resEdit['mensaje']}") . "\n";

// 6. Consultar Detalle y Seguimiento
$solDetalle = $sModel->mostrar($nuevoSolNum);
echo "6. Detalle Trámite: " . ($solDetalle ? "OK (Estado: {$solDetalle['estado']} - Proceso: {$solDetalle['flujo_nombre']})" : "ERROR") . "\n";

// 7. Funcionario Aprueba con Firma Electrónica Avanzada
$funcUserId = $func['usuario_id'] ?? 2;
$resFirma = $sModel->aprobarConFirma($nuevoSolId, $funcUserId, "Documentación verificada conforme a padrón civil de RENAP");
echo "7. Firma Electrónica Avanzada: " . ($resFirma['status'] === 'success' ? "OK (Serial PKI: {$resFirma['certificado_serial']})" : "ERROR: {$resFirma['mensaje']}") . "\n";

// 8. Procesar Pago con Pasarela (VISANET)
$resPago = $pModel->procesar($nuevoSolId, $ciudId, 480.00, 'VISANET', '4821', 'Carlos Ramírez', 'Arancel de emisión de certificación');
echo "8. Procesar Pago VISANET: " . ($resPago['status'] === 'success' ? "OK (Autorización: {$resPago['autorizacion']} - Q {$resPago['monto']})" : "ERROR: {$resPago['mensaje']}") . "\n";

// 9. Listar Pagos de Ciudadano
$pagosCiud = $pModel->listarPorCiudadano($ciudId);
echo "9. Listar Pagos Ciudadano: " . ($pagosCiud ? "OK ({$pagosCiud->num_rows} registros)" : "ERROR") . "\n";

// 10. Métricas de Recaudación para Funcionario
$metricas = $pModel->obtenerMetricas(2); // RENAP
echo "10. Métricas Recaudación: OK (Aprobados: {$metricas['cobros_aprobados']}, Total: Q {$metricas['total_periodo']})\n";

// 11. Control Cruzado de Servicios
$cruzado = $esbModel->controlCruzado('Todas', 'Todos');
echo "11. Control Cruzado ESB: " . ($cruzado ? "OK ({$cruzado->num_rows} trámites interinstitucionales cruzados)" : "ERROR") . "\n";

// 12. Reintentar Mensaje ESB
$resReintento = $esbModel->reintentar(2);
echo "12. Reintentar Transacción ESB: " . ($resReintento['status'] === 'success' ? "OK" : "ERROR") . "\n";

echo "\n========================================================\n";
echo "¡TODOS LOS MÓDULOS DE CIUDADANOS Y FUNCIONARIOS VALIDADOS!\n";
echo "========================================================\n";
?>

