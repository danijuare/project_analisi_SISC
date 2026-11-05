<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../Modelos/Esb.php";

$esb = new Esb();
$op = $_GET["op"] ?? '';

switch ($op) {

    case 'reintentar':
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION["usuario_id"])) {
            echo json_encode(["status" => "error", "mensaje" => "No autenticado."]);
            exit();
        }
        $mensaje_id = $_POST["mensaje_id"] ?? 0;
        $res = $esb->reintentar($mensaje_id);
        echo json_encode($res);
        break;

    case 'exportar_csv_cruzado':
        if (!isset($_SESSION["usuario_id"])) {
            exit("Acceso denegado");
        }
        $inst = $_GET["institucion"] ?? 'Todas';
        $est = $_GET["estado"] ?? 'Todos';

        $rows = $esb->controlCruzado($inst, $est);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="control_cruzado_sisc_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['No. Solicitud', 'CUI', 'Ciudadano', 'Institución', 'Trámite / Proceso', 'Fecha Inicio', 'Estado']);

        while ($r = $rows->fetch_assoc()) {
            fputcsv($output, [
                $r['numero_seguimiento'],
                $r['cui'],
                $r['ciudadano_nombre'],
                $r['institucion_nombre'],
                $r['proceso_nombre'],
                $r['fecha_inicio'],
                $r['estado']
            ]);
        }
        fclose($output);
        exit();
        break;

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["status" => "error", "mensaje" => "Operación no soportada."]);
        break;
}
?>

