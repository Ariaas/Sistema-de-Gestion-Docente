<?php

 if (!isset($_SESSION['name'])) {

     header('Location: .');
    exit();
 }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Horario</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Horario</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Horario</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaListadoHorarios">
                        <thead>
                            <tr>
                                <th>Sección</th>
                                <th>Trayecto</th> 
                                <th>Año</th> 
                                <th>Fase</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta">
                            </tbody>
                    </table>
                </div>
            </div>


        <div class="modal fade" tabindex="-1" role="dialog" id="modal-horario">
            <div class="modal-dialog modal-xl" role="document"> <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalHorarioGlobalTitle">Formulario de Horario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="form-horario" autocomplete="off" class="needs-validation" novalidate>
                           <input type="hidden" name="accion" id="accion"> 
                           <input type="hidden" name="hor_id" id="hor_id"> 
                           <input type="hidden" id="current_editing_sec_id_hidden"> 
                           <input type="hidden" id="current_editing_fase_id_hidden"> 
                           
                           <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-6 mb-3" id="seccion-principal-group">
                                        <label for="seccion_principal_id" class="form-label">Sección <span class="text-danger">*</span></label>
                                        <select class="form-select" id="seccion_principal_id" name="seccion_principal_id" required>
                                            <option value="">Seleccionar Sección...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="fase-group">
                                        <label for="fase_id" class="form-label">Fase <span class="text-danger">*</span></label>
                                        <select class="form-select" id="fase_id" name="fase_id" required>
                                            <option value="">Seleccionar Fase...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-group-horario-individual" id="espacio-group" style="display:none;"> <label for="esp_id" class="form-label">Espacio</label><select class="form-select" id="esp_id" name="esp_id" required></select></div>
                                <div class="mb-3 form-group-horario-individual" id="dia-group" style="display:none;"> <label for="dia" class="form-label">Día</label><select class="form-select" id="dia" name="dia" required><option value="">Seleccionar Día</option><option value="Lunes">Lunes</option><option value="Martes">Martes</option><option value="Miércoles">Miércoles</option> <option value="Jueves">Jueves</option><option value="Viernes">Viernes</option><option value="Sábado">Sábado</option><option value="Domingo">Domingo</option></select></div>
                                <div class="mb-3 form-group-horario-individual" id="hora-inicio-group" style="display:none;"> <label for="hora_inicio" class="form-label">Hora Inicio</label><input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required><span id="shora_inicio" class="text-danger"></span></div>
                                <div class="mb-3 form-group-horario-individual" id="hora-fin-group" style="display:none;"> <label for="hora_fin" class="form-label">Hora Fin</label><input type="time" class="form-control" id="hora_fin" name="hora_fin" required><span id="shora_fin" class="text-danger"></span></div>
                                <div class="mb-3 form-group-horario-individual" id="uc-group" style="display:none;"> <label for="uc_id" class="form-label">Unidad curricular</label><select class="form-select" id="uc_id" name="uc_id" required></select></div>
                                <div class="mb-3 form-group-horario-individual" id="docente-group" style="display:none;"> <label for="doc_id" class="form-label">Docente</label><select class="form-select" id="doc_id" name="doc_id" required></select></div>
                                
                                <div class="header mt-4" id="controlesTablaHorario"></div>

                                <div class="table-responsive" id="contenedorTablaHorario">
                                    <table class="table table-bordered text-center" id="tablaHorario"> <thead>
                                            <tr>
                                                <th style="min-width: 150px;">Hora</th>
                                                <th>Lunes</th>
                                                <th>Martes</th>
                                                <th>Miércoles</th>
                                                <th>Jueves</th>
                                                <th>Viernes</th>
                                                <th>Sábado</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     <div class="modal fade" id="modalEntradaHorario" tabindex="-1" aria-labelledby="etiquetaModalEntradaHorario" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="etiquetaModalEntradaHorario">Añadir/Editar Clase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formularioEntradaHorario">
                        <div class="mb-3">
                            <label for="modalFranjaHoraria" class="form-label">Franja Horaria</label>
                            <input type="text" class="form-control" id="modalFranjaHoraria" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modalDia" class="form-label">Día</label>
                            <input type="text" class="form-control" id="modalDia" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modalSeleccionarDocente" class="form-label">Docente <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalSeleccionarDocente" required>
                                <option value="">Seleccionar Docente</option>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalSeleccionarUc" class="form-label">Unidad Curricular <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalSeleccionarUc" required disabled>
                                <option value="">Primero seleccione un docente</option>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalSeleccionarEspacio" class="form-label">Espacio (Aula/Lab) <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalSeleccionarEspacio" name="esp_id_modal" required disabled> 
                                <option value="">Seleccionar Espacio</option>
                                </select>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnGuardarClase">Guardar Cambios</button>
                        <button type="button" class="btn btn-danger" id="btnEliminarEntrada" style="display:none;">Eliminar Clase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </main>

    <?php require_once("public/components/footer.php"); ?>
    
    <script src="public/js/horario.js"></script>
</body>
</html>