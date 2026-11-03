<?php 
include('header.php'); 
?>

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-building text-primary me-2"></i> Gestión de Departamentos
            </h2>
            <p class="text-muted small mb-0">Administre el registro y control de los departamentos de la institución.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium">
                Módulo Activo / MVC PHP
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fs-6 fw-semibold">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Formulario de Departamento
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form name="formulario" id="formulario" method="POST">
                        <input type="hidden" name="iddepartamento" id="iddepartamento">

                        <div class="mb-3">
                            <label for="codigo_departamento" class="form-label fw-medium text-secondary small">Código:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-barcode"></i></span>
                                <input type="text" class="form-control" name="codigo_departamento" id="codigo_departamento" placeholder="Ej: DEP-001" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_departamento" class="form-label fw-medium text-secondary small">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-signature"></i></span>
                                <input type="text" class="form-control" name="nombre_departamento" id="nombre_departamento" placeholder="Ej: Recursos Humanos" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion_departamento" class="form-label fw-medium text-secondary small">Descripción:</label>
                            <textarea class="form-control" name="descripcion_departamento" id="descripcion_departamento" rows="3" placeholder="Detalle opcional..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-custom text-white py-2" id="btnGuardar">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Registro
                            </button>
                            <button type="button" class="btn btn-light border py-2 text-secondary" onclick="limpiar()">
                                <i class="fa-solid fa-eraser me-2"></i> Limpiar Campos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fs-6 fw-bold text-dark">
                        <i class="fa-solid fa-list-ul me-2 text-primary"></i> Departamentos Registrados
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tbllistado" class="table table-hover align-middle w-100 text-center">
                            <thead class="table-light text-uppercase fs-7">
                                <tr>
                                    <th class="py-3">Opciones</th>
                                    <th class="py-3">Código</th>
                                    <th class="py-3">Nombre</th>
                                    <th class="py-3">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="js/departamento.js"></script>

<?php 
include('footer.php'); 
?>