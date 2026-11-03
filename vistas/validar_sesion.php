<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

/**
 * Función para verificar si el usuario tiene uno de los roles permitidos
 * @param array $roles_permitidos Lista de nombres de rol permitidos, ej: ['ADMINISTRADOR', 'FUNCIONARIO']
 */
function verificarAcceso($roles_permitidos = []) {
    $rolActual = $_SESSION["rol_nombre"] ?? '';
    $tipoUsuario = $_SESSION["tipo_usuario"] ?? '';

    // El Administrador siempre tiene acceso
    if ($rolActual === 'ADMINISTRADOR') {
        return true;
    }

    if (!empty($roles_permitidos)) {
        if (!in_array($rolActual, $roles_permitidos)) {
            // Si es un ciudadano intentando entrar a área de funcionario, mandar a su portal
            if ($tipoUsuario === 'ciudadano') {
                header("Location: inicio_ciudadano.php?error=acceso_denegado");
                exit();
            } else {
                header("Location: bandeja_funcionario.php?error=acceso_denegado");
                exit();
            }
        }
    }
    return true;
}

/**
 * Verifica si el usuario actual posee un permiso específico
 */
function tienePermiso($permiso) {
    if (isset($_SESSION["rol_nombre"]) && $_SESSION["rol_nombre"] === 'ADMINISTRADOR') {
        return true;
    }
    $permisos = $_SESSION["permisos"] ?? [];
    return in_array($permiso, $permisos);
}

/**
 * Retorna la información del usuario en sesión
 */
function usuarioActual() {
    return [
        "id" => $_SESSION["usuario_id"] ?? 0,
        "username" => $_SESSION["username"] ?? '',
        "nombre_completo" => $_SESSION["nombre_completo"] ?? 'Usuario',
        "tipo_usuario" => $_SESSION["tipo_usuario"] ?? 'ciudadano',
        "rol_nombre" => $_SESSION["rol_nombre"] ?? 'CIUDADANO',
        "institucion_id" => $_SESSION["institucion_id"] ?? 1,
        "institucion_nombre" => $_SESSION["institucion_nombre"] ?? 'SISC Central',
        "institucion_codigo" => $_SESSION["institucion_codigo"] ?? 'SISC',
        "cui" => $_SESSION["cui"] ?? '',
        "email" => $_SESSION["email"] ?? '',
        "nit" => $_SESSION["nit"] ?? '',
        "igss" => $_SESSION["igss"] ?? ''
    ];
}
?>

