<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../Modelos/Pago.php";

$pago = new Pago();
$op = $_GET["op"] ?? '';

switch ($op) {

    case 'procesar':
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION["usuario_id"])) {
            echo json_encode(["status" => "error", "mensaje" => "Sesión expirada. Inicie sesión nuevamente."]);
            exit();
        }

        $ciudadano_id = $_SESSION["ciudadano_id"] ?? 0;
        if (!$ciudadano_id) {
            echo json_encode(["status" => "error", "mensaje" => "Debe iniciar sesión como ciudadano para realizar un pago."]);
            exit();
        }

        $solicitud_id = !empty($_POST["solicitud_id"]) ? intval($_POST["solicitud_id"]) : null;
        $monto = floatval($_POST["monto"] ?? 0);
        $metodo_pago = $_POST["metodo_pago"] ?? 'VISANET';
        $tarjeta = $_POST["tarjeta"] ?? '4821';
        $titular = $_POST["titular"] ?? ($_SESSION["nombre_completo"] ?? '');
        $concepto = $_POST["concepto"] ?? '';
        $tipo_servicio = $_POST["tipo_servicio"] ?? '';

        if ($monto <= 0) {
            echo json_encode(["status" => "error", "mensaje" => "El monto a pagar debe ser mayor a Q 0.00"]);
            exit();
        }

        // Si es pago directo de Transmetro o IRTRA sin solicitud previa, crear la solicitud en el workflow automáticamente
        if (empty($solicitud_id)) {
            require_once __DIR__ . "/../Modelos/Solicitud.php";
            $solModel = new Solicitud();
            if ($tipo_servicio === 'transmetro' || stripos($concepto, 'Transmetro') !== false) {
                $datosT = [
                    'tarjeta_virtual' => 'T-' . rand(10000000, 99999999),
                    'monto' => $monto,
                    'metodo' => $metodo_pago,
                    'canal' => 'Portal Ciudadano SISC'
                ];
                $crea = $solModel->crear($ciudadano_id, 'RECARGA_TRANSMETRO', $datosT, 'NORMAL');
                if ($crea['status'] === 'success') {
                    $solicitud_id = $crea['solicitud_id'];
                }
            } elseif ($tipo_servicio === 'irtra' || stripos($concepto, 'IRTRA') !== false) {
                $datosI = [
                    'tipo_habitacion' => 'Hostal IRTRA · Habitación doble',
                    'fechas' => '14-16 nov 2026',
                    'personas' => 2,
                    'monto' => $monto,
                    'canal' => 'Portal Ciudadano SISC'
                ];
                $crea = $solModel->crear($ciudadano_id, 'RESERVA_HOSTAL_IRTRA', $datosI, 'ALTA');
                if ($crea['status'] === 'success') {
                    $solicitud_id = $crea['solicitud_id'];
                }
            }
        }

        $res = $pago->procesar($solicitud_id, $ciudadano_id, $monto, $metodo_pago, $tarjeta, $titular, $concepto);
        echo json_encode($res);
        break;

    case 'comprobante':
        header('Content-Type: application/json; charset=utf-8');
        $pago_id = $_GET["id"] ?? ($_POST["id"] ?? 0);
        $data = $pago->mostrarComprobante($pago_id);
        if ($data) {
            echo json_encode(["status" => "success", "data" => $data]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Comprobante no encontrado."]);
        }
        break;

    case 'exportar_csv':
        if (!isset($_SESSION["usuario_id"])) {
            exit("Acceso denegado");
        }
        $instId = $_SESSION["institucion_id"] ?? 0;
        $esAdmin = (($_SESSION["rol_nombre"] ?? '') === 'ADMINISTRADOR');

        $rows = $pago->listarPorInstitucion($instId, '', '', $esAdmin);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="conciliacion_pagos_sisc_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        // BOM UTF-8 para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID Pago', 'Fecha', 'Solicitud', 'Ciudadano', 'CUI', 'Pasarela', 'Monto (GTQ)', 'No. Autorización', 'Estado']);

        while ($r = $rows->fetch_assoc()) {
            fputcsv($output, [
                $r['pago_id'],
                $r['fecha_pago'],
                $r['numero_seguimiento'] ?: 'Directo',
                $r['ciudadano_nombre'],
                $r['cui'],
                $r['metodo_pago'],
                number_format($r['monto'], 2, '.', ''),
                $r['referencia_pasarela'],
                $r['estado']
            ]);
        }
        fclose($output);
        exit();
        break;

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["status" => "error", "mensaje" => "Operación no válida."]);
        break;
}
?>

