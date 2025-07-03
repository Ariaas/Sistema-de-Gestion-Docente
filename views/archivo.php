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
    <title>Gestión de Notas</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Notas y Remediales</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="btnNuevoRegistro">
                    <i class="fas fa-plus me-2"></i> Crear Registro de Remedial
                </button>
            </div>

            <div class="w-100 datatable-ui" style="max-width: 1100px; margin: 0 auto 2rem auto; border: 1px solid #dee2e6; border-radius: .5rem; padding: 1.5rem 2rem;">
                 <h3 class="text-secondary mt-3 mb-4">Datos de Remediales Registrados</h3>
                 <div class="table-responsive">
                     <table class="table table-striped table-hover w-100" id="tablaRegistros">
                         <thead>
                             <tr>
                                 <th>Año</th>
                                 <th>Sección</th>
                                 <th>U. Curricular</th>
                                 <th>Estudiantes</th>
                                 <th>Para PER</th>
                                 <th>Aprob. PER</th>
                                 <th>Archivo Definitivo</th>
                                 <th>Acciones</th>
                             </tr>
                         </thead>
                         <tbody id="resultadosRegistros"></tbody>
                     </table>
                 </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalRegistroNotas">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Creación de Remedial</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formRegistro" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="registrar_notas">
                            <input type="hidden" id="seccion_codigo" name="seccion_codigo">
                            <input type="hidden" id="uc_nombre" name="uc_nombre">
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="anio" class="form-label">Año Académico</label>
                                    <select class="form-select" name="anio" id="anio" required>
                                        <option value="" disabled selected>Seleccione un año</option>
                                        <?php foreach ($anios as $anio) {
                                            echo "<option value='{$anio['ani_id']}'>{$anio['ani_anio']}</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="seccion" class="form-label">Sección</label>
                                    <select class="form-select" name="seccion" id="seccion" required disabled>
                                        <option value="" disabled selected>Seleccione un año primero</option>
                                    </select>
                                    <span id="scantidad" class="form-text"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="docente" class="form-label">Docente</label>
                                    <select class="form-select" name="docente" id="docente" required>
                                        <option value="" disabled selected>Seleccione un docente</option>
                                        <?php foreach ($docentes as $docente) {
                                            echo "<option value='{$docente['doc_id']}'>{$docente['doc_nombre_completo']}</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ucurricular" class="form-label">Unidad Curricular</label>
                                    <select class="form-select" name="ucurricular" id="ucurricular" required>
                                        <option value="" disabled selected>Seleccione una U.C.</option>
                                        <?php foreach ($unidadesCurriculares as $unidad) {
                                            echo "<option value='{$unidad['uc_id']}'>{$unidad['uc_nombre']}</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad_per" class="form-label">Estudiantes para PER</label>
                                    <input type="number" class="form-control" name="cantidad_per" id="cantidad_per" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha" class="form-label">Fecha de Resguardo</label>
                                    <input class="form-control" type="date" id="fecha" name="fecha" required />
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                     <label for="archivo_notas" class="form-label">Archivo de Notas Definitivas (Opcional)</label>
                                     <input type="file" class="form-control" name="archivo_notas" id="archivo_notas" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary">GUARDAR</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalAprobadosPer">
             <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Registrar Aprobados y Archivo del Remedial (PER)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formAprobadosPer" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="registrar_per">
                            <input type="hidden" id="rem_id_per" name="rem_id">
                            <input type="hidden" id="per_uc_nombre" name="uc_nombre">
                            <input type="hidden" id="per_seccion_codigo" name="seccion_codigo">

                            <p>Sección: <strong id="per_seccion"></strong></p>
                            <p>U. Curricular: <strong id="per_uc"></strong></p>
                            <p>Estudiantes en PER: <strong id="per_cantidad_en_remedial"></strong></p>

                            <div class="mb-3">
                                <label for="cantidad_aprobados" class="form-label">Cantidad de Estudiantes APROBADOS</label>
                                <input type="number" class="form-control" name="cantidad_aprobados" id="cantidad_aprobados" required min="0">
                            </div>

                            <div class="mb-3">
                                <label for="archivo_per" class="form-label">Archivo de Notas del PER (Opcional)</label>
                                <input type="file" class="form-control" name="archivo_per" id="archivo_per" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-info">REGISTRAR</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerNotasPer">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">Archivos de Notas PER</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Sección: <strong id="verPer_seccion"></strong></p>
                        <p>U. Curricular: <strong id="verPer_uc"></strong></p>
                        <div class="table-responsive">
                            <table class="table table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>Nombre de Archivo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="listaArchivosPerModal"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script src="public/js/archivo.js"></script>
</body>
</html>