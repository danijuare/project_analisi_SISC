<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../Modelos/Usuario.php";

$usuarioModel = new Usuario();

$op = isset($_GET["op"]) ? $_GET["op"] : '';

header('Content-Type: application/json; charset=utf-8');

switch ($op) {

    // ==========================================
    // 1. VERIFICACIÓN DE CREDENCIALES (LOGIN)
    // ==========================================
    case 'verificar':
        $identificacion = isset($_POST["identificacion"]) ? trim($_POST["identificacion"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
        $tipo_usuario_tab = isset($_POST["tipo_usuario"]) ? trim($_POST["tipo_usuario"]) : 'ciudadano';

        if (empty($identificacion) || empty($password)) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Por favor ingresa tu identificación y contraseña."
            ]);
            break;
        }

        $res = $usuarioModel->verificar($identificacion, $password, $tipo_usuario_tab);

        if (is_array($res) && isset($res["inactivo"]) && $res["inactivo"] === true) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Tu cuenta se encuentra inactiva. Contacta al soporte técnico o administrador."
            ]);
            break;
        }

        if ($res && is_array($res) && isset($res["usuario_id"])) {
            // Guardar datos en la sesión
            $_SESSION["usuario_id"] = $res["usuario_id"];
            $_SESSION["username"] = $res["username"];
            $_SESSION["tipo_usuario"] = $res["tipo_usuario"];
            $_SESSION["institucion_id"] = $res["institucion_id"];
            $_SESSION["institucion_nombre"] = $res["institucion_nombre"] ?? 'SISC Central';
            $_SESSION["institucion_codigo"] = $res["institucion_codigo"] ?? 'SISC';
            $_SESSION["rol_id"] = $res["rol_id"];
            $_SESSION["rol_nombre"] = $res["rol_nombre"] ?? 'CIUDADANO';
            $_SESSION["ciudadano_id"] = $res["ciudadano_id"] ?? '0';
            
            // Permisos como array
            $permisosStr = $res["permisos"] ?? '';
            $_SESSION["permisos"] = array_map('trim', explode(',', $permisosStr));

            $nombre = trim(($res["nombres"] ?? '') . ' ' . ($res["apellidos"] ?? ''));
            $_SESSION["nombre_completo"] = !empty($nombre) ? $nombre : $res["username"];
            $_SESSION["cui"] = $res["cui"] ?? '';
            $_SESSION["email"] = $res["email"] ?? '';
            $_SESSION["telefono"] = $res["telefono"] ?? '';
            $_SESSION["nit"] = $res["nit"] ?? '';
            $_SESSION["igss"] = $res["numero_afiliacion_igss"] ?? '';

            // Redirección según rol / tipo
            if ($res["tipo_usuario"] === 'ciudadano') {
                $redirect = 'inicio_ciudadano.php';
            } else {
                $redirect = 'bandeja_funcionario.php';
            }

            echo json_encode([
                "status" => "success",
                "mensaje" => "¡Bienvenido al sistema, " . $_SESSION["nombre_completo"] . "!",
                "redirect" => $redirect,
                "rol" => $_SESSION["rol_nombre"],
                "tipo" => $res["tipo_usuario"]
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Credenciales incorrectas. Verifica tu número de DPI/usuario y contraseña."
            ]);
        }
    break;

    // ==========================================
    // 2. REGISTRO DE NUEVO CIUDADANO
    // ==========================================
    case 'registrar_ciudadano':
        $cui = isset($_POST["cui"]) ? trim($_POST["cui"]) : '';
        $nombres = isset($_POST["nombres"]) ? trim($_POST["nombres"]) : '';
        $apellidos = isset($_POST["apellidos"]) ? trim($_POST["apellidos"]) : '';
        $fecha_nacimiento = isset($_POST["fecha_nacimiento"]) ? trim($_POST["fecha_nacimiento"]) : '';
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
        $telefono = isset($_POST["telefono"]) ? trim($_POST["telefono"]) : '';
        $direccion = isset($_POST["direccion"]) ? trim($_POST["direccion"]) : '';
        $nit = isset($_POST["nit"]) ? trim($_POST["nit"]) : '';
        $igss = isset($_POST["numero_afiliacion_igss"]) ? trim($_POST["numero_afiliacion_igss"]) : '';
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
        $password_confirm = isset($_POST["password_confirm"]) ? trim($_POST["password_confirm"]) : '';

        // Validaciones
        if (empty($cui) || empty($nombres) || empty($apellidos) || empty($fecha_nacimiento) || empty($email) || empty($password)) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Los campos CUI, Nombres, Apellidos, Fecha de Nacimiento, Email y Contraseña son obligatorios."
            ]);
            break;
        }

        $cuiLimpio = preg_replace('/\s+/', '', $cui);
        if (strlen($cuiLimpio) < 9) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "El número de CUI / DPI debe tener al menos 9 dígitos válidos."
            ]);
            break;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "El correo electrónico ingresado no tiene un formato válido."
            ]);
            break;
        }

        if (strlen($password) < 6) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "La contraseña debe tener un mínimo de 6 caracteres."
            ]);
            break;
        }

        if (!empty($password_confirm) && $password !== $password_confirm) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Las contraseñas no coinciden."
            ]);
            break;
        }

        $resp = $usuarioModel->registrarCiudadano(
            $cuiLimpio,
            $nombres,
            $apellidos,
            $fecha_nacimiento,
            $email,
            $telefono,
            $direccion,
            $nit,
            $igss,
            $username,
            $password
        );

        echo json_encode($resp);
    break;

    // ==========================================
    // 3. CREAR / EDITAR USUARIO ADMINISTRATIVO
    // ==========================================
    case 'guardaryeditar_funcionario':
        $usuario_id = isset($_POST["usuario_id"]) ? trim($_POST["usuario_id"]) : '';
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
        $tipo_usuario = isset($_POST["tipo_usuario"]) ? trim($_POST["tipo_usuario"]) : 'funcionario';
        $institucion_id = isset($_POST["institucion_id"]) ? intval($_POST["institucion_id"]) : 1;
        $rol_id = isset($_POST["rol_id"]) ? intval($_POST["rol_id"]) : 2;
        $activo = isset($_POST["activo"]) ? intval($_POST["activo"]) : 1;
        $nombres = isset($_POST["nombres"]) ? trim($_POST["nombres"]) : '';
        $apellidos = isset($_POST["apellidos"]) ? trim($_POST["apellidos"]) : '';
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
        $telefono = isset($_POST["telefono"]) ? trim($_POST["telefono"]) : '';

        if (empty($usuario_id)) {
            // Nuevo usuario
            if (empty($username) || empty($password)) {
                echo json_encode(["status" => "error", "mensaje" => "Usuario y contraseña son requeridos."]);
                break;
            }
            $resp = $usuarioModel->insertarFuncionario($username, $password, $tipo_usuario, $institucion_id, $rol_id, $nombres, $apellidos, $email, $telefono);
            echo json_encode($resp);
        } else {
            // Editar usuario
            if (empty($username)) {
                echo json_encode(["status" => "error", "mensaje" => "El nombre de usuario es requerido."]);
                break;
            }
            $resp = $usuarioModel->editar($usuario_id, $username, $institucion_id, $tipo_usuario, $rol_id, $activo, $password);
            echo json_encode($resp);
        }
    break;

    // ==========================================
    // 4. LISTAR USUARIOS
    // ==========================================
    case 'listar':
        $query = $usuarioModel->listar();
        $data = [];
        while ($reg = $query->fetch_object()) {
            $nombreComp = trim(($reg->nombres ?? '') . ' ' . ($reg->apellidos ?? ''));
            $data[] = [
                "usuario_id" => $reg->usuario_id,
                "username" => $reg->username,
                "nombre_completo" => !empty($nombreComp) ? $nombreComp : '<em>(Sin nombre)</em>',
                "cui" => $reg->cui ?? 'N/A',
                "email" => $reg->email ?? 'N/A',
                "tipo_usuario" => $reg->tipo_usuario,
                "institucion_nombre" => $reg->institucion_nombre ?? 'Central',
                "rol_nombre" => $reg->rol_nombre ?? 'Sin Rol',
                "activo" => $reg->activo,
                "ultimo_acceso" => $reg->ultimo_acceso ?? 'Nunca',
                "created_at" => $reg->created_at
            ];
        }
        echo json_encode(["status" => "success", "data" => $data]);
    break;

    // ==========================================
    // 5. MOSTRAR UN USUARIO
    // ==========================================
    case 'mostrar':
        $usuario_id = isset($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : 0;
        $res = $usuarioModel->mostrar($usuario_id);
        if ($res) {
            echo json_encode(["status" => "success", "data" => $res]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Usuario no encontrado."]);
        }
    break;

    // ==========================================
    // 6. CAMBIAR ESTADO (ACTIVAR / DESACTIVAR)
    // ==========================================
    case 'cambiar_estado':
        $usuario_id = isset($_POST["usuario_id"]) ? intval($_POST["usuario_id"]) : 0;
        $activo = isset($_POST["activo"]) ? intval($_POST["activo"]) : 0;
        $res = $usuarioModel->cambiarEstado($usuario_id, $activo);
        if ($res) {
            echo json_encode(["status" => "success", "mensaje" => "Estado actualizado exitosamente."]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "No se pudo actualizar el estado."]);
        }
    break;

    // ==========================================
    // 7. LISTAR ROLES DISPONIBLES
    // ==========================================
    case 'listar_roles':
        $query = $usuarioModel->listarRoles();
        $roles = [];
        while ($r = $query->fetch_object()) {
            $roles[] = [
                "rol_id" => $r->rol_id,
                "nombre" => $r->nombre,
                "descripcion" => $r->descripcion,
                "permisos" => $r->permisos
            ];
        }
        echo json_encode(["status" => "success", "data" => $roles]);
    break;

    // ==========================================
    // 8. LISTAR INSTITUCIONES
    // ==========================================
    case 'listar_instituciones':
        $query = $usuarioModel->listarInstituciones();
        $insts = [];
        while ($i = $query->fetch_object()) {
            $insts[] = [
                "institucion_id" => $i->institucion_id,
                "nombre" => $i->nombre,
                "codigo" => $i->codigo
            ];
        }
        echo json_encode(["status" => "success", "data" => $insts]);
    break;

    // ==========================================
    // 9. CERRAR SESIÓN
    // ==========================================
    case 'salir':
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: ../vistas/login.php");
        exit;
    break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Operación no válida."]);
    break;
}
?>
