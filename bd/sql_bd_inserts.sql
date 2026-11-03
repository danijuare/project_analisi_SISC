-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.11-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.20.0.7320
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para proyecto_final_analisis_2
DROP DATABASE IF EXISTS `proyecto_final_analisis_2`;
CREATE DATABASE IF NOT EXISTS `proyecto_final_analisis_2` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `proyecto_final_analisis_2`;

-- Volcando estructura para tabla proyecto_final_analisis_2.bitacora_auditoria
DROP TABLE IF EXISTS `bitacora_auditoria`;
CREATE TABLE IF NOT EXISTS `bitacora_auditoria` (
  `auditoria_id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`auditoria_id`),
  KEY `fk_audit_usuario` (`usuario_id`),
  CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.bitacora_auditoria: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.ciudadanos
DROP TABLE IF EXISTS `ciudadanos`;
CREATE TABLE IF NOT EXISTS `ciudadanos` (
  `ciudadano_id` int(11) NOT NULL AUTO_INCREMENT,
  `cui` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_afiliacion_igss` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ciudadano_id`),
  UNIQUE KEY `cui` (`cui`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.ciudadanos: ~3 rows (aproximadamente)
INSERT INTO `ciudadanos` (`ciudadano_id`, `cui`, `nombres`, `apellidos`, `fecha_nacimiento`, `email`, `telefono`, `direccion`, `nit`, `numero_afiliacion_igss`, `created_at`, `updated_at`) VALUES
	(1, '1234567890101', 'Carlos Roberto', 'Morales Gómez', '1992-05-15', 'carlos.morales@correo.com', '55554444', 'Zona 1, Ciudad de Guatemala', '1234567-8', '987654321', '2026-11-03 15:55:42', '2026-11-03 15:55:42'),
	(2, '9876543210987', 'Ana Lucia', 'García Reyes', '1995-08-20', 'ana.garcia@test.gt', '55551122', 'Zona 10, Guatemala', '888877-6', '11223344', '2026-11-03 16:03:25', '2026-11-03 16:03:25'),
	(3, 'FUNC-179372180650', 'Jorge', 'Méndez', '1990-01-01', 'jorge.mendez@sat.gob.gt', '22003300', 'Institucional', NULL, NULL, '2026-11-03 16:03:26', '2026-11-03 16:03:26');

-- Volcando estructura para tabla proyecto_final_analisis_2.documentos
DROP TABLE IF EXISTS `documentos`;
CREATE TABLE IF NOT EXISTS `documentos` (
  `documento_id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitud_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_archivo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_almacenamiento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano_bytes` bigint(20) NOT NULL,
  `hash_sha256` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`documento_id`),
  KEY `fk_doc_solicitud` (`solicitud_id`),
  CONSTRAINT `fk_doc_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`solicitud_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.documentos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.esb_mensajes
DROP TABLE IF EXISTS `esb_mensajes`;
CREATE TABLE IF NOT EXISTS `esb_mensajes` (
  `mensaje_id` int(11) NOT NULL AUTO_INCREMENT,
  `servicio_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `direccion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `codigo_respuesta` int(11) DEFAULT NULL,
  `respuesta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`respuesta`)),
  `tiempo_respuesta_ms` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`mensaje_id`),
  KEY `fk_esbm_servicio` (`servicio_id`),
  KEY `fk_esbm_solicitud` (`solicitud_id`),
  CONSTRAINT `fk_esbm_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `esb_servicios` (`servicio_id`),
  CONSTRAINT `fk_esbm_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`solicitud_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.esb_mensajes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.esb_servicios
DROP TABLE IF EXISTS `esb_servicios`;
CREATE TABLE IF NOT EXISTS `esb_servicios` (
  `servicio_id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metodo_http` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `protocolo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timeout_ms` int(11) DEFAULT 30000,
  `reintentos_max` int(11) DEFAULT 3,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`servicio_id`),
  KEY `fk_esbs_institucion` (`institucion_id`),
  CONSTRAINT `fk_esbs_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`institucion_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.esb_servicios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.firmas_electronicas
DROP TABLE IF EXISTS `firmas_electronicas`;
CREATE TABLE IF NOT EXISTS `firmas_electronicas` (
  `firma_id` int(11) NOT NULL AUTO_INCREMENT,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `certificado_serial` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firma_digital` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `algoritmo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_firma` timestamp NOT NULL DEFAULT current_timestamp(),
  `valida` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`firma_id`),
  KEY `fk_firma_doc` (`documento_id`),
  KEY `fk_firma_usuario` (`usuario_id`),
  CONSTRAINT `fk_firma_doc` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`documento_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_firma_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.firmas_electronicas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.flujo_pasos
DROP TABLE IF EXISTS `flujo_pasos`;
CREATE TABLE IF NOT EXISTS `flujo_pasos` (
  `paso_id` int(11) NOT NULL AUTO_INCREMENT,
  `flujo_id` int(11) NOT NULL,
  `numero_paso` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_paso` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_responsable` int(11) NOT NULL,
  `timeout_horas` int(11) DEFAULT 24,
  `requiere_firma` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`paso_id`),
  KEY `fk_pasos_flujo` (`flujo_id`),
  KEY `fk_pasos_rol` (`rol_responsable`),
  CONSTRAINT `fk_pasos_flujo` FOREIGN KEY (`flujo_id`) REFERENCES `flujos` (`flujo_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pasos_rol` FOREIGN KEY (`rol_responsable`) REFERENCES `roles` (`rol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.flujo_pasos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.flujo_transiciones
DROP TABLE IF EXISTS `flujo_transiciones`;
CREATE TABLE IF NOT EXISTS `flujo_transiciones` (
  `transicion_id` int(11) NOT NULL AUTO_INCREMENT,
  `flujo_id` int(11) NOT NULL,
  `paso_origen` int(11) NOT NULL,
  `paso_destino` int(11) NOT NULL,
  `condicion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`transicion_id`),
  KEY `fk_trans_flujo` (`flujo_id`),
  KEY `fk_trans_origen` (`paso_origen`),
  KEY `fk_trans_destino` (`paso_destino`),
  CONSTRAINT `fk_trans_destino` FOREIGN KEY (`paso_destino`) REFERENCES `flujo_pasos` (`paso_id`),
  CONSTRAINT `fk_trans_flujo` FOREIGN KEY (`flujo_id`) REFERENCES `flujos` (`flujo_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trans_origen` FOREIGN KEY (`paso_origen`) REFERENCES `flujo_pasos` (`paso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.flujo_transiciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.flujos
DROP TABLE IF EXISTS `flujos`;
CREATE TABLE IF NOT EXISTS `flujos` (
  `flujo_id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_proceso` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `version` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`flujo_id`),
  KEY `fk_flujos_institucion` (`institucion_id`),
  CONSTRAINT `fk_flujos_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`institucion_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.flujos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.instituciones
DROP TABLE IF EXISTS `instituciones`;
CREATE TABLE IF NOT EXISTS `instituciones` (
  `institucion_id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_integracion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endpoint_base` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `protocolo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`institucion_id`),
  UNIQUE KEY `nombre` (`nombre`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.instituciones: ~6 rows (aproximadamente)
INSERT INTO `instituciones` (`institucion_id`, `nombre`, `codigo`, `tipo_integracion`, `endpoint_base`, `protocolo`, `activa`, `descripcion`) VALUES
	(1, 'SISC - Sede Central', 'SISC_CENTRAL', 'REST', 'https://api.sisc.gob.gt/v1', 'HTTPS', 1, 'Plataforma Interinstitucional Central de Servicios Ciudadanos'),
	(2, 'RENAP - Registro Nacional de las Personas', 'RENAP', 'REST/SOAP', 'https://ws.renap.gob.gt/api', 'HTTPS', 1, 'Registro de identidad civil y estado civil de los ciudadanos'),
	(3, 'SAT - Superintendencia de Administración Tributaria', 'SAT', 'REST', 'https://api.sat.gob.gt/v1', 'HTTPS', 1, 'Administración y recaudación tributaria'),
	(4, 'IGSS - Instituto Guatemalteco de Seguridad Social', 'IGSS', 'REST', 'https://servicios.igssgt.org/api', 'HTTPS', 1, 'Seguridad social, afiliación y pensiones'),
	(5, 'IRTRA - Instituto de Recreación de los Trabajadores', 'IRTRA', 'REST', 'https://api.irtra.org.gt/v1', 'HTTPS', 1, 'Parques y recreación para afiliados'),
	(6, 'Transmetro - Municipalidad de Guatemala', 'TRANSMETRO', 'REST', 'https://api.muniguate.com/transmetro', 'HTTPS', 1, 'Sistema integrado de transporte metropolitano');

-- Volcando estructura para tabla proyecto_final_analisis_2.notificaciones
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `notificacion_id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `canal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notificacion_id`),
  KEY `fk_notif_usuario` (`usuario_id`),
  KEY `fk_notif_solicitud` (`solicitud_id`),
  CONSTRAINT `fk_notif_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`solicitud_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.notificaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.pagos
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `pago_id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitud_id` int(11) NOT NULL,
  `ciudadano_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'GTQ',
  `metodo_pago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia_pasarela` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `detalle_respuesta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalle_respuesta`)),
  PRIMARY KEY (`pago_id`),
  KEY `fk_pago_solicitud` (`solicitud_id`),
  KEY `fk_pago_ciudadano` (`ciudadano_id`),
  CONSTRAINT `fk_pago_ciudadano` FOREIGN KEY (`ciudadano_id`) REFERENCES `ciudadanos` (`ciudadano_id`),
  CONSTRAINT `fk_pago_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.pagos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `rol_id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permisos` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`rol_id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.roles: ~3 rows (aproximadamente)
INSERT INTO `roles` (`rol_id`, `nombre`, `descripcion`, `permisos`) VALUES
	(1, 'ADMINISTRADOR', 'Administrador total del sistema SISC, configuración y gestión de usuarios', 'dashboard,usuarios,roles,instituciones,solicitudes_todas,auditoria,reportes'),
	(2, 'FUNCIONARIO', 'Funcionario institucional para revisión, aprobación y firma de trámites', 'dashboard,bandeja_solicitudes,revisar_tramites,validar_documentos,firmar,reportes'),
	(3, 'CIUDADANO', 'Ciudadano guatemalteco autenticado para gestión de trámites personales', 'dashboard,mis_tramites,nueva_solicitud,mis_documentos,mis_pagos,mi_perfil');

-- Volcando estructura para tabla proyecto_final_analisis_2.solicitud_pasos
DROP TABLE IF EXISTS `solicitud_pasos`;
CREATE TABLE IF NOT EXISTS `solicitud_pasos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitud_id` int(11) NOT NULL,
  `paso_id` int(11) NOT NULL,
  `usuario_responsable` int(11) DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comentarios` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sp_solicitud` (`solicitud_id`),
  KEY `fk_sp_paso` (`paso_id`),
  KEY `fk_sp_usuario` (`usuario_responsable`),
  CONSTRAINT `fk_sp_paso` FOREIGN KEY (`paso_id`) REFERENCES `flujo_pasos` (`paso_id`),
  CONSTRAINT `fk_sp_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`solicitud_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sp_usuario` FOREIGN KEY (`usuario_responsable`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.solicitud_pasos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.solicitudes
DROP TABLE IF EXISTS `solicitudes`;
CREATE TABLE IF NOT EXISTS `solicitudes` (
  `solicitud_id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_seguimiento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudadano_id` int(11) NOT NULL,
  `flujo_id` int(11) NOT NULL,
  `paso_actual` int(11) NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_formulario` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_formulario`)),
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `prioridad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'NORMAL',
  PRIMARY KEY (`solicitud_id`),
  UNIQUE KEY `numero_seguimiento` (`numero_seguimiento`),
  KEY `fk_sol_ciudadano` (`ciudadano_id`),
  KEY `fk_sol_flujo` (`flujo_id`),
  KEY `fk_sol_paso` (`paso_actual`),
  CONSTRAINT `fk_sol_ciudadano` FOREIGN KEY (`ciudadano_id`) REFERENCES `ciudadanos` (`ciudadano_id`),
  CONSTRAINT `fk_sol_flujo` FOREIGN KEY (`flujo_id`) REFERENCES `flujos` (`flujo_id`),
  CONSTRAINT `fk_sol_paso` FOREIGN KEY (`paso_actual`) REFERENCES `flujo_pasos` (`paso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.solicitudes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla proyecto_final_analisis_2.usuario_roles
DROP TABLE IF EXISTS `usuario_roles`;
CREATE TABLE IF NOT EXISTS `usuario_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `institucion_id` int(11) NOT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ur_usuario` (`usuario_id`),
  KEY `fk_ur_rol` (`rol_id`),
  KEY `fk_ur_institucion` (`institucion_id`),
  CONSTRAINT `fk_ur_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`institucion_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.usuario_roles: ~5 rows (aproximadamente)
INSERT INTO `usuario_roles` (`id`, `usuario_id`, `rol_id`, `institucion_id`, `asignado_en`) VALUES
	(1, 1, 1, 1, '2026-11-03 15:55:42'),
	(2, 2, 2, 2, '2026-11-03 15:55:42'),
	(3, 3, 3, 1, '2026-11-03 15:55:42'),
	(4, 4, 3, 1, '2026-11-03 16:03:25'),
	(5, 5, 2, 3, '2026-11-03 16:03:26');

-- Volcando estructura para tabla proyecto_final_analisis_2.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `usuario_id` int(11) NOT NULL AUTO_INCREMENT,
  `ciudadano_id` int(11) DEFAULT NULL,
  `institucion_id` int(11) DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_usuarios_ciudadano` (`ciudadano_id`),
  KEY `fk_usuarios_institucion` (`institucion_id`),
  CONSTRAINT `fk_usuarios_ciudadano` FOREIGN KEY (`ciudadano_id`) REFERENCES `ciudadanos` (`ciudadano_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuarios_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`institucion_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla proyecto_final_analisis_2.usuarios: ~5 rows (aproximadamente)
INSERT INTO `usuarios` (`usuario_id`, `ciudadano_id`, `institucion_id`, `username`, `password_hash`, `tipo_usuario`, `activo`, `ultimo_acceso`, `created_at`) VALUES
	(1, NULL, 1, 'admin', '$2y$12$GWVy/6zWE9jUDUTYiJD4Vu0lD8qVHilSgysZpvQREhmiGhLppQZvu', 'funcionario', 1, '2026-11-03 16:09:32', '2026-11-03 15:55:42'),
	(2, NULL, 2, 'funcionario_renap', '$2y$12$FPWtfvJwPzGGwuFwRXHGBea.a/c9OD6FoHH55IT3sp1OsZyCOLGtK', 'funcionario', 1, '2026-11-03 16:03:25', '2026-11-03 15:55:42'),
	(3, 1, 1, 'ciudadano_demo', '$2y$12$8juzwCeDNK3tWByDVwtR4.YKcyqghrk/DHnpBVimR0vmeq4nSGosm', 'ciudadano', 1, '2026-11-03 16:11:18', '2026-11-03 15:55:42'),
	(4, 2, 1, 'ana_garcia', '$2y$12$rS0aKOL9iy4wpr3hrW/cV.LALDEeTEcklWP4Ee8lTV2oauMvjF/y.', 'ciudadano', 1, '2026-11-03 16:03:26', '2026-11-03 16:03:25'),
	(5, 3, 3, 'auditor_sat', '$2y$12$fEqlOREiFF6k8vRZEowx5O3wIQYeRFETXJdotbLx/dg76npjRP1Z.', 'funcionario', 1, '2026-11-03 16:03:26', '2026-11-03 16:03:26');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
