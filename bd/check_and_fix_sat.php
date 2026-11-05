<?php
require_once __DIR__ . "/../config/Conexion.php";

echo "=== VERIFICANDO FLUJOS Y PASOS DE SAT Y DEMÁS ENTIDADES ===\n";

// 1. Institución SAT
$sat = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = 'SAT'");
if (!$sat) {
    $satId = ejecutarConsulta_retornarID("INSERT INTO instituciones (nombre, codigo, tipo_integracion, endpoint_base, protocolo, activa, descripcion)
                                          VALUES ('SAT - Superintendencia de Administración Tributaria', 'SAT', 'REST', 'https://api.sat.gob.gt/v1', 'HTTPS', 1, 'Administración tributaria')");
} else {
    $satId = $sat['institucion_id'];
}
echo "SAT ID: $satId\n";

// 2. Flujo SAT
$flujoSat = ejecutarConsultaSimpleFila("SELECT flujo_id FROM flujos WHERE tipo_proceso = 'ACT_RTU_SAT'");
if (!$flujoSat) {
    $flujoSatId = ejecutarConsulta_retornarID("INSERT INTO flujos (institucion_id, nombre, tipo_proceso, descripcion, activo, version)
                                              VALUES ($satId, 'Actualización de RTU y Régimen Tributario', 'ACT_RTU_SAT', 'Trámite de actualización de datos de RTU y régimen tributario en SAT', 1, 1)");
} else {
    $flujoSatId = $flujoSat['flujo_id'];
}
echo "Flujo SAT ID: $flujoSatId\n";

// 3. Pasos para ACT_RTU_SAT
$rolFunc = ejecutarConsultaSimpleFila("SELECT rol_id FROM roles WHERE nombre = 'FUNCIONARIO'");
$rolFuncId = $rolFunc ? $rolFunc['rol_id'] : 2;

$pasosSat = [
    [1, 'Recepción de solicitud RTU', 'INICIO', 0],
    [2, 'Validación y fiscalización SAT', 'REVISION', 1],
    [3, 'Actualización de padrón tributario', 'INTEGRACION', 0],
    [4, 'Emisión de Constancia de RTU', 'FIN', 0]
];

foreach ($pasosSat as $p) {
    $chkP = ejecutarConsultaSimpleFila("SELECT paso_id FROM flujo_pasos WHERE flujo_id = $flujoSatId AND numero_paso = {$p[0]}");
    if (!$chkP) {
        ejecutarConsulta("INSERT INTO flujo_pasos (flujo_id, numero_paso, nombre, tipo_paso, rol_responsable, timeout_horas, requiere_firma)
                          VALUES ($flujoSatId, {$p[0]}, '{$p[1]}', '{$p[2]}', $rolFuncId, 24, {$p[3]})");
        echo "+ Paso creado: {$p[1]}\n";
    } else {
        echo "• Paso ya existe: {$p[1]}\n";
    }
}

// 4. Crear o verificar usuario Funcionario SAT
$chkUserSat = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = 'auditor_sat'");
if (!$chkUserSat) {
    $passHash = password_hash('sat123', PASSWORD_BCRYPT);
    $uSatId = ejecutarConsulta_retornarID("INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                                          VALUES (NULL, $satId, 'auditor_sat', '$passHash', 'funcionario', 1)");
    ejecutarConsulta("INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($uSatId, $rolFuncId, $satId)");
    echo "+ Usuario auditor_sat creado (pass: sat123)\n";
} else {
    // Asegurar que la contraseña sea sat123
    $passHash = password_hash('sat123', PASSWORD_BCRYPT);
    ejecutarConsulta("UPDATE usuarios SET password_hash = '$passHash', institucion_id = $satId, activo = 1 WHERE username = 'auditor_sat'");
    echo "• Usuario auditor_sat actualizado (pass: sat123)\n";
}

// También crear alias 'funcionario_sat' para comodidad
$chkFuncSat = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = 'funcionario_sat'");
if (!$chkFuncSat) {
    $passHash = password_hash('sat123', PASSWORD_BCRYPT);
    $uSatId2 = ejecutarConsulta_retornarID("INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                                           VALUES (NULL, $satId, 'funcionario_sat', '$passHash', 'funcionario', 1)");
    ejecutarConsulta("INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($uSatId2, $rolFuncId, $satId)");
    echo "+ Usuario funcionario_sat creado (pass: sat123)\n";
}

// 5. Crear solicitud demo de SAT para pruebas si no existe
$cCarlos = ejecutarConsultaSimpleFila("SELECT ciudadano_id FROM ciudadanos WHERE cui = '1234567890101'");
if ($cCarlos) {
    $cId = $cCarlos['ciudadano_id'];
    $chkSolSat = ejecutarConsultaSimpleFila("SELECT solicitud_id FROM solicitudes WHERE numero_seguimiento = 'SOL-000130'");
    $paso2Sat = ejecutarConsultaSimpleFila("SELECT paso_id FROM flujo_pasos WHERE flujo_id = $flujoSatId AND numero_paso = 2");
    $pId = $paso2Sat ? $paso2Sat['paso_id'] : 1;
    $datosSatDemo = json_encode([
        'nit' => '1234567-8',
        'actividad_economica' => 'Servicios de Consultoría y Desarrollo de Software',
        'regimen' => 'Pequeño Contribuyente (Tarifa 5%)',
        'direccion_fiscal' => '12 calle 5-30, zona 2, Villa Nueva',
        'telefono' => '5512 3456',
        'email' => 'carlos.ramirez@correo.com',
        'consentimiento' => true
    ], JSON_UNESCAPED_UNICODE);

    if (!$chkSolSat) {
        ejecutarConsulta("INSERT INTO solicitudes (numero_seguimiento, ciudadano_id, flujo_id, paso_actual, estado, datos_formulario, fecha_inicio, prioridad)
                          VALUES ('SOL-000130', $cId, $flujoSatId, $pId, 'En proceso', '$datosSatDemo', '2026-10-25 11:00:00', 'NORMAL')");
        echo "+ Solicitud demo SAT SOL-000130 creada\n";
    }
}

echo "=== SAT CONFIGURADO EXITOSAMENTE ===\n";
?>

