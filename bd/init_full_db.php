<?php
require_once __DIR__ . "/../config/Conexion.php";

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Inicializando Tablas y Datos de SISC Fase 3...</h2>";

// Crear tablas si no existen
$ddl = [
    "CREATE TABLE IF NOT EXISTS instituciones (
        institucion_id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        tipo_integracion VARCHAR(50) NOT NULL,
        endpoint_base VARCHAR(255) NOT NULL,
        protocolo VARCHAR(50) NOT NULL,
        activa TINYINT(1) DEFAULT 1,
        descripcion TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS ciudadanos (
        ciudadano_id INT AUTO_INCREMENT PRIMARY KEY,
        cui VARCHAR(20) NOT NULL UNIQUE,
        nombres VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        fecha_nacimiento DATE NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        direccion VARCHAR(255) NOT NULL,
        nit VARCHAR(20) NULL,
        numero_afiliacion_igss VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS usuarios (
        usuario_id INT AUTO_INCREMENT PRIMARY KEY,
        ciudadano_id INT NULL,
        institucion_id INT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        tipo_usuario VARCHAR(50) NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        ultimo_acceso TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS roles (
        rol_id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT NULL,
        permisos TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS usuario_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        rol_id INT NOT NULL,
        institucion_id INT NOT NULL,
        asignado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS flujos (
        flujo_id INT AUTO_INCREMENT PRIMARY KEY,
        institucion_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        tipo_proceso VARCHAR(50) NOT NULL,
        descripcion TEXT NULL,
        activo TINYINT(1) DEFAULT 1,
        version INT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS flujo_pasos (
        paso_id INT AUTO_INCREMENT PRIMARY KEY,
        flujo_id INT NOT NULL,
        numero_paso INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        tipo_paso VARCHAR(50) NOT NULL,
        rol_responsable INT NOT NULL,
        timeout_horas INT DEFAULT 24,
        requiere_firma TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS flujo_transiciones (
        transicion_id INT AUTO_INCREMENT PRIMARY KEY,
        flujo_id INT NOT NULL,
        paso_origen INT NOT NULL,
        paso_destino INT NOT NULL,
        condicion VARCHAR(100) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS solicitudes (
        solicitud_id INT AUTO_INCREMENT PRIMARY KEY,
        numero_seguimiento VARCHAR(50) NOT NULL UNIQUE,
        ciudadano_id INT NOT NULL,
        flujo_id INT NOT NULL,
        paso_actual INT NOT NULL,
        estado VARCHAR(50) NOT NULL,
        datos_formulario JSON NULL,
        fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_fin TIMESTAMP NULL,
        prioridad VARCHAR(20) DEFAULT 'NORMAL'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS solicitud_pasos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        paso_id INT NOT NULL,
        usuario_responsable INT NULL,
        estado VARCHAR(50) NOT NULL,
        comentarios TEXT NULL,
        fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_fin TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS documentos (
        documento_id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        nombre_archivo VARCHAR(255) NOT NULL,
        tipo_archivo VARCHAR(50) NOT NULL,
        ruta_almacenamiento VARCHAR(255) NOT NULL,
        tamano_bytes BIGINT NOT NULL,
        hash_sha256 VARCHAR(64) NOT NULL,
        estado VARCHAR(50) NOT NULL,
        fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS pagos (
        pago_id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NULL,
        ciudadano_id INT NOT NULL,
        monto DECIMAL(10, 2) NOT NULL,
        moneda VARCHAR(10) DEFAULT 'GTQ',
        metodo_pago VARCHAR(50) NOT NULL,
        referencia_pasarela VARCHAR(100) NOT NULL,
        estado VARCHAR(50) NOT NULL,
        fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        detalle_respuesta JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS firmas_electronicas (
        firma_id INT AUTO_INCREMENT PRIMARY KEY,
        documento_id INT NOT NULL,
        usuario_id INT NOT NULL,
        certificado_serial VARCHAR(100) NOT NULL,
        firma_digital TEXT NOT NULL,
        algoritmo VARCHAR(50) NOT NULL,
        fecha_firma TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        valida TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS notificaciones (
        notificacion_id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        solicitud_id INT NULL,
        tipo VARCHAR(50) NOT NULL,
        canal VARCHAR(50) NOT NULL,
        asunto VARCHAR(150) NOT NULL,
        mensaje TEXT NOT NULL,
        leida TINYINT(1) DEFAULT 0,
        fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS bitacora_auditoria (
        auditoria_id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        accion VARCHAR(50) NOT NULL,
        entidad VARCHAR(100) NOT NULL,
        entidad_id INT NOT NULL,
        datos_anteriores JSON NULL,
        datos_nuevos JSON NULL,
        ip_address VARCHAR(45) NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS esb_servicios (
        servicio_id INT AUTO_INCREMENT PRIMARY KEY,
        institucion_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        endpoint VARCHAR(255) NOT NULL,
        metodo_http VARCHAR(10) NOT NULL,
        protocolo VARCHAR(50) NOT NULL,
        timeout_ms INT DEFAULT 30000,
        reintentos_max INT DEFAULT 3,
        activo TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS esb_mensajes (
        mensaje_id INT AUTO_INCREMENT PRIMARY KEY,
        servicio_id INT NOT NULL,
        solicitud_id INT NULL,
        direccion VARCHAR(20) NOT NULL,
        payload JSON NOT NULL,
        codigo_respuesta INT NULL,
        respuesta JSON NULL,
        tiempo_respuesta_ms INT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($ddl as $sql) {
    if (!$conexion->query($sql)) {
        echo "<p style='color:red;'>Error creando tabla: " . $conexion->error . "</p>";
    }
}
echo "<p style='color:green;'>Tablas verificadas / creadas correctamente.</p>";

// Insertar datos de Instituciones si no están
$instituciones = [
    ['SISC - Sede Central', 'SISC_CENTRAL', 'REST', 'https://api.sisc.gob.gt/v1', 'HTTPS', 'Plataforma Interinstitucional'],
    ['RENAP - Registro Nacional de las Personas', 'RENAP', 'REST/SOAP', 'https://ws.renap.gob.gt/api', 'HTTPS', 'Identidad civil y registro'],
    ['SAT - Superintendencia de Administración Tributaria', 'SAT', 'REST', 'https://api.sat.gob.gt/v1', 'HTTPS', 'Administración tributaria'],
    ['IGSS - Instituto Guatemalteco de Seguridad Social', 'IGSS', 'REST', 'https://servicios.igssgt.org/api', 'HTTPS', 'Seguridad social y salud'],
    ['IRTRA - Instituto de Recreación de los Trabajadores', 'IRTRA', 'REST', 'https://api.irtra.org.gt/v1', 'HTTPS', 'Parques y recreación'],
    ['Transmetro - Municipalidad de Guatemala', 'TRANSMETRO', 'REST', 'https://api.muniguate.com/transmetro', 'HTTPS', 'Transporte urbano']
];

foreach ($instituciones as $inst) {
    $chk = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = '{$inst[1]}'");
    if (!$chk) {
        ejecutarConsulta("INSERT INTO instituciones (nombre, codigo, tipo_integracion, endpoint_base, protocolo, activa, descripcion)
                          VALUES ('{$inst[0]}', '{$inst[1]}', '{$inst[2]}', '{$inst[3]}', '{$inst[4]}', 1, '{$inst[5]}')");
    }
}

// Insertar Roles
$roles = [
    ['ADMINISTRADOR', 'Administrador total del sistema SISC', 'dashboard,usuarios,roles,instituciones,solicitudes_todas,auditoria,reportes'],
    ['FUNCIONARIO', 'Funcionario institucional para revisión, aprobación y firma de trámites', 'dashboard,bandeja_solicitudes,revisar_tramites,validar_documentos,firmar,reportes'],
    ['CIUDADANO', 'Ciudadano guatemalteco autenticado para trámites personales', 'dashboard,mis_tramites,nueva_solicitud,mis_documentos,mis_pagos,mi_perfil']
];
foreach ($roles as $r) {
    $chk = ejecutarConsultaSimpleFila("SELECT rol_id FROM roles WHERE nombre = '{$r[0]}'");
    if (!$chk) {
        ejecutarConsulta("INSERT INTO roles (nombre, descripcion, permisos) VALUES ('{$r[0]}', '{$r[1]}', '{$r[2]}')");
    }
}

// Flujos de Procesos Interinstitucionales
$flujosData = [
    ['RENAP', 'Actualización de datos personales', 'ACT_DATOS_RENAP', 'Trámite para cambio de residencia, teléfono o correo en RENAP'],
    ['IGSS', 'Solicitud de Cita Médica', 'CITA_MEDICA_IGSS', 'Agendamiento de citas de consulta externa en el IGSS'],
    ['IRTRA', 'Reservación de Hostal', 'RESERVA_HOSTAL_IRTRA', 'Reserva de habitaciones en los hostales del IRTRA'],
    ['TRANSMETRO', 'Compra de Saldo Ciudadano', 'RECARGA_TRANSMETRO', 'Acreditación de saldo para el sistema Transmetro'],
    ['SAT', 'Actualización de RTU', 'ACT_RTU_SAT', 'Modificación de actividad económica o datos tributarios en SAT']
];

$flujoIds = [];
foreach ($flujosData as $fd) {
    $inst = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = '{$fd[0]}'");
    $instId = $inst ? $inst['institucion_id'] : 1;
    $chk = ejecutarConsultaSimpleFila("SELECT flujo_id FROM flujos WHERE tipo_proceso = '{$fd[2]}'");
    if (!$chk) {
        $id = ejecutarConsulta_retornarID("INSERT INTO flujos (institucion_id, nombre, tipo_proceso, descripcion, activo, version)
                                           VALUES ($instId, '{$fd[1]}', '{$fd[2]}', '{$fd[3]}', 1, 1)");
        $flujoIds[$fd[2]] = $id;
    } else {
        $flujoIds[$fd[2]] = $chk['flujo_id'];
    }
}

// Pasos para cada flujo
$rolFunc = ejecutarConsultaSimpleFila("SELECT rol_id FROM roles WHERE nombre = 'FUNCIONARIO'");
$rolFuncId = $rolFunc ? $rolFunc['rol_id'] : 2;

$pasosData = [
    // Flujo RENAP:
    ['ACT_DATOS_RENAP', 1, 'Recepción de solicitud', 'INICIO', 0],
    ['ACT_DATOS_RENAP', 2, 'Revisión del funcionario', 'REVISION', 1],
    ['ACT_DATOS_RENAP', 3, 'Envío y validación RENAP', 'INTEGRACION', 0],
    ['ACT_DATOS_RENAP', 4, 'Cierre y notificación', 'FIN', 0],

    // Flujo IGSS:
    ['CITA_MEDICA_IGSS', 1, 'Solicitud de cita', 'INICIO', 0],
    ['CITA_MEDICA_IGSS', 2, 'Asignación de cupo médico', 'REVISION', 0],
    ['CITA_MEDICA_IGSS', 3, 'Confirmación y recordatorio', 'FIN', 0],

    // Flujo IRTRA:
    ['RESERVA_HOSTAL_IRTRA', 1, 'Solicitud de reserva', 'INICIO', 0],
    ['RESERVA_HOSTAL_IRTRA', 2, 'Pago de arancel de hospedaje', 'PAGO', 0],
    ['RESERVA_HOSTAL_IRTRA', 3, 'Emisión de voucher y confirmación', 'FIN', 0],

    // Flujo Transmetro:
    ['RECARGA_TRANSMETRO', 1, 'Orden de recarga', 'INICIO', 0],
    ['RECARGA_TRANSMETRO', 2, 'Pago con tarjeta', 'PAGO', 0],
    ['RECARGA_TRANSMETRO', 3, 'Acreditación de saldo a tarjeta virtual', 'FIN', 0]
];

$pasoIds = [];
foreach ($pasosData as $pd) {
    $flujoId = $flujoIds[$pd[0]] ?? null;
    if ($flujoId) {
        $chk = ejecutarConsultaSimpleFila("SELECT paso_id FROM flujo_pasos WHERE flujo_id = $flujoId AND numero_paso = {$pd[1]}");
        if (!$chk) {
            $pid = ejecutarConsulta_retornarID("INSERT INTO flujo_pasos (flujo_id, numero_paso, nombre, tipo_paso, rol_responsable, timeout_horas, requiere_firma)
                                                VALUES ($flujoId, {$pd[1]}, '{$pd[2]}', '{$pd[3]}', $rolFuncId, 24, {$pd[4]})");
            $pasoIds[$pd[0] . '_' . $pd[1]] = $pid;
        } else {
            $pasoIds[$pd[0] . '_' . $pd[1]] = $chk['paso_id'];
        }
    }
}

// Ciudadanos Mockup Fase 3:
// 1. Carlos Ramírez Soto / Carlos Roberto Morales Gómez (CUI 1234567890101 o 2456789010123)
$cuiCarlos = '1234567890101';
$cCarlos = ejecutarConsultaSimpleFila("SELECT ciudadano_id FROM ciudadanos WHERE cui = '$cuiCarlos'");
if (!$cCarlos) {
    $cCarlosId = ejecutarConsulta_retornarID("INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion, nit, numero_afiliacion_igss)
                                             VALUES ('$cuiCarlos', 'Carlos Ramírez', 'Soto', '1992-05-15', 'carlos.ramirez@correo.com', '5512 3456', '12 calle 5-30, zona 2, Villa Nueva', '1234567-8', '5521')");
} else {
    $cCarlosId = $cCarlos['ciudadano_id'];
    // Actualizar nombre y afiliación según Fase 3
    ejecutarConsulta("UPDATE ciudadanos SET nombres = 'Carlos Ramírez', apellidos = 'Soto', email = 'carlos.ramirez@correo.com', telefono = '5512 3456', direccion = '12 calle 5-30, zona 2, Villa Nueva', numero_afiliacion_igss = '5521' WHERE ciudadano_id = $cCarlosId");
}

// 2. María López Cruz (CUI 4417000004417)
$cuiMaria = '4417000004417';
$cMaria = ejecutarConsultaSimpleFila("SELECT ciudadano_id FROM ciudadanos WHERE cui = '$cuiMaria'");
if (!$cMaria) {
    $cMariaId = ejecutarConsulta_retornarID("INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion, nit, numero_afiliacion_igss)
                                            VALUES ('$cuiMaria', 'María', 'López Cruz', '1988-11-20', 'maria.lopez@correo.gt', '4412 8899', 'Zona 7, Guatemala', '441723-9', '4417')");
} else {
    $cMariaId = $cMaria['ciudadano_id'];
}

// 3. Luis Pérez Gómez (CUI 8802000008802)
$cuiLuis = '8802000008802';
$cLuis = ejecutarConsultaSimpleFila("SELECT ciudadano_id FROM ciudadanos WHERE cui = '$cuiLuis'");
if (!$cLuis) {
    $cLuisId = ejecutarConsulta_retornarID("INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion, nit, numero_afiliacion_igss)
                                           VALUES ('$cuiLuis', 'Luis', 'Pérez Gómez', '1985-03-12', 'luis.perez@correo.gt', '5566 7788', 'Zona 11, Guatemala', '880245-1', '8802')");
} else {
    $cLuisId = $cLuis['ciudadano_id'];
}

// Solicitudes Demo de Fase 3:
$solicitudesMock = [
    [
        'numero' => 'SOL-000123',
        'ciudadano_id' => $cCarlosId,
        'flujo_clave' => 'ACT_DATOS_RENAP',
        'paso_num' => 2,
        'estado' => 'En proceso',
        'prioridad' => 'NORMAL',
        'datos' => [
            'direccion_residencia' => '12 calle 5-30, zona 2, Villa Nueva',
            'telefono' => '5512 3456',
            'email' => 'carlos.ramirez@correo.com',
            'consentimiento' => true,
            'fecha_consentimiento' => '26 oct 2026, 09:12'
        ],
        'fecha' => '2026-10-26 09:14:00',
        'doc' => 'recibo-domicilio.pdf'
    ],
    [
        'numero' => 'SOL-000124',
        'ciudadano_id' => $cMariaId,
        'flujo_clave' => 'ACT_DATOS_RENAP',
        'paso_num' => 3,
        'estado' => 'En proceso',
        'prioridad' => 'NORMAL',
        'datos' => [
            'direccion_residencia' => '7ma avenida 14-20, zona 7',
            'telefono' => '4412 8899',
            'email' => 'maria.lopez@correo.gt',
            'consentimiento' => true
        ],
        'fecha' => '2026-10-26 10:30:00',
        'doc' => 'factura_luz_maria.pdf'
    ],
    [
        'numero' => 'SOL-000119',
        'ciudadano_id' => $cLuisId,
        'flujo_clave' => 'ACT_DATOS_RENAP',
        'paso_num' => 4,
        'estado' => 'Finalizada',
        'prioridad' => 'NORMAL',
        'datos' => [
            'direccion_residencia' => 'Colonia Mariscal, zona 11',
            'telefono' => '5566 7788',
            'email' => 'luis.perez@correo.gt',
            'consentimiento' => true
        ],
        'fecha' => '2026-10-22 14:00:00',
        'doc' => 'constancia_residencia_luis.pdf'
    ],
    [
        'numero' => 'SOL-000136',
        'ciudadano_id' => $cCarlosId,
        'flujo_clave' => 'RESERVA_HOSTAL_IRTRA',
        'paso_num' => 2,
        'estado' => 'Pendiente de pago',
        'prioridad' => 'ALTA',
        'datos' => [
            'tipo_habitacion' => 'Hostal IRTRA · Habitación Doble',
            'fechas' => '14-16 nov 2026',
            'personas' => 2,
            'monto' => 480.00
        ],
        'fecha' => '2026-10-26 11:20:00',
        'doc' => null
    ],
    [
        'numero' => 'SOL-000118',
        'ciudadano_id' => $cCarlosId,
        'flujo_clave' => 'RECARGA_TRANSMETRO',
        'paso_num' => 3,
        'estado' => 'Finalizada',
        'prioridad' => 'NORMAL',
        'datos' => [
            'tarjeta_virtual' => 'T-88990011',
            'monto' => 100.00,
            'metodo' => 'VISANET'
        ],
        'fecha' => '2026-10-22 08:45:00',
        'doc' => null
    ],
    [
        'numero' => 'SOL-000140',
        'ciudadano_id' => $cCarlosId,
        'flujo_clave' => 'CITA_MEDICA_IGSS',
        'paso_num' => 2,
        'estado' => 'En proceso',
        'prioridad' => 'NORMAL',
        'datos' => [
            'afiliacion' => '5521',
            'unidad_medica' => 'Consulta externa · Zona 9',
            'fecha_cita' => '2026-11-04',
            'horario' => '09:30',
            'especialidad' => 'Medicina General'
        ],
        'fecha' => '2026-11-01 16:10:00',
        'doc' => null
    ]
];

foreach ($solicitudesMock as $sm) {
    $chkSol = ejecutarConsultaSimpleFila("SELECT solicitud_id FROM solicitudes WHERE numero_seguimiento = '{$sm['numero']}'");
    $flujoId = $flujoIds[$sm['flujo_clave']] ?? 1;
    $pasoId = $pasoIds[$sm['flujo_clave'] . '_' . $sm['paso_num']] ?? 1;
    $jsonDatos = json_encode($sm['datos'], JSON_UNESCAPED_UNICODE);

    if (!$chkSol) {
        $solId = ejecutarConsulta_retornarID("INSERT INTO solicitudes (numero_seguimiento, ciudadano_id, flujo_id, paso_actual, estado, datos_formulario, fecha_inicio, prioridad)
                                             VALUES ('{$sm['numero']}', {$sm['ciudadano_id']}, $flujoId, $pasoId, '{$sm['estado']}', '$jsonDatos', '{$sm['fecha']}', '{$sm['prioridad']}')");
    } else {
        $solId = $chkSol['solicitud_id'];
        ejecutarConsulta("UPDATE solicitudes SET paso_actual = $pasoId, estado = '{$sm['estado']}', datos_formulario = '$jsonDatos' WHERE solicitud_id = $solId");
    }

    // Documento adjunto si aplica
    if ($sm['doc']) {
        $chkDoc = ejecutarConsultaSimpleFila("SELECT documento_id FROM documentos WHERE solicitud_id = $solId");
        if (!$chkDoc) {
            $hash = hash('sha256', $sm['doc'] . $solId);
            ejecutarConsulta("INSERT INTO documentos (solicitud_id, nombre_archivo, tipo_archivo, ruta_almacenamiento, tamano_bytes, hash_sha256, estado)
                              VALUES ($solId, '{$sm['doc']}', 'application/pdf', 'uploads/{$sm['doc']}', 188416, '$hash', 'en revisión')");
        }
    }
}

// Pagos Demo de Fase 3 (Pantalla 07 & 10):
$pagosMock = [
    [
        'sol_num' => 'SOL-000131',
        'ciudadano_id' => $cCarlosId,
        'monto' => 480.00,
        'metodo' => 'VISANET',
        'ref' => 'AUT-482913',
        'estado' => 'Aprobado',
        'fecha' => '2026-10-25 10:15:00',
        'concepto' => 'Reservación IRTRA · Hostal Habitación Doble'
    ],
    [
        'sol_num' => 'SOL-000135',
        'ciudadano_id' => $cMariaId,
        'monto' => 480.00,
        'metodo' => 'VISANET',
        'ref' => '—',
        'estado' => 'Rechazado',
        'fecha' => '2026-10-24 16:40:00',
        'concepto' => 'Reservación IRTRA · Hostal'
    ],
    [
        'sol_num' => 'SOL-000128',
        'ciudadano_id' => $cLuisId,
        'monto' => 360.00,
        'metodo' => 'BAC Credomatic',
        'ref' => 'AUT-660231',
        'estado' => 'Anulado',
        'fecha' => '2026-10-20 12:20:00',
        'concepto' => 'Reservación IRTRA · Cabaña Sencilla'
    ],
    [
        'sol_num' => 'SOL-000120',
        'ciudadano_id' => $cCarlosId,
        'monto' => 360.00,
        'metodo' => 'BAC Credomatic',
        'ref' => 'AUT-771204',
        'estado' => 'Aprobado',
        'fecha' => '2026-10-18 09:10:00',
        'concepto' => 'Reservación IRTRA · SOL-000120'
    ],
    [
        'sol_num' => 'SOL-000118',
        'ciudadano_id' => $cCarlosId,
        'monto' => 100.00,
        'metodo' => 'VISANET',
        'ref' => 'AUT-991823',
        'estado' => 'Aprobado',
        'fecha' => '2026-10-22 08:46:00',
        'concepto' => 'Compra de saldo · Transmetro'
    ],
    [
        'sol_num' => null,
        'ciudadano_id' => $cCarlosId,
        'monto' => 50.00,
        'metodo' => 'VISANET',
        'ref' => 'ERR-DECLINED-51',
        'estado' => 'Rechazado',
        'fecha' => '2026-10-21 19:30:00',
        'concepto' => 'Compra de saldo · Transmetro'
    ]
];

foreach ($pagosMock as $pm) {
    $solId = "NULL";
    if ($pm['sol_num']) {
        $s = ejecutarConsultaSimpleFila("SELECT solicitud_id FROM solicitudes WHERE numero_seguimiento = '{$pm['sol_num']}'");
        if ($s) $solId = $s['solicitud_id'];
    }
    $chkP = ejecutarConsultaSimpleFila("SELECT pago_id FROM pagos WHERE referencia_pasarela = '{$pm['ref']}' AND monto = {$pm['monto']}");
    if (!$chkP) {
        $det = json_encode(['concepto' => $pm['concepto'], 'canal' => 'Web SISC']);
        ejecutarConsulta("INSERT INTO pagos (solicitud_id, ciudadano_id, monto, moneda, metodo_pago, referencia_pasarela, estado, fecha_pago, detalle_respuesta)
                          VALUES ($solId, {$pm['ciudadano_id']}, {$pm['monto']}, 'GTQ', '{$pm['metodo']}', '{$pm['ref']}', '{$pm['estado']}', '{$pm['fecha']}', '$det')");
    }
}

// Adaptadores y Mensajes ESB (Pantalla 12):
$esbServicios = [
    ['renap', 'validar-dpi, actualizar-datos, consultar-estatus', 'Operativo', 'https://esb.sisc.gob.gt/renap', 'POST'],
    ['sat', 'actualizar-rtu, obtener-constancia', 'Release 2', 'https://esb.sisc.gob.gt/sat', 'POST'],
    ['transmetro', 'consultar-saldo, comprar-saldo', 'Operativo', 'https://esb.sisc.gob.gt/transmetro', 'POST'],
    ['pasarela', 'autorizar-cobro, anular-cobro', 'Operativo', 'https://esb.sisc.gob.gt/pasarela', 'POST'],
    ['firma', 'firmar-documento, validar-firma', 'Operativo', 'https://esb.sisc.gob.gt/pki-firma', 'POST'],
    ['igss', 'solicitar-cita, consultar-historial', 'Release 3', 'https://esb.sisc.gob.gt/igss', 'POST'],
    ['irtra', 'reservar-hostal, consultar-disponibilidad', 'Operativo', 'https://esb.sisc.gob.gt/irtra', 'POST']
];

$instRenap = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = 'RENAP'");
$renapId = $instRenap ? $instRenap['institucion_id'] : 2;

$srvIds = [];
foreach ($esbServicios as $es) {
    $chkSrv = ejecutarConsultaSimpleFila("SELECT servicio_id FROM esb_servicios WHERE nombre = '{$es[0]}'");
    if (!$chkSrv) {
        $sid = ejecutarConsulta_retornarID("INSERT INTO esb_servicios (institucion_id, nombre, endpoint, metodo_http, protocolo, activo)
                                            VALUES ($renapId, '{$es[0]}', '{$es[3]}', '{$es[4]}', 'HTTPS', 1)");
        $srvIds[$es[0]] = $sid;
    } else {
        $srvIds[$es[0]] = $chkSrv['servicio_id'];
    }
}

// Mensajes ESB
$chkMsg = ejecutarConsultaSimpleFila("SELECT COUNT(*) as c FROM esb_mensajes");
if (!$chkMsg || $chkMsg['c'] == 0) {
    $sRenap = $srvIds['renap'] ?? 1;
    $s123 = ejecutarConsultaSimpleFila("SELECT solicitud_id FROM solicitudes WHERE numero_seguimiento = 'SOL-000123'");
    $s124 = ejecutarConsultaSimpleFila("SELECT solicitud_id FROM solicitudes WHERE numero_seguimiento = 'SOL-000124'");

    $id123 = $s123 ? $s123['solicitud_id'] : "NULL";
    $id124 = $s124 ? $s124['solicitud_id'] : "NULL";

    ejecutarConsulta("INSERT INTO esb_mensajes (servicio_id, solicitud_id, direccion, payload, codigo_respuesta, respuesta, tiempo_respuesta_ms, estado)
                      VALUES ($sRenap, $id123, 'OUTBOUND', '{\"operacion\":\"actualizar-datos\",\"solicitud\":\"SOL-000123\",\"cui\":\"1234567890101\"}', 202, '{\"status\":\"ACCEPTED\"}', 142, 'En proceso')");

    ejecutarConsulta("INSERT INTO esb_mensajes (servicio_id, solicitud_id, direccion, payload, codigo_respuesta, respuesta, tiempo_respuesta_ms, estado)
                      VALUES ($sRenap, $id124, 'OUTBOUND', '{\"operacion\":\"actualizar-datos\",\"solicitud\":\"SOL-000124\",\"cui\":\"4417000004417\"}', 504, '{\"error\":\"Gateway Timeout\"}', 30005, 'Error · 5 reintentos')");
}

echo "<h3>¡Base de datos SISC sincronizada exitosamente con las pantallas de la Fase 3!</h3>";
?>

