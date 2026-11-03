<?php
require_once "../Modelos/Departamento.php";

$departamento = new Departamento();

$iddepartamento = isset($_POST["iddepartamento"]) ? limpiarCadena($_POST["iddepartamento"]) : "";
$codigo_departamento = isset($_POST["codigo_departamento"]) ? limpiarCadena($_POST["codigo_departamento"]) : "";
$nombre_departamento = isset($_POST["nombre_departamento"]) ? limpiarCadena($_POST["nombre_departamento"]) : "";
$descripcion_departamento = isset($_POST["descripcion_departamento"]) ? limpiarCadena($_POST["descripcion_departamento"]) : "";

switch ($_GET["op"]){
    case 'guardaryeditar':
        if (empty($iddepartamento)){
            $respuesta = $departamento->insertar_departamento($codigo_departamento, $nombre_departamento, $descripcion_departamento);
            
            if (is_string($respuesta)) {
                echo $respuesta;
            } else if ($respuesta) {
                echo "Departamento registrado con exito";
            } else {
                echo "No se pudieron registrar todos los datos";
            }
        } else {
            $respuesta = $departamento->editar_departamento($iddepartamento, $codigo_departamento, $nombre_departamento,
                $descripcion_departamento);
            
            if (is_string($respuesta)) {
                echo $respuesta;
            } else if ($respuesta) {
                echo "Departamento actualizado con exito";
            } else {
                echo "Error al actualizar";
            }
        }
    break;

    case 'eliminar':
        $respuesta = $departamento->eliminar_departamento($iddepartamento);
        if (is_string($respuesta)) {
            echo $respuesta;
        } else if ($respuesta) {
            echo "Departamento eliminado con exito";
        } else {
            echo "No se pudo eliminar el departamento";
        }
    break;

    case 'mostrar':
        $rspta = $departamento->mostrar($iddepartamento);
        echo json_encode($rspta);
    break;

    case 'listar':
        $rspta = $departamento->listar();
        $data = array();

        while ($reg = $rspta->fetch_object()){
            $data[] = array(
                "0" => '<button class="btn btn-warning btn-sm text-white" onclick="mostrar('.$reg->iddepartamento.')" title="Editar"><i class="fa fa-pencil"></i></button> '.
                       '<button class="btn btn-danger btn-sm" onclick="eliminar('.$reg->iddepartamento.')" title="Eliminar"><i class="fa fa-trash"></i></button>',
                "1" => $reg->codigo_departamento,
                "2" => $reg->nombre_departamento,
                "3" => $reg->descripcion_departamento
            );
        }
        
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
    break;

    case 'selectDepartamento':
        $rspta = $departamento->listar();
        while ($reg = $rspta->fetch_object()){
            echo '<option value=' . $reg->iddepartamento . '>' . $reg->codigo_departamento. ' | '.$reg->nombre_departamento.'</option>';
        }
    break;
}
?>