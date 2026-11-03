<?php
    require "../config/Conexion.php";

    class Usuario {

        public function __construct() {
            
        }

        // Método para insertar un nuevo usuario
        public function insertar($ciudadano_id, $institucion_id, $username, $password_hash, $tipo_usuario) {
            $username = limpiarCadena($username);
            
            // Validar si el username ya existe
            $sql_buscar = "SELECT usuario_id FROM usuarios WHERE username = '$username'";
            if (ejecutarConsultaSimpleFila($sql_buscar)) {
                return "El nombre de usuario ya existe.";
            }

            $sql_insertar = "INSERT INTO usuarios (ciudadano_id, institucion_id, username, password_hash, tipo_usuario, activo)
                             VALUES ('$ciudadano_id', '$institucion_id', '$username', '$password_hash', '$tipo_usuario', 1)";
            
            return ejecutarConsulta_retornarID($sql_insertar) ? true : false;
        }

        // Método para editar usuario
        public function editar($usuario_id, $ciudadano_id, $institucion_id, $username, $tipo_usuario, $activo) {
            $username = limpiarCadena($username);
            
            $sql_buscar = "SELECT usuario_id FROM usuarios WHERE username = '$username' AND usuario_id != '$usuario_id'";
            $resultado = ejecutarConsulta($sql_buscar);
            
            if ($fetch = $resultado->fetch_object()) {
                return "El nombre de usuario ya pertenece a otro registro.";
            }

            $sql_editar = "UPDATE usuarios SET 
                            ciudadano_id = '$ciudadano_id',
                            institucion_id = '$institucion_id',
                            username = '$username',
                            tipo_usuario = '$tipo_usuario',
                            activo = '$activo'
                           WHERE usuario_id = '$usuario_id'";
                           
            return ejecutarConsulta($sql_editar) ? true : false;
        }

        // Método para desactivar usuario
        public function desactivar($usuario_id) {
            $sql = "UPDATE usuarios SET activo = 0 WHERE usuario_id = '$usuario_id'";
            return ejecutarConsulta($sql);
        }

        // Método para activar usuario
        public function activar($usuario_id) {
            $sql = "UPDATE usuarios SET activo = 1 WHERE usuario_id = '$usuario_id'";
            return ejecutarConsulta($sql);
        }

        // Método para eliminar usuario
        public function eliminar($usuario_id) {
            $sql = "DELETE FROM usuarios WHERE usuario_id = '$usuario_id'";
            return ejecutarConsulta($sql);
        }

        // Mostrar datos de un usuario específico
        public function mostrar($usuario_id) {
            $sql = "SELECT * FROM usuarios WHERE usuario_id = '$usuario_id'";
            return ejecutarConsultaSimpleFila($sql);
        }

        // Listar todos los usuarios con información de ciudadano e institución
        public function listar() {
            $sql = "SELECT 
                        u.usuario_id,
                        u.username,
                        u.tipo_usuario,
                        u.activo,
                        u.created_at,
                        c.nombres,
                        c.apellidos,
                        i.nombre AS institucion
                    FROM usuarios u
                    LEFT JOIN ciudadanos c ON u.ciudadano_id = c.ciudadano_id
                    LEFT JOIN instituciones i ON u.institucion_id = i.institucion_id";
            return ejecutarConsulta($sql);
        }

        // Método clave para verificar el Login (por username o CUI del ciudadano)
        public function verificar($identificacion, $password) {
            $identificacion = limpiarCadena($identificacion);
            
            $sql = "SELECT u.*, c.cui 
                    FROM usuarios u 
                    LEFT JOIN ciudadanos c ON u.ciudadano_id = c.ciudadano_id 
                    WHERE (u.username = '$identificacion' OR c.cui = '$identificacion') 
                    AND u.activo = 1";
            
            $resultado = ejecutarConsultaSimpleFila($sql);
            
            if ($resultado) {
                // Validar contraseña cifrada
                if (password_verify($password, $resultado['password_hash'])) {
                    return $resultado;
                }
            }
            return false;
        }
    }
?>