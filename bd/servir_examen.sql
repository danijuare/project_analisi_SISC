CREATE DATABASE proyecto_final_analisis_2;

USE proyecto_final_analisis_2;

-- =========================================================================
-- 1. DOMINIO: GESTIÓN DE USUARIOS E INSTITUCIONES
-- =========================================================================

CREATE TABLE instituciones (
    institucion_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo_integracion VARCHAR(50) NOT NULL,
    endpoint_base VARCHAR(255) NOT NULL,
    protocolo VARCHAR(50) NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    descripcion TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ciudadanos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    ciudadano_id INT NULL,
    institucion_id INT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(50) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(ciudadano_id) ON DELETE SET NULL,
    CONSTRAINT fk_usuarios_institucion FOREIGN KEY (institucion_id) REFERENCES instituciones(institucion_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    rol_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    permisos TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    institucion_id INT NOT NULL,
    asignado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ur_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_rol FOREIGN KEY (rol_id) REFERENCES roles(rol_id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_institucion FOREIGN KEY (institucion_id) REFERENCES instituciones(institucion_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- 2. DOMINIO: MOTOR DE WORKFLOW
-- =========================================================================

DROP TABLE IF EXISTS solicitud_pasos;
DROP TABLE IF EXISTS solicitudes;
DROP TABLE IF EXISTS flujo_transiciones;
DROP TABLE IF EXISTS flujo_pasos;
DROP TABLE IF EXISTS flujos;

CREATE TABLE flujos (
    flujo_id INT AUTO_INCREMENT PRIMARY KEY,
    institucion_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo_proceso VARCHAR(50) NOT NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) DEFAULT 1,
    version INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_flujos_institucion FOREIGN KEY (institucion_id) REFERENCES instituciones(institucion_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flujo_pasos (
    paso_id INT AUTO_INCREMENT PRIMARY KEY,
    flujo_id INT NOT NULL,
    numero_paso INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo_paso VARCHAR(50) NOT NULL,
    rol_responsable INT NOT NULL,
    timeout_horas INT DEFAULT 24,
    requiere_firma TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_pasos_flujo FOREIGN KEY (flujo_id) REFERENCES flujos(flujo_id) ON DELETE CASCADE,
    CONSTRAINT fk_pasos_rol FOREIGN KEY (rol_responsable) REFERENCES roles(rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flujo_transiciones (
    transicion_id INT AUTO_INCREMENT PRIMARY KEY,
    flujo_id INT NOT NULL,
    paso_origen INT NOT NULL,
    paso_destino INT NOT NULL,
    condicion VARCHAR(100) NULL,
    CONSTRAINT fk_trans_flujo FOREIGN KEY (flujo_id) REFERENCES flujos(flujo_id) ON DELETE CASCADE,
    CONSTRAINT fk_trans_origen FOREIGN KEY (paso_origen) REFERENCES flujo_pasos(paso_id),
    CONSTRAINT fk_trans_destino FOREIGN KEY (paso_destino) REFERENCES flujo_pasos(paso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitudes (
    solicitud_id INT AUTO_INCREMENT PRIMARY KEY,
    numero_seguimiento VARCHAR(50) NOT NULL UNIQUE,
    ciudadano_id INT NOT NULL,
    flujo_id INT NOT NULL,
    paso_actual INT NOT NULL,
    estado VARCHAR(50) NOT NULL,
    datos_formulario JSON NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    prioridad VARCHAR(20) DEFAULT 'NORMAL',
    CONSTRAINT fk_sol_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(ciudadano_id),
    CONSTRAINT fk_sol_flujo FOREIGN KEY (flujo_id) REFERENCES flujos(flujo_id),
    CONSTRAINT fk_sol_paso FOREIGN KEY (paso_actual) REFERENCES flujo_pasos(paso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitud_pasos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    paso_id INT NOT NULL,
    usuario_responsable INT NULL,
    estado VARCHAR(50) NOT NULL,
    comentarios TEXT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    CONSTRAINT fk_sp_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(solicitud_id) ON DELETE CASCADE,
    CONSTRAINT fk_sp_paso FOREIGN KEY (paso_id) REFERENCES flujo_pasos(paso_id),
    CONSTRAINT fk_sp_usuario FOREIGN KEY (usuario_responsable) REFERENCES usuarios(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- 3. DOMINIO: DOCUMENTOS Y PAGOS
-- =========================================================================

DROP TABLE IF EXISTS firmas_electronicas;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS documentos;

CREATE TABLE documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_almacenamiento VARCHAR(255) NOT NULL,
    tamano_bytes BIGINT NOT NULL,
    hash_sha256 VARCHAR(64) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(solicitud_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagos (
    pago_id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    ciudadano_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    moneda VARCHAR(10) DEFAULT 'GTQ',
    metodo_pago VARCHAR(50) NOT NULL,
    referencia_pasarela VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalle_respuesta JSON NULL,
    CONSTRAINT fk_pago_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(solicitud_id),
    CONSTRAINT fk_pago_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES ciudadanos(ciudadano_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE firmas_electronicas (
    firma_id INT AUTO_INCREMENT PRIMARY KEY,
    documento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    certificado_serial VARCHAR(100) NOT NULL,
    firma_digital TEXT NOT NULL,
    algoritmo VARCHAR(50) NOT NULL,
    fecha_firma TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valida TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_firma_doc FOREIGN KEY (documento_id) REFERENCES documentos(documento_id) ON DELETE CASCADE,
    CONSTRAINT fk_firma_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- 4. DOMINIO: NOTIFICACIONES Y AUDITORÍA
-- =========================================================================

DROP TABLE IF EXISTS bitacora_auditoria;
DROP TABLE IF EXISTS notificaciones;

CREATE TABLE notificaciones (
    notificacion_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    solicitud_id INT NULL,
    tipo VARCHAR(50) NOT NULL,
    canal VARCHAR(50) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    leida TINYINT(1) DEFAULT 0,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id),
    CONSTRAINT fk_notif_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(solicitud_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bitacora_auditoria (
    auditoria_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(50) NOT NULL,
    entidad VARCHAR(100) NOT NULL,
    entidad_id INT NOT NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip_address VARCHAR(45) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- 5. DOMINIO: INTEGRACIÓN ESB
-- =========================================================================

DROP TABLE IF EXISTS esb_mensajes;
DROP TABLE IF EXISTS esb_servicios;

CREATE TABLE esb_servicios (
    servicio_id INT AUTO_INCREMENT PRIMARY KEY,
    institucion_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    metodo_http VARCHAR(10) NOT NULL,
    protocolo VARCHAR(50) NOT NULL,
    timeout_ms INT DEFAULT 30000,
    reintentos_max INT DEFAULT 3,
    activo TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_esbs_institucion FOREIGN KEY (institucion_id) REFERENCES instituciones(institucion_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE esb_mensajes (
    mensaje_id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_id INT NOT NULL,
    solicitud_id INT NULL,
    direccion VARCHAR(20) NOT NULL,
    payload JSON NOT NULL,
    codigo_respuesta INT NULL,
    respuesta JSON NULL,
    tiempo_respuesta_ms INT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(50) NOT NULL,
    CONSTRAINT fk_esbm_servicio FOREIGN KEY (servicio_id) REFERENCES esb_servicios(servicio_id),
    CONSTRAINT fk_esbm_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(solicitud_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reactivar las restricciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;