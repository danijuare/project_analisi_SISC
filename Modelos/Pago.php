<?php
require_once __DIR__ . "/../config/Conexion.php";

class Pago {

    public function __construct() {}

    /**
     * Procesar un pago de trámite o servicio en línea
     */
    public function procesar($solicitud_id, $ciudadano_id, $monto, $metodo_pago, $tarjeta_numero, $titular = '', $concepto = '') {
        global $conexion;
        $solicitud_sql = (!empty($solicitud_id) && intval($solicitud_id) > 0) ? intval($solicitud_id) : "NULL";
        $ciudadano_id = intval($ciudadano_id);
        $monto = floatval($monto);
        $metodo_pago = limpiarCadena($metodo_pago) ?: 'VISANET';
        $tarjetaLimpia = preg_replace('/\s+/', '', $tarjeta_numero);
        $ultimos4 = substr($tarjetaLimpia, -4) ?: '4821';
        $titular = limpiarCadena($titular) ?: 'CIUDADANO SISC';

        // Generar número de autorización bancaria
        $numAutorizacion = "AUT-" . rand(100000, 999999);

        // Detalle de la transacción bancaria simulada
        $detalle = [
            'concepto' => $concepto ?: 'Pago de Trámite Ciudadano SISC',
            'titular' => $titular,
            'tarjeta_enmascarada' => '•••• •••• •••• ' . $ultimos4,
            'pasarela' => $metodo_pago,
            'autorizacion' => $numAutorizacion,
            'codigo_comercio' => 'SISC-GT-00192',
            'canal' => 'Portal Ciudadano Web & Móvil'
        ];
        $jsonDetalle = mysqli_real_escape_string($conexion, json_encode($detalle, JSON_UNESCAPED_UNICODE));

        $conexion->begin_transaction();
        try {
            $sqlPago = "INSERT INTO pagos (solicitud_id, ciudadano_id, monto, moneda, metodo_pago, referencia_pasarela, estado, fecha_pago, detalle_respuesta)
                        VALUES ($solicitud_sql, $ciudadano_id, $monto, 'GTQ', '$metodo_pago', '$numAutorizacion', 'Aprobado', NOW(), '$jsonDetalle')";
            if (!$conexion->query($sqlPago)) {
                throw new Exception("Error al guardar el pago: " . $conexion->error);
            }
            $pago_id = $conexion->insert_id;

            // Si hay solicitud vinculada, actualizar su estado
            if ($solicitud_sql !== "NULL") {
                $sol = ejecutarConsultaSimpleFila("SELECT s.solicitud_id, s.numero_seguimiento, f.tipo_proceso FROM solicitudes s INNER JOIN flujos f ON s.flujo_id = f.flujo_id WHERE s.solicitud_id = $solicitud_sql");
                if ($sol) {
                    $nuevoEstado = ($sol['tipo_proceso'] === 'RECARGA_TRANSMETRO') ? 'Finalizada' : 'En proceso';
                    $conexion->query("UPDATE solicitudes SET estado = '$nuevoEstado' WHERE solicitud_id = $solicitud_sql");

                    // Registrar paso de pago completado
                    $conexion->query("INSERT INTO solicitud_pasos (solicitud_id, paso_id, estado, comentarios, fecha_inicio, fecha_fin)
                                      SELECT $solicitud_sql, paso_id, 'Completado', 'Pago procesado exitosamente ($numAutorizacion - Q $monto)', NOW(), NOW()
                                      FROM flujo_pasos WHERE flujo_id = (SELECT flujo_id FROM solicitudes WHERE solicitud_id = $solicitud_sql) AND tipo_paso = 'PAGO' LIMIT 1");
                }
            }

            // Registrar mensaje ESB hacia la pasarela
            $srv = ejecutarConsultaSimpleFila("SELECT servicio_id FROM esb_servicios WHERE nombre = 'pasarela' LIMIT 1");
            if ($srv) {
                $payload = json_encode([
                    'operacion' => 'autorizar-cobro',
                    'pasarela' => $metodo_pago,
                    'monto' => $monto,
                    'autorizacion' => $numAutorizacion,
                    'pago_id' => $pago_id
                ]);
                $solIdEsb = ($solicitud_sql !== "NULL") ? $solicitud_sql : "NULL";
                $conexion->query("INSERT INTO esb_mensajes (servicio_id, solicitud_id, direccion, payload, codigo_respuesta, respuesta, tiempo_respuesta_ms, estado)
                                  VALUES ({$srv['servicio_id']}, $solIdEsb, 'OUTBOUND', '$payload', 200, '{\"status\":\"APPROVED\",\"auth\":\"$numAutorizacion\"}', 189, 'Operativo')");
            }

            $conexion->commit();
            return [
                "status" => "success",
                "mensaje" => "¡Pago por Q " . number_format($monto, 2) . " procesado exitosamente!",
                "pago_id" => $pago_id,
                "autorizacion" => $numAutorizacion,
                "monto" => $monto
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Listar pagos del ciudadano actual
     */
    public function listarPorCiudadano($ciudadano_id, $filtro = 'Todos') {
        global $conexion;
        $ciudadano_id = intval($ciudadano_id);

        $sql_obtener = "SELECT ciudadano_id FROM usuarios WHERE usuario_id = '$ciudadano_id'";
        $fila = ejecutarConsultaSimpleFila($sql_obtener);
        
        $id_ciudadano_r = $fila['ciudadano_id'] ?? 0;
        
        $cond = "p.ciudadano_id = $id_ciudadano_r";

        if ($filtro === 'Aprobados') {
            $cond .= " AND p.estado = 'Aprobado'";
        } elseif ($filtro === 'Rechazados') {
            $cond .= " AND p.estado IN ('Rechazado', 'Anulado')";
        }

        $sql = "SELECT p.pago_id, p.monto, p.moneda, p.metodo_pago, p.referencia_pasarela, p.estado, p.fecha_pago, p.detalle_respuesta,
                       s.numero_seguimiento, f.nombre AS flujo_nombre, i.nombre AS institucion_nombre, i.codigo AS institucion_codigo
                FROM pagos p
                LEFT JOIN solicitudes s ON p.solicitud_id = s.solicitud_id
                LEFT JOIN flujos f ON s.flujo_id = f.flujo_id
                LEFT JOIN instituciones i ON f.institucion_id = i.institucion_id
                WHERE $cond
                ORDER BY p.pago_id DESC";
        return ejecutarConsulta($sql);
    }

    /**
     * Listar pagos recibidos por institución (para Pantalla 10)
     */
    public function listarPorInstitucion($institucion_id = 0, $filtro_estado = '', $filtro_pasarela = '', $esAdmin = false) {
        global $conexion;
        $condiciones = [];

        if (!$esAdmin && $institucion_id > 1) {
            $institucion_id = intval($institucion_id);
            $condiciones[] = "(f.institucion_id = $institucion_id OR f.institucion_id IS NULL)";
        }

        if (!empty($filtro_estado) && $filtro_estado !== 'Todos') {
            $estadoClean = mysqli_real_escape_string($conexion, $filtro_estado);
            $condiciones[] = "p.estado = '$estadoClean'";
        }

        if (!empty($filtro_pasarela) && $filtro_pasarela !== 'Todas') {
            $pasarelaClean = mysqli_real_escape_string($conexion, $filtro_pasarela);
            $condiciones[] = "p.metodo_pago = '$pasarelaClean'";
        }

        $where = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

        $sql = "SELECT p.pago_id, p.monto, p.moneda, p.metodo_pago, p.referencia_pasarela, p.estado, p.fecha_pago, p.detalle_respuesta,
                       s.numero_seguimiento,
                       c.cui, CONCAT(c.nombres, ' ', c.apellidos) AS ciudadano_nombre,
                       f.nombre AS flujo_nombre, i.nombre AS institucion_nombre, i.codigo AS institucion_codigo
                FROM pagos p
                LEFT JOIN solicitudes s ON p.solicitud_id = s.solicitud_id
                LEFT JOIN ciudadanos c ON p.ciudadano_id = c.ciudadano_id
                LEFT JOIN flujos f ON s.flujo_id = f.flujo_id
                LEFT JOIN instituciones i ON f.institucion_id = i.institucion_id
                $where
                ORDER BY p.pago_id DESC";
        return ejecutarConsulta($sql);
    }

    /**
     * Obtener métricas de recaudación para la institución
     */
    public function obtenerMetricas($institucion_id = 0, $esAdmin = false) {
        $whereInst = "";
        if (!$esAdmin && $institucion_id > 1) {
            $institucion_id = intval($institucion_id);
            $whereInst = "AND (f.institucion_id = $institucion_id OR f.institucion_id IS NULL)";
        }

        $aprobados = ejecutarConsultaSimpleFila("SELECT COUNT(*) as cantidad, COALESCE(SUM(p.monto), 0) as total
                                                FROM pagos p
                                                LEFT JOIN solicitudes s ON p.solicitud_id = s.solicitud_id
                                                LEFT JOIN flujos f ON s.flujo_id = f.flujo_id
                                                WHERE p.estado = 'Aprobado' $whereInst");

        $pendientes = ejecutarConsultaSimpleFila("SELECT COUNT(*) as cantidad
                                                 FROM pagos p
                                                 LEFT JOIN solicitudes s ON p.solicitud_id = s.solicitud_id
                                                 LEFT JOIN flujos f ON s.flujo_id = f.flujo_id
                                                 WHERE p.estado IN ('Pendiente', 'Anulado') $whereInst");

        return [
            "cobros_aprobados" => intval($aprobados['cantidad'] ?? 0),
            "total_periodo" => floatval($aprobados['total'] ?? 0),
            "pendientes_conciliar" => intval($pendientes['cantidad'] ?? 1)
        ];
    }

    /**
     * Obtener comprobante detallado
     */
    public function mostrarComprobante($pago_id) {
        $pago_id = intval($pago_id);
        $sql = "SELECT p.*, s.numero_seguimiento,
                       c.cui, CONCAT(c.nombres, ' ', c.apellidos) AS ciudadano_nombre, c.email, c.nit,
                       f.nombre AS flujo_nombre, i.nombre AS institucion_nombre
                FROM pagos p
                LEFT JOIN solicitudes s ON p.solicitud_id = s.solicitud_id
                LEFT JOIN ciudadanos c ON p.ciudadano_id = c.ciudadano_id
                LEFT JOIN flujos f ON s.flujo_id = f.flujo_id
                LEFT JOIN instituciones i ON f.institucion_id = i.institucion_id
                WHERE p.pago_id = $pago_id";
        return ejecutarConsultaSimpleFila($sql);
    }
}
?>

