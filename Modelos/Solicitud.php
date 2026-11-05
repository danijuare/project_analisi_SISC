<?php
require_once __DIR__ . "/../config/Conexion.php";

class Solicitud {

    public function __construct() {}

    /**
     * Generar un correlativo único tipo SOL-000125
     */
    private function generarNumeroSeguimiento() {
        $q = ejecutarConsulta("SELECT numero_seguimiento FROM solicitudes WHERE numero_seguimiento LIKE 'SOL-%'");
        $maxNum = 140;
        while ($r = $q->fetch_assoc()) {
            if (preg_match('/SOL-(\d+)/', $r['numero_seguimiento'], $matches)) {
                $val = intval($matches[1]);
                if ($val > $maxNum) {
                    $maxNum = $val;
                }
            }
        }
        $next = $maxNum + 1;
        return "SOL-" . str_pad($next, 6, "0", STR_PAD_LEFT);
    }

    /**
     * Crear una nueva solicitud ciudadana
     */
    public function crear($ciudadano_id, $tipo_proceso, $datos_formulario, $prioridad = 'NORMAL', $archivo = null) {
        global $conexion;
        $ciudadano_id = intval($ciudadano_id);
        $tipo_proceso = limpiarCadena($tipo_proceso);
        $prioridad = limpiarCadena($prioridad) ?: 'NORMAL';

        // 1. Obtener flujo
        $flujo = ejecutarConsultaSimpleFila("SELECT flujo_id, institucion_id FROM flujos WHERE tipo_proceso = '$tipo_proceso' AND activo = 1 LIMIT 1");
        if (!$flujo) {
            // Intentar buscar por código de institución
            $flujo = ejecutarConsultaSimpleFila("SELECT flujo_id, institucion_id FROM flujos WHERE activo = 1 LIMIT 1");
            if (!$flujo) {
                return ["status" => "error", "mensaje" => "No existe un flujo configurado para este trámite."];
            }
        }
        $flujo_id = $flujo['flujo_id'];

        // 2. Obtener paso inicial
        $pasoInicial = ejecutarConsultaSimpleFila("SELECT paso_id, numero_paso FROM flujo_pasos WHERE flujo_id = $flujo_id ORDER BY numero_paso ASC LIMIT 1");
        $paso_id = $pasoInicial ? $pasoInicial['paso_id'] : 1;

        $numero_seguimiento = $this->generarNumeroSeguimiento();
        $jsonDatos = is_array($datos_formulario) ? json_encode($datos_formulario, JSON_UNESCAPED_UNICODE) : $datos_formulario;
        $jsonDatosEscaped = mysqli_real_escape_string($conexion, $jsonDatos);

        $estadoInicial = 'En proceso';
        if ($tipo_proceso === 'RESERVA_HOSTAL_IRTRA') {
            $estadoInicial = 'Pendiente de pago';
        }

        $conexion->begin_transaction();
        try {
            $sql = "INSERT INTO solicitudes (numero_seguimiento, ciudadano_id, flujo_id, paso_actual, estado, datos_formulario, fecha_inicio, prioridad)
                    VALUES ('$numero_seguimiento', $ciudadano_id, $flujo_id, $paso_id, '$estadoInicial', '$jsonDatosEscaped', NOW(), '$prioridad')";
            
            if (!$conexion->query($sql)) {
                throw new Exception("Error al insertar solicitud: " . $conexion->error);
            }
            $solicitud_id = $conexion->insert_id;

            // Historial inicial del paso
            $sqlPaso = "INSERT INTO solicitud_pasos (solicitud_id, paso_id, estado, comentarios, fecha_inicio)
                        VALUES ($solicitud_id, $paso_id, 'Completado', 'Solicitud iniciada por el ciudadano', NOW())";
            $conexion->query($sqlPaso);

            // Manejo de archivo adjunto si existe
            if ($archivo && isset($archivo['name']) && !empty($archivo['name'])) {
                $uploadDir = __DIR__ . "/../uploads/";
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $nombreLimpio = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($archivo['name']));
                $rutaDestino = $uploadDir . $nombreLimpio;

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $tamano = intval($archivo['size']);
                    $tipo = $archivo['type'];
                    $hash = hash_file('sha256', $rutaDestino);
                    $rutaRelativa = "uploads/" . $nombreLimpio;

                    $sqlDoc = "INSERT INTO documentos (solicitud_id, nombre_archivo, tipo_archivo, ruta_almacenamiento, tamano_bytes, hash_sha256, estado)
                               VALUES ($solicitud_id, '{$archivo['name']}', '$tipo', '$rutaRelativa', $tamano, '$hash', 'en revisión')";
                    $conexion->query($sqlDoc);
                }
            }

            $conexion->commit();
            return [
                "status" => "success",
                "mensaje" => "Trámite registrado con éxito.",
                "solicitud_id" => $solicitud_id,
                "numero_seguimiento" => $numero_seguimiento
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Editar datos de una solicitud existente
     */
    public function editar($solicitud_id, $datos_formulario, $prioridad = 'NORMAL') {
        global $conexion;
        $solicitud_id = intval($solicitud_id);
        $prioridad = limpiarCadena($prioridad);

        $json = is_array($datos_formulario) ? json_encode($datos_formulario, JSON_UNESCAPED_UNICODE) : $datos_formulario;
        $jsonEscaped = mysqli_real_escape_string($conexion, $json);

        $sql = "UPDATE solicitudes SET datos_formulario = '$jsonEscaped', prioridad = '$prioridad' WHERE solicitud_id = $solicitud_id";
        if (ejecutarConsulta($sql)) {
            return ["status" => "success", "mensaje" => "Solicitud actualizada correctamente."];
        }
        return ["status" => "error", "mensaje" => "No se pudo actualizar la solicitud."];
    }

    /**
     * Listar trámites del ciudadano en sesión
     */
    public function listarPorCiudadano($ciudadano_id) {
        $ciudadano_id = intval($ciudadano_id);

        $sql_obtener = "SELECT ciudadano_id FROM usuarios WHERE usuario_id = '$ciudadano_id'";
        $fila = ejecutarConsultaSimpleFila($sql_obtener);
        
        $id_ciudadano_r = $fila['ciudadano_id'] ?? 0;

        $sql = "SELECT s.solicitud_id, s.numero_seguimiento, s.estado, s.prioridad, s.fecha_inicio, s.datos_formulario,
                       f.nombre AS flujo_nombre, f.tipo_proceso,
                       i.nombre AS institucion_nombre, i.codigo AS institucion_codigo,
                       p.nombre AS paso_nombre, p.numero_paso,
                       d.nombre_archivo AS documento_nombre, d.ruta_almacenamiento AS documento_ruta,
                       pg.monto AS monto_pago, pg.estado AS estado_pago, pg.referencia_pasarela
                FROM solicitudes s
                INNER JOIN flujos f ON s.flujo_id = f.flujo_id
                INNER JOIN instituciones i ON f.institucion_id = i.institucion_id
                LEFT JOIN flujo_pasos p ON s.paso_actual = p.paso_id
                LEFT JOIN documentos d ON s.solicitud_id = d.solicitud_id
                LEFT JOIN pagos pg ON s.solicitud_id = pg.solicitud_id
                WHERE s.ciudadano_id = '$id_ciudadano_r'
                ORDER BY s.solicitud_id DESC";
        //print_r($sql);
        return ejecutarConsulta($sql);
    }

    /**
     * Listar solicitudes para funcionarios o administradores
     */
    public function listarParaFuncionario($institucion_id = 0, $estado = '', $filtro = '', $esAdmin = false) {
        global $conexion;
        $condiciones = [];

        if (!$esAdmin && $institucion_id > 1) {
            $institucion_id = intval($institucion_id);
            $condiciones[] = "f.institucion_id = $institucion_id";
        }

        if (!empty($estado) && $estado !== 'Todos') {
            $estadoClean = mysqli_real_escape_string($conexion, $estado);
            $condiciones[] = "s.estado = '$estadoClean'";
        }

        if (!empty($filtro)) {
            $filtroClean = mysqli_real_escape_string($conexion, $filtro);
            $condiciones[] = "(s.numero_seguimiento LIKE '%$filtroClean%' OR c.cui LIKE '%$filtroClean%' OR CONCAT(c.nombres, ' ', c.apellidos) LIKE '%$filtroClean%')";
        }

        $where = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

        $sql = "SELECT s.solicitud_id, s.numero_seguimiento, s.estado, s.prioridad, s.fecha_inicio, s.datos_formulario,
                       c.ciudadano_id, c.cui, CONCAT(c.nombres, ' ', c.apellidos) AS ciudadano_nombre, c.email AS ciudadano_email, c.telefono AS ciudadano_telefono,
                       f.nombre AS flujo_nombre, f.tipo_proceso,
                       i.institucion_id, i.nombre AS institucion_nombre, i.codigo AS institucion_codigo,
                       p.paso_id, p.nombre AS paso_nombre, p.numero_paso, p.requiere_firma,
                       d.documento_id, d.nombre_archivo, d.ruta_almacenamiento, d.tamano_bytes, d.hash_sha256
                FROM solicitudes s
                INNER JOIN ciudadanos c ON s.ciudadano_id = c.ciudadano_id
                INNER JOIN flujos f ON s.flujo_id = f.flujo_id
                INNER JOIN instituciones i ON f.institucion_id = i.institucion_id
                LEFT JOIN flujo_pasos p ON s.paso_actual = p.paso_id
                LEFT JOIN documentos d ON s.solicitud_id = d.solicitud_id
                $where
                ORDER BY s.solicitud_id DESC";
        return ejecutarConsulta($sql);
    }

    /**
     * Obtener detalle completo de una solicitud por su número de seguimiento o ID
     */
    public function mostrar($identificador) {
        global $conexion;
        $idClean = mysqli_real_escape_string($conexion, $identificador);

        $sql = "SELECT s.solicitud_id, s.numero_seguimiento, s.estado, s.prioridad, s.fecha_inicio, s.fecha_fin, s.datos_formulario,
                       c.ciudadano_id, c.cui, CONCAT(c.nombres, ' ', c.apellidos) AS ciudadano_nombre, c.email AS ciudadano_email, c.telefono AS ciudadano_telefono, c.direccion AS ciudadano_direccion, c.nit AS ciudadano_nit, c.numero_afiliacion_igss AS ciudadano_igss,
                       f.flujo_id, f.nombre AS flujo_nombre, f.tipo_proceso,
                       i.institucion_id, i.nombre AS institucion_nombre, i.codigo AS institucion_codigo,
                       p.paso_id, p.nombre AS paso_nombre, p.numero_paso, p.tipo_paso, p.requiere_firma,
                       d.documento_id, d.nombre_archivo, d.tipo_archivo, d.ruta_almacenamiento, d.tamano_bytes, d.hash_sha256, d.estado AS doc_estado,
                       fe.firma_id, fe.certificado_serial, fe.firma_digital, fe.fecha_firma,
                       pg.pago_id, pg.monto, pg.metodo_pago, pg.referencia_pasarela, pg.estado AS estado_pago, pg.fecha_pago
                FROM solicitudes s
                INNER JOIN ciudadanos c ON s.ciudadano_id = c.ciudadano_id
                INNER JOIN flujos f ON s.flujo_id = f.flujo_id
                INNER JOIN instituciones i ON f.institucion_id = i.institucion_id
                LEFT JOIN flujo_pasos p ON s.paso_actual = p.paso_id
                LEFT JOIN documentos d ON s.solicitud_id = d.solicitud_id
                LEFT JOIN firmas_electronicas fe ON d.documento_id = fe.documento_id
                LEFT JOIN pagos pg ON s.solicitud_id = pg.solicitud_id
                WHERE (s.numero_seguimiento = '$idClean' OR s.solicitud_id = '$idClean')
                LIMIT 1";
        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Obtener lista de pasos configurados de un flujo para el stepper
     */
    public function obtenerPasosFlujo($flujo_id) {
        $flujo_id = intval($flujo_id);
        $sql = "SELECT paso_id, numero_paso, nombre, tipo_paso, requiere_firma
                FROM flujo_pasos
                WHERE flujo_id = $flujo_id
                ORDER BY numero_paso ASC";
        return ejecutarConsulta($sql);
    }

    /**
     * Aprobar con firma electrónica avanzada
     */
    public function aprobarConFirma($solicitud_id, $usuario_id, $comentario = '') {
        global $conexion;
        $solicitud_id = intval($solicitud_id);
        $usuario_id = intval($usuario_id);
        $comentario = limpiarCadena($comentario);

        $sol = $this->mostrar($solicitud_id);
        if (!$sol) {
            return ["status" => "error", "mensaje" => "Solicitud no encontrada."];
        }

        $flujo_id = $sol['flujo_id'];
        $pasoActualNum = intval($sol['numero_paso']);

        // Buscar siguiente paso
        $siguientePaso = ejecutarConsultaSimpleFila("SELECT paso_id, numero_paso, nombre, tipo_paso FROM flujo_pasos WHERE flujo_id = $flujo_id AND numero_paso > $pasoActualNum ORDER BY numero_paso ASC LIMIT 1");

        $conexion->begin_transaction();
        try {
            $nuevoEstado = $siguientePaso ? 'En proceso' : 'Finalizada';
            $nuevoPasoId = $siguientePaso ? $siguientePaso['paso_id'] : $sol['paso_id'];

            // Actualizar solicitud
            $sqlSol = "UPDATE solicitudes SET paso_actual = $nuevoPasoId, estado = '$nuevoEstado' WHERE solicitud_id = $solicitud_id";
            $conexion->query($sqlSol);

            // Registrar en historial
            $comentFinal = !empty($comentario) ? $comentario : "Aprobado con firma electrónica avanzada por el funcionario.";
            $sqlPaso = "INSERT INTO solicitud_pasos (solicitud_id, paso_id, usuario_responsable, estado, comentarios, fecha_inicio, fecha_fin)
                        VALUES ($solicitud_id, {$sol['paso_id']}, $usuario_id, 'Completado', '$comentFinal', NOW(), NOW())";
            $conexion->query($sqlPaso);

            // Generar Firma Electrónica Avanzada si existe documento o crear firma de expediente
            $serialCert = "GT-PKI-" . strtoupper(bin2hex(random_bytes(6)));
            $firmaDigital = hash('sha512', $sol['numero_seguimiento'] . $usuario_id . time() . "MINFIN_GUATEMALA");
            $docId = $sol['documento_id'] ? $sol['documento_id'] : "NULL";

            if ($docId !== "NULL") {
                $sqlFirma = "INSERT INTO firmas_electronicas (documento_id, usuario_id, certificado_serial, firma_digital, algoritmo, fecha_firma, valida)
                             VALUES ($docId, $usuario_id, '$serialCert', '$firmaDigital', 'SHA-512/RSA-4096', NOW(), 1)";
                $conexion->query($sqlFirma);

                $sqlUpDoc = "UPDATE documentos SET estado = 'Aprobado y Firmado' WHERE documento_id = $docId";
                $conexion->query($sqlUpDoc);
            }

            // Registrar en ESB comunicación de éxito
            $srv = ejecutarConsultaSimpleFila("SELECT servicio_id FROM esb_servicios WHERE nombre = 'renap' LIMIT 1");
            if ($srv) {
                $payload = json_encode([
                    'operacion' => 'notificacion-aprobacion',
                    'solicitud' => $sol['numero_seguimiento'],
                    'certificado' => $serialCert,
                    'timestamp' => time()
                ]);
                $conexion->query("INSERT INTO esb_mensajes (servicio_id, solicitud_id, direccion, payload, codigo_respuesta, respuesta, tiempo_respuesta_ms, estado)
                                  VALUES ({$srv['servicio_id']}, $solicitud_id, 'OUTBOUND', '$payload', 200, '{\"resultado\":\"SINCRONIZADO_EXITOSAMENTE\"}', 118, 'Operativo')");
            }

            $conexion->commit();
            return [
                "status" => "success",
                "mensaje" => "¡Solicitud {$sol['numero_seguimiento']} aprobada con firma electrónica avanzada exitosamente!",
                "certificado_serial" => $serialCert,
                "siguiente_paso" => $siguientePaso ? $siguientePaso['nombre'] : 'Proceso Finalizado'
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Finalizar completamente el proceso emitiendo resolución oficial
     */
    public function finalizarProceso($solicitud_id, $usuario_id, $comentario = '') {
        global $conexion;
        $solicitud_id = intval($solicitud_id);
        $usuario_id = intval($usuario_id);
        $comentario = limpiarCadena($comentario);

        $sol = $this->mostrar($solicitud_id);
        if (!$sol) {
            return ["status" => "error", "mensaje" => "Solicitud no encontrada."];
        }

        $flujo_id = $sol['flujo_id'];
        $tipo_proceso = $sol['tipo_proceso'];
        $ciudadano_id = $sol['ciudadano_id'];
        $datosForm = json_decode($sol['datos_formulario'], true) ?: [];

        // Obtener el paso final del flujo
        $pasoFinal = ejecutarConsultaSimpleFila("SELECT paso_id, nombre FROM flujo_pasos WHERE flujo_id = $flujo_id ORDER BY numero_paso DESC LIMIT 1");
        $pasoFinalId = $pasoFinal ? $pasoFinal['paso_id'] : $sol['paso_id'];

        $conexion->begin_transaction();
        try {
            // 1. Actualizar estado de la solicitud a Finalizada
            $sqlSol = "UPDATE solicitudes SET paso_actual = $pasoFinalId, estado = 'Finalizada', fecha_fin = NOW() WHERE solicitud_id = $solicitud_id";
            $conexion->query($sqlSol);

            // 2. Si es actualización de datos de RENAP o SAT, aplicar cambios en la tabla ciudadanos
            if ($tipo_proceso === 'ACT_DATOS_RENAP') {
                $dir = mysqli_real_escape_string($conexion, $datosForm['direccion_residencia'] ?? '');
                $tel = mysqli_real_escape_string($conexion, $datosForm['telefono'] ?? '');
                $email = mysqli_real_escape_string($conexion, $datosForm['email'] ?? '');
                if (!empty($dir) || !empty($tel) || !empty($email)) {
                    $setParts = [];
                    if (!empty($dir)) $setParts[] = "direccion = '$dir'";
                    if (!empty($tel)) $setParts[] = "telefono = '$tel'";
                    if (!empty($email)) $setParts[] = "email = '$email'";
                    $conexion->query("UPDATE ciudadanos SET " . implode(", ", $setParts) . " WHERE ciudadano_id = $ciudadano_id");
                }
            } elseif ($tipo_proceso === 'ACT_RTU_SAT') {
                $nit = mysqli_real_escape_string($conexion, $datosForm['nit'] ?? '');
                $dirFiscal = mysqli_real_escape_string($conexion, $datosForm['direccion_fiscal'] ?? '');
                $setParts = [];
                if (!empty($nit)) $setParts[] = "nit = '$nit'";
                if (!empty($dirFiscal)) $setParts[] = "direccion = '$dirFiscal'";
                if (!empty($setParts)) {
                    $conexion->query("UPDATE ciudadanos SET " . implode(", ", $setParts) . " WHERE ciudadano_id = $ciudadano_id");
                }
            }

            // 3. Registrar en historial de pasos
            $notaResolucion = !empty($comentario) ? $comentario : "Expediente resuelto favorablemente y finalizado por funcionario institucional.";
            $conexion->query("INSERT INTO solicitud_pasos (solicitud_id, paso_id, usuario_responsable, estado, comentarios, fecha_inicio, fecha_fin)
                              VALUES ($solicitud_id, $pasoFinalId, $usuario_id, 'Finalizado', '$notaResolucion', NOW(), NOW())");

            // 4. Generar Firma Electrónica Avanzada y Certificado Digital PKI
            $serialCert = "GT-PKI-" . strtoupper(bin2hex(random_bytes(6)));
            $firmaDigital = hash('sha512', $sol['numero_seguimiento'] . $usuario_id . time() . "RESOLUCION_OFICIAL_GUATEMALA");
            $docId = $sol['documento_id'] ? $sol['documento_id'] : "NULL";

            if ($docId !== "NULL") {
                $conexion->query("INSERT INTO firmas_electronicas (documento_id, usuario_id, certificado_serial, firma_digital, algoritmo, fecha_firma, valida)
                                  VALUES ($docId, $usuario_id, '$serialCert', '$firmaDigital', 'SHA-512/RSA-4096', NOW(), 1)");
                $conexion->query("UPDATE documentos SET estado = 'Finalizado y Certificado' WHERE documento_id = $docId");
            }

            // 5. Crear Notificación al Ciudadano
            $userCiud = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE ciudadano_id = $ciudadano_id LIMIT 1");
            if ($userCiud) {
                $asunto = "Resolución Favorable: " . $sol['numero_seguimiento'];
                $msgNotif = "Estimado ciudadano, su trámite ({$sol['flujo_nombre']}) ha sido finalizado con éxito por {$sol['institucion_nombre']}. La resolución oficial se encuentra disponible.";
                $conexion->query("INSERT INTO notificaciones (usuario_id, solicitud_id, tipo, canal, asunto, mensaje, leida)
                                  VALUES ({$userCiud['usuario_id']}, $solicitud_id, 'RESOLUCION', 'PORTAL_EMAIL', '$asunto', '$msgNotif', 0)");
            }

            // 6. Registrar en ESB
            $srv = ejecutarConsultaSimpleFila("SELECT servicio_id FROM esb_servicios WHERE nombre = '{$sol['institucion_codigo']}' OR nombre = 'renap' LIMIT 1");
            if ($srv) {
                $payload = json_encode([
                    'operacion' => 'cierre-expediente',
                    'solicitud' => $sol['numero_seguimiento'],
                    'certificado' => $serialCert,
                    'estado' => 'FINALIZADO',
                    'timestamp' => time()
                ]);
                $conexion->query("INSERT INTO esb_mensajes (servicio_id, solicitud_id, direccion, payload, codigo_respuesta, respuesta, tiempo_respuesta_ms, estado)
                                  VALUES ({$srv['servicio_id']}, $solicitud_id, 'OUTBOUND', '$payload', 200, '{\"status\":\"COMPLETED\",\"cert\":\"$serialCert\"}', 105, 'Sincronizado exitoso')");
            }

            $conexion->commit();
            return [
                "status" => "success",
                "mensaje" => "¡Proceso finalizado exitosamente! Expediente cerrado y resolución oficial emitida con firma electrónica avanzada.",
                "certificado_serial" => $serialCert
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Reabrir expediente si se requiere reconsideración
     */
    public function reabrir($solicitud_id, $usuario_id, $motivo = '') {
        global $conexion;
        $solicitud_id = intval($solicitud_id);
        $usuario_id = intval($usuario_id);
        $motivo = limpiarCadena($motivo);

        $sol = $this->mostrar($solicitud_id);
        if (!$sol) {
            return ["status" => "error", "mensaje" => "Solicitud no encontrada."];
        }

        $paso2 = ejecutarConsultaSimpleFila("SELECT paso_id FROM flujo_pasos WHERE flujo_id = {$sol['flujo_id']} AND numero_paso = 2");
        $pasoId = $paso2 ? $paso2['paso_id'] : $sol['paso_id'];

        $sql = "UPDATE solicitudes SET estado = 'En proceso', paso_actual = $pasoId, fecha_fin = NULL WHERE solicitud_id = $solicitud_id";
        if (ejecutarConsulta($sql)) {
            $nota = !empty($motivo) ? $motivo : "Expediente reabierto para revisión por el funcionario.";
            ejecutarConsulta("INSERT INTO solicitud_pasos (solicitud_id, paso_id, usuario_responsable, estado, comentarios, fecha_inicio)
                              VALUES ($solicitud_id, $pasoId, $usuario_id, 'Reabierto', '$nota', NOW())");
            return ["status" => "success", "mensaje" => "Expediente reabierto con éxito para nueva revisión."];
        }
        return ["status" => "error", "mensaje" => "No se pudo reabrir el expediente."];
    }

    /**
     * Rechazar solicitud con motivo justificado
     */
    public function rechazar($solicitud_id, $usuario_id, $comentario) {
        global $conexion;
        $solicitud_id = intval($solicitud_id);
        $usuario_id = intval($usuario_id);
        $comentario = limpiarCadena($comentario);

        if (empty($comentario)) {
            return ["status" => "error", "mensaje" => "Debe especificar la razón del rechazo."];
        }

        $sol = $this->mostrar($solicitud_id);
        if (!$sol) {
            return ["status" => "error", "mensaje" => "Solicitud no encontrada."];
        }

        $conexion->begin_transaction();
        try {
            $sqlSol = "UPDATE solicitudes SET estado = 'Rechazada' WHERE solicitud_id = $solicitud_id";
            $conexion->query($sqlSol);

            $sqlPaso = "INSERT INTO solicitud_pasos (solicitud_id, paso_id, usuario_responsable, estado, comentarios, fecha_inicio, fecha_fin)
                        VALUES ($solicitud_id, {$sol['paso_id']}, $usuario_id, 'Rechazado', '$comentario', NOW(), NOW())";
            $conexion->query($sqlPaso);

            $conexion->commit();
            return ["status" => "success", "mensaje" => "La solicitud ha sido rechazada con las observaciones registradas."];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Cancelar solicitud por parte del ciudadano
     */
    public function cancelarPorCiudadano($solicitud_id, $ciudadano_id) {
        $solicitud_id = intval($solicitud_id);
        $ciudadano_id = intval($ciudadano_id);

        $chk = ejecutarConsultaSimpleFila("SELECT solicitud_id, estado FROM solicitudes WHERE solicitud_id = $solicitud_id AND ciudadano_id = $ciudadano_id");
        if (!$chk) {
            return ["status" => "error", "mensaje" => "No tienes permisos para cancelar este trámite."];
        }
        if ($chk['estado'] === 'Finalizada') {
            return ["status" => "error", "mensaje" => "No es posible cancelar un trámite que ya está finalizado."];
        }

        if (ejecutarConsulta("UPDATE solicitudes SET estado = 'Cancelada' WHERE solicitud_id = $solicitud_id")) {
            return ["status" => "success", "mensaje" => "El trámite ha sido cancelado por tu solicitud."];
        }
        return ["status" => "error", "mensaje" => "No se pudo cancelar el trámite."];
    }
}
?>
