<?php
require_once __DIR__ . "/../config/Conexion.php";

echo "Modificando tabla pagos para permitir solicitud_id NULL...\n";
$res = $conexion->query("ALTER TABLE pagos MODIFY solicitud_id INT NULL");
if ($res) {
    echo "OK: Columna solicitud_id modificada a NULL exitosamente.\n";
} else {
    echo "Error: " . $conexion->error . "\n";
}
?>

