<?php
session_start();

require_once "../config/conexion.php";
require_once "../Modelos/Usuario.php";

$usuarioModel = new Usuario();

$op = isset($_GET["op"]) ? $_GET["op"] : '';

switch ($op) {
    case 'verificar':
        $identificacion = isset($_POST["identificacion"]) ? trim($_POST["identificacion"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
        $tipo_usuario = isset($_POST["tipo_usuario"]) ? trim($_POST["tipo_usuario"]) : 'ciudadano';

        $res = $usuarioModel->verificar($identificacion, $password, $tipo_usuario);

        if ($res) {
            $_SESSION["usuario_id"] = $res["usuario_id"];
            $_SESSION["username"] = $res["username"];
            $_SESSION["tipo_usuario"] = $res["tipo_usuario"];

            $redirect = ($res["tipo_usuario"] === 'ciudadano') ? 'inicio_ciudadano.php' : 'bandeja_funcionario.php';

            echo json_encode([
                "status" => "success",
                "mensaje" => "¡Acceso correcto! Redirigiendo...",
                "redirect" => $redirect
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Credenciales incorrectas o usuario inactivo."
            ]);
        }
    break;

    case 'listar':
        $rspta = $usuarioModel->listar();
        echo json_encode($rspta);
    break;
}
?>