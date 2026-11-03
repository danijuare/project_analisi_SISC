var tabla;

function init(){
    listar();

    $("#formulario").on("submit", function(e){
        guardaryeditar(e);
    });

    limpiar();
}

//limpiar datos
function limpiar(){
    $("#iddepartamento").val("");
    $("#codigo_departamento").val("");
    $("#nombre_departamento").val("");
    $("#descripcion_departamento").val("N/A");
}

//listar
function listar(){
    tabla = $('#tbllistado').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: '<"row"<"col-md-6"B><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        ajax: {
            url: '../ajax/departamento.php?op=listar',
            type: "get",
            dataType: "json",
            error: function(e){
                console.log(e.responseText);
            }
        },
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "search": "Buscar:",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "bDestroy": true,
        "iDisplayLength": 5,
        "order": [[ 0, "desc" ]]
    }).DataTable();
}

//guardar o editar
function guardaryeditar(e){
    e.preventDefault();
    var formData = new FormData($("#formulario")[0]);

    $.ajax({
        url: "../ajax/departamento.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos){
            datos = datos.trim();

            if(datos.includes("ya existe") || datos.includes("pertenece a otro")){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: datos,
                    confirmButtonColor: '#3085d6'
                });
            } else if(datos.includes("exito")){
                Swal.fire({
                    icon: 'success',
                    title: '¡Excelente!',
                    text: datos,
                    showConfirmButton: false,
                    timer: 1500
                });
                limpiar();
                listar();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: datos
                });
            }
        }
    });
}

//mostrar para editar
function mostrar(iddepartamento){
    $.post("../ajax/departamento.php?op=mostrar", {iddepartamento : iddepartamento}, function(data, status){
        data = JSON.parse(data);
        
        $("#iddepartamento").val(data.iddepartamento);
        $("#codigo_departamento").val(data.codigo_departamento);
        $("#nombre_departamento").val(data.nombre_departamento);
        $("#descripcion_departamento").val(data.descripcion_departamento);
    });
}

//eliminar
function eliminar(iddepartamento){
    Swal.fire({
        title: '¿Esta seguro de eliminar este departamento?',
        text: "¡Si tiene empleados asignados, el sistema no permitira borrarlo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/departamento.php?op=eliminar", {iddepartamento : iddepartamento}, function(e){
                e = e.trim();
                
                if(e.includes("exito")){
                    Swal.fire(
                        '¡Eliminado!',
                        e,
                        'success'
                    );
                    listar();
                } else {
                    Swal.fire(
                        'Peticion denegada',
                        e,
                        'warning'
                    );
                }
            });
        }
    });
}

init();