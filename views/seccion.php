<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>

    <title>Secciones</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Secciones</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px; gap: 10px;">
                <button class="btn btn-success px-4" id="registrar">Registrar</button>
            </div>

            <div class="datatable-ui w-100" id="tablaseccionContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaseccion">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Código de Sección</th>
                                <th>Cantidad de Estudiantes</th>
                                <th>Año</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta1"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Sección</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion" value="registrar">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div style="display: none;" class="col-md-6">
                                        <label for="seccionId" class="form-label">ID</label>
                                        <input class="form-control" type="text" id="seccionId" name="seccionId" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="codigoSeccion" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoSeccion" name="codigoSeccion" required>
                                        <span id="scodigoSeccion"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cantidadSeccion" class="form-label">Cantidad de Estudiantes</label>
                                        <input class="form-control" type="number" id="cantidadSeccion" name="cantidadSeccion" required>
                                        <span id="scantidadSeccion"></span>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label for="anioId" class="form-label">Año</label>
                                        <select class="form-select" name="anioId" id="anioId" required>
                                            <option value="" disabled selected>Seleccione un año</option>
                                            <?php
                                            if (!empty($anios)) {
                                                foreach ($anios as $anio) {
                                                    echo "<option value='" . $anio['ani_id'] . "'>" . $anio['ani_anio'] . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No hay años activos disponibles</option>";
                                            }
                                            ?>
                                        </select>
                                        <span id="sanioId" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/seccion.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>