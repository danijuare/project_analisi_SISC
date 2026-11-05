<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../Modelos/Solicitud.php";

header('Content-Type: application/json; charset=utf-8');

$solicitud = new Solicitud();
$op = $_GET["op"] ?? '';

switch ($op) {

    case 'guardar':
        if (!isset($_SESSION["usuario_id"])) {
            echo json_encode(["status" => "error", "mensaje" => "Sesión expirada. Inicie sesión nuevamente."]);
            exit();
        }

        $ciudadano_id = $_SESSION["ciudadano_id"] ?? 0;
        if (!$ciudadano_id) {
            echo json_encode(["status" => "error", "mensaje" => "No se encontró el perfil de ciudadano asociado."]);
            exit();
        }

        $tipo_proceso = $_POST["tipo_proceso"] ?? 'ACT_DATOS_RENAP';
        $prioridad = $_POST["prioridad"] ?? 'NORMAL';

        // Construir datos del formulario según el trámite
        $datos = [];
        if ($tipo_proceso === 'ACT_DATOS_RENAP') {
            $datos = [
                'direccion_residencia' => $_POST["direccion"] ?? '',
                'telefono' => $_POST["telefono"] ?? '',
                'email' => $_POST["email"] ?? '',
                'consentimiento' => isset($_POST["consentimiento"]) ? true : false,
                'fecha_registro' => date('d M Y, H:i')
            ];
        } elseif ($tipo_proceso === 'CITA_MEDICA_IGSS') {
            $datos = [
                'afiliacion' => $_POST["afiliacion"] ?? ($_SESSION["igss"] ?? '5521'),
                'unidad_medica' => $_POST["unidad_medica"] ?? 'Consulta externa · Zona 9',
                'fecha_cita' => $_POST["fecha_cita"] ?? date('Y-m-d'),
                'horario' => $_POST["horario"] ?? '09:30',
                'consentimiento' => true
            ];
        } elseif ($tipo_proceso === 'RESERVA_HOSTAL_IRTRA') {
            $datos = [
                'tipo_habitacion' => $_POST["tipo_habitacion"] ?? 'Hostal IRTRA · Hab. doble',
                'fechas' => $_POST["fechas"] ?? '14-16 nov 2026',
                'personas' => intval($_POST["personas"] ?? 2),
                'monto' => floatval($_POST["monto"] ?? 480.00)
            ];
        } elseif ($tipo_proceso === 'RECARGA_TRANSMETRO') {
            $datos = [
                'tarjeta_virtual' => $_POST["tarjeta_virtual"] ?? 'T-' . rand(10000000, 99999999),
                'monto' => floatval($_POST["monto"] ?? 100.00),
                'metodo' => 'VISANET'
            ];
        } elseif ($tipo_proceso === 'ACT_RTU_SAT') {
            $datos = [
                'nit' => $_POST["nit"] ?? '',
                'actividad_economica' => $_POST["actividad_economica"] ?? 'Servicios Profesionales',
                'regimen' => $_POST["regimen"] ?? 'Pequeño Contribuyente (5%)',
                'direccion_fiscal' => $_POST["direccion_fiscal"] ?? '',
                'telefono' => $_POST["telefono"] ?? '',
                'email' => $_POST["email"] ?? '',
                'consentimiento' => true,
                'fecha_registro' => date('d M Y, H:i')
            ];
        } else {
            $datos = $_POST;
        }

        $archivo = $_FILES["documento_respaldo"] ?? null;
        $res = $solicitud->crear($ciudadano_id, $tipo_proceso, $datos, $prioridad, $archivo);
        echo json_encode($res);
        break;

    case 'editar':
        if (!isset($_SESSION["usuario_id"])) {
            echo json_encode(["status" => "error", "mensaje" => "No autenticado."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $prioridad = $_POST["prioridad"] ?? 'NORMAL';

        $datos = [
            'direccion_residencia' => $_POST["direccion"] ?? '',
            'telefono' => $_POST["telefono"] ?? '',
            'email' => $_POST["email"] ?? '',
            'nota_modificacion' => 'Actualizado el ' . date('d M Y H:i')
        ];

        $res = $solicitud->editar($solicitud_id, $datos, $prioridad);
        echo json_encode($res);
        break;

    case 'mostrar':
        $id = $_GET["id"] ?? ($_POST["id"] ?? '');
        $data = $solicitud->mostrar($id);
        if ($data) {
            echo json_encode(["status" => "success", "data" => $data]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Solicitud no encontrada."]);
        }
        break;

    case 'aprobar_firma':
        if (!isset($_SESSION["usuario_id"]) || !in_array($_SESSION["rol_nombre"] ?? '', ['FUNCIONARIO', 'ADMINISTRADOR'])) {
            echo json_encode(["status" => "error", "mensaje" => "Permisos insuficientes para firmar expedientes."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $comentario = $_POST["comentario"] ?? '';
        $res = $solicitud->aprobarConFirma($solicitud_id, $_SESSION["usuario_id"], $comentario);
        echo json_encode($res);
        break;

    case 'finalizar':
        if (!isset($_SESSION["usuario_id"]) || !in_array($_SESSION["rol_nombre"] ?? '', ['FUNCIONARIO', 'ADMINISTRADOR'])) {
            echo json_encode(["status" => "error", "mensaje" => "Permisos insuficientes para resolver expedientes."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $comentario = $_POST["comentario"] ?? '';
        $res = $solicitud->finalizarProceso($solicitud_id, $_SESSION["usuario_id"], $comentario);
        echo json_encode($res);
        break;

    case 'reabrir':
        if (!isset($_SESSION["usuario_id"]) || !in_array($_SESSION["rol_nombre"] ?? '', ['FUNCIONARIO', 'ADMINISTRADOR'])) {
            echo json_encode(["status" => "error", "mensaje" => "Permisos insuficientes."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $motivo = $_POST["motivo"] ?? '';
        $res = $solicitud->reabrir($solicitud_id, $_SESSION["usuario_id"], $motivo);
        echo json_encode($res);
        break;

    case 'rechazar':
        if (!isset($_SESSION["usuario_id"]) || !in_array($_SESSION["rol_nombre"] ?? '', ['FUNCIONARIO', 'ADMINISTRADOR'])) {
            echo json_encode(["status" => "error", "mensaje" => "Permisos insuficientes."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $comentario = $_POST["comentario"] ?? '';
        $res = $solicitud->rechazar($solicitud_id, $_SESSION["usuario_id"], $comentario);
        echo json_encode($res);
        break;

    case 'cancelar':
        if (!isset($_SESSION["ciudadano_id"])) {
            echo json_encode(["status" => "error", "mensaje" => "No autorizado."]);
            exit();
        }
        $solicitud_id = $_POST["solicitud_id"] ?? 0;
        $res = $solicitud->cancelarPorCiudadano($solicitud_id, $_SESSION["ciudadano_id"]);
        echo json_encode($res);
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Operación no soportada."]);
        break;
}
?>

