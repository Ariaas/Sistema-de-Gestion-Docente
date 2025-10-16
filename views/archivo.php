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
    <title>Carga de Notas Definitivas</title>
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Carga de Notas Definitivas</h2>

            <?php if (!empty($alerta_datos)) {
                echo $alerta_datos;
            } ?>

            <div class="w-100 d-flex justify-content-end align-items-center mb-3" style="max-width: 1200px;">
                <button class="btn btn-success px-4" id="btnNuevoRegistro">
                    <i class="fas fa-plus me-2"></i> Registrar Notas Definitivas
                </button>
            </div>

            <div class="w-100 datatable-ui" style="max-width: 1200px; margin: 0 auto 2rem auto; border: 1px solid #dee2e6; border-radius: .5rem; padding: 1.5rem 2rem;">
                <table class="table table-striped table-hover w-100" id="tablaRegistros">
                    <thead>
                        <tr>
                            <th>Año Académico</th>
                            <th>Docente</th>
                            <th>Sección</th>
                            <th>Unidad Curricular</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="resultadosRegistros"></tbody>
                </table>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalRegistroNotas">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Resguardo de Acta</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formRegistro" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="accion" value="registrar_notas">
                            <input type="hidden" id="uc_nombre" name="uc_nombre">

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="anio" class="form-label">Año Académico</label>
                                    <select class="form-select" name="anio" id="anio" required>
                                        <option value="" disabled>Seleccione un año</option>
                                        <?php foreach ($anios as $anio) {
                                            $valor_compuesto = $anio['ani_anio'] . ':' . $anio['ani_tipo'];
                                            $texto_opcion = $anio['ani_anio'];
                                            $selected = ($anio_seleccionado == $valor_compuesto) ? 'selected' : '';
                                            echo "<option value='{$valor_compuesto}' {$selected}>{$texto_opcion}</option>";
                                        } ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione un año académico.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="docente" class="form-label">Docente</label>
                                    <select class="form-select" name="docente" id="docente" required>
                                        <option value="" disabled selected>Seleccione un docente</option>
                                        <?php foreach ($docentes as $doc) {
                                            echo "<option value='{$doc['doc_cedula']}'>{$doc['doc_nombre']} {$doc['doc_apellido']}</option>";
                                        } ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione un docente.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="seccion" class="form-label">Sección</label>
                                    <select class="form-select" name="seccion" id="seccion" required disabled>
                                        <option value="" disabled selected>Seleccione año y docente</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione una sección.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ucurricular" class="form-label">Unidad Curricular</label>
                                    <select class="form-select" name="ucurricular" id="ucurricular" required disabled>
                                        <option value="" disabled selected>Seleccione una sección primero</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione una unidad curricular.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="archivo_notas" class="form-label">Acta de Notas Finales (PDF, Word, Excel)</label>
                                    <input type="file" class="form-control" name="archivo_notas" id="archivo_notas" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                    <div class="invalid-feedback">Por favor, adjunte el acta de notas.</div>
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
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script src="public/js/archivo.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>

</body>

</html>