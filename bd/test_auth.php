<?php
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../Modelos/Usuario.php";

$u = new Usuario();

echo "=== TEST 1: LOGIN ADMIN ===" . PHP_EOL;
$resAdmin = $u->verificar('admin', 'admin123', 'funcionario');
if ($resAdmin && $resAdmin['rol_nombre'] === 'ADMINISTRADOR') {
    echo "SUCCESS: Admin logged in with role: " . $resAdmin['rol_nombre'] . PHP_EOL;
} else {
    echo "FAIL: Admin login failed" . PHP_EOL;
}

echo "=== TEST 2: LOGIN FUNCIONARIO RENAP ===" . PHP_EOL;
$resFunc = $u->verificar('funcionario_renap', 'renap123', 'funcionario');
if ($resFunc && $resFunc['rol_nombre'] === 'FUNCIONARIO') {
    echo "SUCCESS: Funcionario logged in with institution: " . $resFunc['institucion_nombre'] . PHP_EOL;
} else {
    echo "FAIL: Funcionario login failed" . PHP_EOL;
}

echo "=== TEST 3: LOGIN CIUDADANO (POR CUI) ===" . PHP_EOL;
$resCiud = $u->verificar('1234567890101', 'ciudadano123', 'ciudadano');
if ($resCiud && $resCiud['tipo_usuario'] === 'ciudadano') {
    echo "SUCCESS: Ciudadano logged in: " . $resCiud['nombres'] . " " . $resCiud['apellidos'] . PHP_EOL;
} else {
    echo "FAIL: Ciudadano login failed" . PHP_EOL;
}

echo "=== TEST 4: REGISTRO DE NUEVO CIUDADANO ===" . PHP_EOL;
$cuiTest = '9876543210987';
// Borrar si existía previamente para que el test sea idempotente
$checkPrev = $conexion->query("SELECT usuario_id FROM usuarios WHERE username = 'ana_garcia'")->fetch_row();
if ($checkPrev) {
    $conexion->query("DELETE FROM usuarios WHERE usuario_id = " . $checkPrev[0]);
    $conexion->query("DELETE FROM ciudadanos WHERE cui = '$cuiTest'");
}

$reg = $u->registrarCiudadano($cuiTest, 'Ana Lucia', 'García Reyes', '1995-08-20', 'ana.garcia@test.gt', '55551122', 'Zona 10, Guatemala', '888877-6', '11223344', 'ana_garcia', 'segura123');
echo "Registro status: " . $reg['status'] . " - " . $reg['mensaje'] . PHP_EOL;

if ($reg['status'] === 'success') {
    $loginNuevo = $u->verificar($cuiTest, 'segura123', 'ciudadano');
    if ($loginNuevo && $loginNuevo['tipo_usuario'] === 'ciudadano') {
        echo "SUCCESS: Nuevo ciudadano inicio sesion correctamente con su CUI!" . PHP_EOL;
    } else {
        echo "FAIL: Nuevo ciudadano no pudo iniciar sesion" . PHP_EOL;
    }
}

echo "=== TEST 5: CREACION DE FUNCIONARIO POR ADMIN ===" . PHP_EOL;
$checkPrevF = $conexion->query("SELECT usuario_id FROM usuarios WHERE username = 'auditor_sat'")->fetch_row();
if ($checkPrevF) {
    $conexion->query("DELETE FROM usuarios WHERE usuario_id = " . $checkPrevF[0]);
}

$instSat = $conexion->query("SELECT institucion_id FROM instituciones WHERE codigo = 'SAT'")->fetch_row()[0];
$rolFunc = $conexion->query("SELECT rol_id FROM roles WHERE nombre = 'FUNCIONARIO'")->fetch_row()[0];
$funcTest = $u->insertarFuncionario('auditor_sat', 'sat12345', 'funcionario', $instSat, $rolFunc, 'Jorge', 'Méndez', 'jorge.mendez@sat.gob.gt', '22003300');
echo "Funcionario status: " . $funcTest['status'] . " - " . $funcTest['mensaje'] . PHP_EOL;

$loginFunc = $u->verificar('auditor_sat', 'sat12345', 'funcionario');
if ($loginFunc && strpos($loginFunc['institucion_nombre'], 'SAT') !== false) {
    echo "SUCCESS: Nuevo funcionario de SAT autenticado y con rol FUNCIONARIO!" . PHP_EOL;
} else {
    echo "FAIL: Autenticacion de nuevo funcionario fallo" . PHP_EOL;
}
?>

