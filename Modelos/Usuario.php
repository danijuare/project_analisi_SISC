<?php
require_once __DIR__ . "/../config/Conexion.php";

class Usuario {

    public function __construct() {}

    /**
     * Registro de nuevo Ciudadano desde el portal público
     */
    public function registrarCiudadano($cui, $nombres, $apellidos, $fecha_nacimiento, $email, $telefono, $direccion, $nit, $numero_afiliacion_igss, $username, $password) {
        global $conexion;

        $cui = preg_replace('/\s+/', '', limpiarCadena($cui));
        $nombres = limpiarCadena($nombres);
        $apellidos = limpiarCadena($apellidos);
        $fecha_nacimiento = limpiarCadena($fecha_nacimiento);
        $email = limpiarCadena($email);
        $telefono = limpiarCadena($telefono);
        $direccion = limpiarCadena($direccion);
        $nit = limpiarCadena($nit);
        $numero_afiliacion_igss = limpiarCadena($numero_afiliacion_igss);
        $username = trim(limpiarCadena($username));

        // Si no se proporcionó username, usar el CUI como username por defecto
        if (empty($username)) {
            $username = $cui;
        }

        // 1. Validaciones de existencia previa
        $checkCui = ejecutarConsultaSimpleFila("SELECT cui FROM ciudadanos WHERE cui = '$cui'");
        if ($checkCui) {
            return ["status" => "error", "mensaje" => "Ya existe un registro con el CUI / DPI ingresado."];
        }

        $checkEmail = ejecutarConsultaSimpleFila("SELECT email FROM ciudadanos WHERE email = '$email'");
        if ($checkEmail) {
            return ["status" => "error", "mensaje" => "El correo electrónico ya se encuentra registrado."];
        }

        $checkUser = ejecutarConsultaSimpleFila("SELECT username FROM usuarios WHERE username = '$username'");
        if ($checkUser) {
            return ["status" => "error", "mensaje" => "El nombre de usuario ya está en uso."];
        }

        // Obtener Institución y Rol por defecto para Ciudadano
        $inst = ejecutarConsultaSimpleFila("SELECT institucion_id FROM instituciones WHERE codigo = 'SISC_CENTRAL' LIMIT 1");
        $institucion_id = $inst ? $inst['institucion_id'] : 1;

        $rol = ejecutarConsultaSimpleFila("SELECT rol_id FROM roles WHERE nombre = 'CIUDADANO' LIMIT 1");
        $rol_id = $rol ? $rol['rol_id'] : 3;

        // Iniciar transacción
        $conexion->begin_transaction();

        try {
            // Insertar datos del ciudadano
            $sqlCiudadano = "INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion, nit, numero_afiliacion_igss)
                             VALUES ('$cui', '$nombres', '$apellidos', '$fecha_nacimiento', '$email', '$telefono', '$direccion', '$nit', '$numero_afiliacion_igss')";
            
            $queryC = $conexion->query($sqlCiudadano);
            if (!$queryC) {
                throw new Exception("Error al guardar datos del ciudadano: " . $conexion->error);
            }
            $ciudadano_id = $conexion->insert_id;

            // Hash de contraseña seguro
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar usuario
            $sqlUsuario = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                           VALUES ($ciudadano_id, $institucion_id, '$username', '$password_hash', 'ciudadano', 1)";
            $queryU = $conexion->query($sqlUsuario);
            if (!$queryU) {
                throw new Exception("Error al crear cuenta de usuario: " . $conexion->error);
            }
            $usuario_id = $conexion->insert_id;

            // Asignar rol de ciudadano
            $sqlRol = "INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id)
                       VALUES ($usuario_id, $rol_id, $institucion_id)";
            $queryR = $conexion->query($sqlRol);
            if (!$queryR) {
                throw new Exception("Error al asignar rol: " . $conexion->error);
            }

            $conexion->commit();
            return [
                "status" => "success",
                "mensaje" => "¡Cuenta de ciudadano creada exitosamente! Ya puedes iniciar sesión con tu CUI o usuario.",
                "usuario_id" => $usuario_id,
                "username" => $username
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Creación de usuario administrativo / funcionario
     */
    public function insertarFuncionario($username, $password, $tipo_usuario, $institucion_id, $rol_id, $nombres = '', $apellidos = '', $email = '', $telefono = '') {
        global $conexion;

        $username = trim(limpiarCadena($username));
        $tipo_usuario = limpiarCadena($tipo_usuario);
        $institucion_id = intval($institucion_id);
        $rol_id = intval($rol_id);
        $nombres = limpiarCadena($nombres);
        $apellidos = limpiarCadena($apellidos);
        $email = limpiarCadena($email);
        $telefono = limpiarCadena($telefono);

        // Validar username
        $checkUser = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = '$username'");
        if ($checkUser) {
            return ["status" => "error", "mensaje" => "El nombre de usuario ya está registrado."];
        }

        $conexion->begin_transaction();
        try {
            $ciudadano_id = "NULL";
            // Si se suministraron nombres y apellidos de funcionario, registrar como ciudadano o referencia
            if (!empty($nombres) && !empty($apellidos)) {
                $fakeCui = "FUNC-" . time() . rand(10, 99);
                $sqlC = "INSERT INTO ciudadanos (cui, nombres, apellidos, fecha_nacimiento, email, telefono, direccion)
                         VALUES ('$fakeCui', '$nombres', '$apellidos', '1990-01-01', '$email', '$telefono', 'Institucional')";
                if ($conexion->query($sqlC)) {
                    $ciudadano_id = $conexion->insert_id;
                }
            }

            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $sqlU = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                     VALUES ($ciudadano_id, $institucion_id, '$username', '$password_hash', '$tipo_usuario', 1)";
            
            if (!$conexion->query($sqlU)) {
                throw new Exception("Error al insertar usuario: " . $conexion->error);
            }
            $usuario_id = $conexion->insert_id;

            // Rol
            $sqlR = "INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id)
                     VALUES ($usuario_id, $rol_id, $institucion_id)";
            if (!$conexion->query($sqlR)) {
                throw new Exception("Error al asignar rol: " . $conexion->error);
            }

            $conexion->commit();
            return ["status" => "success", "mensaje" => "Usuario registrado correctamente."];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Editar usuario existente
     */
    public function editar($usuario_id, $username, $institucion_id, $tipo_usuario, $rol_id, $activo, $nueva_password = '') {
        global $conexion;
        $usuario_id = intval($usuario_id);
        $username = trim(limpiarCadena($username));
        $institucion_id = intval($institucion_id);
        $tipo_usuario = limpiarCadena($tipo_usuario);
        $rol_id = intval($rol_id);
        $activo = intval($activo);

        // Validar username en otro usuario
        $check = ejecutarConsultaSimpleFila("SELECT usuario_id FROM usuarios WHERE username = '$username' AND usuario_id != $usuario_id");
        if ($check) {
            return ["status" => "error", "mensaje" => "El nombre de usuario ya pertenece a otra cuenta."];
        }

        $conexion->begin_transaction();
        try {
            if (!empty($nueva_password)) {
                $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                $sqlU = "UPDATE usuarios SET username = '$username', institucion_id = $institucion_id, tipo_usuario = '$tipo_usuario', activo = $activo, password_hash = '$hash' WHERE usuario_id = $usuario_id";
            } else {
                $sqlU = "UPDATE usuarios SET username = '$username', institucion_id = $institucion_id, tipo_usuario = '$tipo_usuario', activo = $activo WHERE usuario_id = $usuario_id";
            }

            if (!$conexion->query($sqlU)) {
                throw new Exception("Error al actualizar usuario: " . $conexion->error);
            }

            // Actualizar rol
            $checkUR = ejecutarConsultaSimpleFila("SELECT id FROM usuario_roles WHERE usuario_id = $usuario_id");
            if ($checkUR) {
                $sqlR = "UPDATE usuario_roles SET rol_id = $rol_id, institucion_id = $institucion_id WHERE usuario_id = $usuario_id";
            } else {
                $sqlR = "INSERT INTO usuario_roles (usuario_id, rol_id, institucion_id) VALUES ($usuario_id, $rol_id, $institucion_id)";
            }
            if (!$conexion->query($sqlR)) {
                throw new Exception("Error al actualizar rol del usuario: " . $conexion->error);
            }

            $conexion->commit();
            return ["status" => "success", "mensaje" => "Usuario actualizado correctamente."];
        } catch (Exception $e) {
            $conexion->rollback();
            return ["status" => "error", "mensaje" => $e->getMessage()];
        }
    }

    /**
     * Verificar Login por CUI, username o email institucional
     */
    public function verificar($identificacion, $password, $tipo_usuario_tab = 'ciudadano') {
        global $conexion;
        $idLimpio = trim(limpiarCadena($identificacion));
        $idSinEspacios = preg_replace('/\s+/', '', $idLimpio);

        // Buscar coincidencia en usuarios y ciudadanos
        $sql = "SELECT 
                    u.usuario_id,
                    u.username,
                    u.password_hash,
                    u.tipo_usuario,
                    u.activo,
                    u.institucion_id,
                    u.ciudadano_id,
                    u.ultimo_acceso,
                    c.cui,
                    c.nombres,
                    c.apellidos,
                    c.email,
                    c.telefono,
                    c.nit,
                    c.numero_afiliacion_igss,
                    i.nombre AS institucion_nombre,
                    i.codigo AS institucion_codigo,
                    r.rol_id,
                    r.nombre AS rol_nombre,
                    r.permisos
                FROM usuarios u
                LEFT JOIN ciudadanos c ON u.ciudadano_id = c.ciudadano_id
                LEFT JOIN instituciones i ON u.institucion_id = i.institucion_id
                LEFT JOIN usuario_roles ur ON u.usuario_id = ur.usuario_id
                LEFT JOIN roles r ON ur.rol_id = r.rol_id
                WHERE (u.username = '$idLimpio' 
                   OR c.cui = '$idSinEspacios' 
                   OR c.cui = '$idLimpio' 
                   OR c.email = '$idLimpio')
                LIMIT 1";

        $res = ejecutarConsultaSimpleFila($sql);

        if (!$res) {
            return false;
        }

        // Verificar si está activo
        if ((int)$res['activo'] !== 1) {
            return ["inactivo" => true];
        }

        // Verificar contraseña
        if (password_verify($password, $res['password_hash'])) {
            // Actualizar último acceso
            $u_id = $res['usuario_id'];
            ejecutarConsulta("UPDATE usuarios SET ultimo_acceso = NOW() WHERE usuario_id = $u_id");
            return $res;
        }

        return false;
    }

    /**
     * Listar todos los usuarios para la tabla administrativa
     */
    public function listar() {
        $sql = "SELECT 
                    u.usuario_id,
                    u.username,
                    u.tipo_usuario,
                    u.activo,
                    u.ultimo_acceso,
                    u.created_at,
                    c.cui,
                    c.nombres,
                    c.apellidos,
                    c.email,
                    i.nombre AS institucion_nombre,
                    i.codigo AS institucion_codigo,
                    r.rol_id,
                    r.nombre AS rol_nombre,
                    r.permisos
                FROM usuarios u
                LEFT JOIN ciudadanos c ON u.ciudadano_id = c.ciudadano_id
                LEFT JOIN instituciones i ON u.institucion_id = i.institucion_id
                LEFT JOIN usuario_roles ur ON u.usuario_id = ur.usuario_id
                LEFT JOIN roles r ON ur.rol_id = r.rol_id
                ORDER BY u.usuario_id DESC";
        return ejecutarConsulta($sql);
    }

    /**
     * Mostrar un usuario por ID
     */
    public function mostrar($usuario_id) {
        $usuario_id = intval($usuario_id);
        $sql = "SELECT 
                    u.usuario_id,
                    u.username,
                    u.tipo_usuario,
                    u.activo,
                    u.institucion_id,
                    u.ciudadano_id,
                    c.cui,
                    c.nombres,
                    c.apellidos,
                    c.email,
                    c.telefono,
                    ur.rol_id,
                    r.nombre AS rol_nombre
                FROM usuarios u
                LEFT JOIN ciudadanos c ON u.ciudadano_id = c.ciudadano_id
                LEFT JOIN usuario_roles ur ON u.usuario_id = ur.usuario_id
                LEFT JOIN roles r ON ur.rol_id = r.rol_id
                WHERE u.usuario_id = $usuario_id";
        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Activar o desactivar usuario
     */
    public function cambiarEstado($usuario_id, $activo) {
        $usuario_id = intval($usuario_id);
        $activo = intval($activo);
        $sql = "UPDATE usuarios SET activo = $activo WHERE usuario_id = $usuario_id";
        return ejecutarConsulta($sql);
    }

    /**
     * Listar todos los roles
     */
    public function listarRoles() {
        $sql = "SELECT rol_id, nombre, descripcion, permisos FROM roles ORDER BY rol_id ASC";
        return ejecutarConsulta($sql);
    }

    /**
     * Listar instituciones activas
     */
    public function listarInstituciones() {
        $sql = "SELECT institucion_id, nombre, codigo, tipo_integracion FROM instituciones WHERE activa = 1 ORDER BY institucion_id ASC";
        return ejecutarConsulta($sql);
    }
}
?>
