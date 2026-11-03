<?php
    require "../config/Conexion.php";

    Class Departamento{

        public function __construct() {
            
        }

        public function insertar_departamento($codigo, $nombre, $descripcion){
            //validar si el codgio ya existe
            $validacion = $this->validar_codigo_departamento($codigo);
                if (is_string($validacion)) {
                return $validacion;
            }
            
            //insetar nuevo departamento
            $sql_insertar = "INSERT INTO departamentos (codigo_departamento, nombre_departamento,
                descripcion_departamento)
                VALUES ('$codigo', '$nombre', '$descripcion')
            ";
            if(ejecutarConsulta($sql_insertar)){
                return true;
            }else{
                return false;
            }
        }

        private function validar_codigo_departamento($codigo){
            $sql_buscar = "SELECT iddepartamento FROM departamentos WHERE codigo_departamento = '$codigo'";
            if(ejecutarConsultaSimpleFila($sql_buscar)){
                return "Codigo de Departamento ya existe.";
            }else{
                return true;
            }
        }

        public function listar() {
            $sql = "SELECT
                    d.iddepartamento,
                    d.codigo_departamento,
                    d.nombre_departamento,
                    d.descripcion_departamento
                FROM departamentos d";
            return ejecutarConsulta($sql);
        }

            public function editar_departamento($iddepartamento, $codigo, $nombre, $descripcion){
            $sql_buscar = "SELECT iddepartamento FROM departamentos
                WHERE codigo_departamento = '$codigo' AND iddepartamento != '$iddepartamento'";
            $resultado = ejecutarConsulta($sql_buscar);
            
            if($fetch = $resultado->fetch_object()){
                return "El codigo de departamento ya pertenece a otro departamento.";
            }

            $sql_editar = "UPDATE departamentos SET 
                            codigo_departamento = '$codigo', 
                            nombre_departamento = '$nombre', 
                            descripcion_departamento = '$descripcion' 
                           WHERE iddepartamento = '$iddepartamento'";
                           
            if(ejecutarConsulta($sql_editar)){
                return true;
            } else {
                return false;
            }
        }

        public function eliminar_departamento($iddepartamento){
            $sql_verificar = "SELECT idempleado FROM empleados WHERE iddepartamento = '$iddepartamento'";
            $resultado = ejecutarConsulta($sql_verificar);
            
            if($fetch = $resultado->fetch_object()){
                return "No se puede eliminar el departamento porque tiene empleados asignados.";
            }

            $sql_eliminar = "DELETE FROM departamentos WHERE iddepartamento = '$iddepartamento'";
            
            if(ejecutarConsulta($sql_eliminar)){
                return true;
            } else {
                return false;
            }
        }

        public function mostrar($iddepartamento){
            $sql_mostrar = "SELECT
                d.iddepartamento,
                d.codigo_departamento,
                d.nombre_departamento,
                d.descripcion_departamento
            FROM departamentos d
            WHERE d.iddepartamento = '$iddepartamento'";
            return ejecutarConsultaSimpleFila($sql_mostrar);
        }
    }
?>