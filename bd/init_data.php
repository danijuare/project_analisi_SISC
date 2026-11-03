<?php
require_once __DIR__ . "/../config/Conexion.php";

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Inicializador de Datos SISC (Proyecto Final Análisis II)</h2>";

// 1. Instituciones
$instituciones = [
    [
        'nombre' => 'SISC - Sede Central',
        'codigo' => 'SISC_CENTRAL',
        'tipo_integracion' => 'REST',
        'endpoint_base' => 'https://api.sisc.gob.gt/v1',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Plataforma Interinstitucional Central de Servicios Ciudadanos'
    ],
    [
        'nombre' => 'RENAP - Registro Nacional de las Personas',
        'codigo' => 'RENAP',
        'tipo_integracion' => 'REST/SOAP',
        'endpoint_base' => 'https://ws.renap.gob.gt/api',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Registro de identidad civil y estado civil de los ciudadanos'
    ],
    [
        'nombre' => 'SAT - Superintendencia de Administración Tributaria',
        'codigo' => 'SAT',
        'tipo_integracion' => 'REST',
        'endpoint_base' => 'https://api.sat.gob.gt/v1',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Administración y recaudación tributaria'
    ],
    [
        'nombre' => 'IGSS - Instituto Guatemalteco de Seguridad Social',
        'codigo' => 'IGSS',
        'tipo_integracion' => 'REST',
        'endpoint_base' => 'https://servicios.igssgt.org/api',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Seguridad social, afiliación y pensiones'
    ],
    [
        'nombre' => 'IRTRA - Instituto de Recreación de los Trabajadores',
        'codigo' => 'IRTRA',
        'tipo_integracion' => 'REST',
        'endpoint_base' => 'https://api.irtra.org.gt/v1',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Parques y recreación para afiliados'
    ],
    [
        'nombre' => 'Transmetro - Municipalidad de Guatemala',
        'codigo' => 'TRANSMETRO',
        'tipo_integracion' => 'REST',
        'endpoint_base' => 'https://api.muniguate.com/transmetro',
        'protocolo' => 'HTTPS',
        'descripcion' => 'Sistema integrado de transporte metropolitano'
    ]
];

$inst_ids = [];
foreach ($instituciones as $inst) {
    $nombre = $inst['nombre'];
    $codigo = $inst['codigo'];
    $tipo = $inst['tipo_integracion'];
    $endpoint = $inst['endpoint_base'];
    $protocolo = $inst['protocolo'];
    $desc = $inst['descripcion'];

    $check = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = '$codigo'");
    if (!$check) {
        $sql = "INSERT INTO instituciones (nombre, codigo, tipo_integracion, endpoint_base, protocolo, activa, descripcion)
                VALUES ('$nombre', '$codigo', '$tipo', '$endpoint', '$protocolo', 1, '$desc')";
        $id = ejecutarConsulta_retornarID($sql);
        echo "<p style='color:green;'>+ Institución creada: <strong>$nombre</strong> (ID: $id)</p>";
        $inst_ids[$codigo] = $id;
    } else {
        $inst_ids[$codigo] = $check['institucion_id'];
        echo "<p style='color:gray;'>• Institución ya existía: <strong>$nombre</strong> (ID: {$check['institucion_id']})</p>";
    }
}

// 2. Roles
$roles = [
    [
        'nombre' => 'ADMINISTRADOR',
        'descripcion' => 'Administrador total del sistema SISC, configuración y gestión de usuarios',
        'permisos' => 'dashboard,usuarios,roles,instituciones,solicitudes_todas,auditoria,reportes'
    ],
    [
        'nombre' => 'FUNCIONARIO',
        'descripcion' => 'Funcionario institucional para revisión, aprobación y firma de trámites',
        'permisos' => 'dashboard,bandeja_solicitudes,revisar_tramites,validar_documentos,firmar,reportes'
    ],
    [
        'nombre' => 'CIUDADANO',
        'descripcion' => 'Ciudadano guatemalteco autenticado para gestión de trámites personales',
        'permisos' => 'dashboard,mis_tramites,nueva_solicitud,mis_documentos,mis_pagos,mi_perfil'
    ]
];

$role_ids = [];
foreach ($roles as $rol) {
    $nombre = $rol['nombre'];
    $desc = $rol['descripcion'];
    $perm = $rol['permisos'];

    $check = ejecutarConsultaSimpleFila("SELECT rol_id FROM roles WHERE nombre = '$nombre'");
    if (!$check) {
        $sql = "INSERT INTO roles (nombre, descripcion, permisos) VALUES ('$nombre', '$desc', '$perm')";
        $id = ejecutarConsulta_retornarID($sql);
        echo "<p style='color:green;'>+ Rol creado: <strong>$nombre</strong> (ID: $id)</p>";
        $role_ids[$nombre] = $id;
    } else {
        $role_ids[$nombre] = $check['rol_id'];
        echo "<p style='color:gray;'>• Rol ya existía: <strong>$nombre</strong> (ID: {$check['rol_id']})</p>";
    }
}

// 3. Usuario Administrador
$admin_check = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = 'admin'");
if (!$admin_check) {
    $sisc_id = $inst_ids['SISC_CENTRAL'];
    $rol_admin_id = $role_ids['ADMINISTRADOR'];
    $pass_hash = password_hash('admin123', PASSWORD_BCRYPT);

    $sql_admin = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                  VALUES (NULL, $sisc_id, 'admin', '$pass_hash', 'funcionario', 1)";
    $admin_id = ejecutarConsulta_retornarID($sql_admin);

    ejecutarConsulta("INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($admin_id, $rol_admin_id, $sisc_id)");
    echo "<p style='color:blue;'>✔ Usuario Administrador creado: <strong>admin</strong> / contraseña: <strong>admin123</strong> (Rol: ADMINISTRADOR)</p>";
} else {
    echo "<p style='color:gray;'>• Usuario admin ya existía.</p>";
}

// 4. Usuario Funcionario RENAP
$func_check = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = 'funcionario_renap'");
if (!$func_check) {
    $renap_id = $inst_ids['RENAP'];
    $rol_func_id = $role_ids['FUNCIONARIO'];
    $pass_hash = password_hash('renap123', PASSWORD_BCRYPT);

    $sql_func = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                 VALUES (NULL, $renap_id, 'funcionario_renap', '$pass_hash', 'funcionario', 1)";
    $func_id = ejecutarConsulta_retornarID($sql_func);

    ejecutarConsulta("INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($func_id, $rol_func_id, $renap_id)");
    echo "<p style='color:blue;'>✔ Usuario Funcionario creado: <strong>funcionario_renap</strong> / contraseña: <strong>renap123</strong> (Institución: RENAP)</p>";
} else {
    echo "<p style='color:gray;'>• Usuario funcionario_renap ya existía.</p>";
}

// 5. Usuario Ciudadano Demo
$cui_demo = '1234567890101';
$ciudadano_check = ejecutarConsultaSimpleFila("SELECT ciudadano_id FROM ciudadanos WHERE cui = '$cui_demo'");
$ciudadano_id = null;
if (!$ciudadano_check) {
    $sql_ciudadano = "INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion, nit, numero_afiliacion_igss)
                      VALUES ('$cui_demo', 'Carlos Roberto', 'Morales Gómez', '1992-05-15', 'carlos.morales@correo.com', '55554444', 'Zona 1, Ciudad de Guatemala', '1234567-8', '987654321')";
    $ciudadano_id = ejecutarConsulta_retornarID($sql_ciudadano);
    echo "<p style='color:green;'>+ Ciudadano creado: <strong>Carlos Roberto Morales Gómez</strong> (CUI: $cui_demo)</p>";
} else {
    $ciudadano_id = $ciudadano_check['ciudadano_id'];
    echo "<p style='color:gray;'>• Ciudadano CUI $cui_demo ya existía.</p>";
}

$user_ciud_check = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = 'ciudadano_demo'");
if (!$user_ciud_check && $ciudadano_id) {
    $sisc_id = $inst_ids['SISC_CENTRAL'];
    $rol_ciud_id = $role_ids['CIUDADANO'];
    $pass_hash = password_hash('ciudadano123', PASSWORD_BCRYPT);

    $sql_u_ciud = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                   VALUES ($ciudadano_id, $sisc_id, 'ciudadano_demo', '$pass_hash', 'ciudadano', 1)";
    $u_ciud_id = ejecutarConsulta_retornarID($sql_u_ciud);

    ejecutarConsulta("INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($u_ciud_id, $rol_ciud_id, $sisc_id)");
    echo "<p style='color:blue;'>✔ Usuario Ciudadano creado: <strong>ciudadano_demo</strong> (o CUI: <strong>1234567890101</strong>) / contraseña: <strong>ciudadano123</strong> (Rol: CIUDADANO)</p>";
} else {
    echo "<p style='color:gray;'>• Usuario ciudadano_demo ya existía.</p>";
}

echo "<h3>¡Inicialización completada con éxito!</h3>";
echo "<p><a href='../vistas/login.php'>Ir al Login</a></p>";
?>

