<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Resguardar Notas</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Resguardar Notas</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="btnSubir">Registrar Notas</button>
            </div>

            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaArchivo">
                        <thead>
                            <tr>
                                <th>Nombre de Archivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultados"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modalArchivo">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Resguardar notas</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formArchivo" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" value="subir">

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="archivo" class="form-label">Seleccionar archivo</label>
                                    <input type="file" class="form-control" name="archivo" id="archivo" accept=".pdf,.doc,.docx,.txt" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="docente" class="form-label">Docente</label>
                                    <select class="form-select" name="docente" id="docente" required>
                                        <option value="" disabled selected>Seleccione un docente</option>
                                        <?php
                                        foreach ($docentes as $docente) {
                                            echo "<option value='" . htmlspecialchars($docente['doc_nombre']) . "'>" . htmlspecialchars($docente['doc_nombre']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <span id="sdocente" class="error"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="ucurricular" class="form-label">Unidad Curricular</label>
                                    <select class="form-select" name="ucurricular" id="ucurricular" required>
                                        <option value="" disabled selected>Seleccione una unidad curricular</option>
                                        <?php
                                        foreach ($unidadcurriculares as $unidad) {
                                            echo "<option value='" . htmlspecialchars($unidad['uc_nombre']) . "'>" . htmlspecialchars($unidad['uc_nombre']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <span id="sucurricular" class="error"></span>
                                </div>

                                <div class="col-md-6">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input class="form-control" type="date" id="fecha" name="fecha" />
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary me-2">GUARDAR</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>

    <script src="public/js/archivo.js"></script>
</body>

</html>