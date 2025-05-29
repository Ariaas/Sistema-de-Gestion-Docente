<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestionar Horario Docente</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Horario Docente</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Horario Docente</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablacertificado">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Lapso</th>
                                <th>Actividad</th>
                                <th>Descripción</th>
                                <th>Dependencia</th>
                                <th>Observación</th>
                                <th>Hora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Horario Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="container">
                                <div class="row mb-3">

                                    <div class="col-md-4" style="display: none;">
                                        <label for="certificadoid">id</label>
                                        <input class="form-control" type="text" id="certificadoid" name="certificadoid" min="1">
                                        <span id="scertificadoid"></span>
                                    </div>


                                    <div class="col-md-4">

                                        <label for="certificadonombre">Nombre</label>
                                        <input class="form-control" type="text" id="certificadonombre" name="certificadonombre" placeholder="Nombre del certificado" required>
                                        <span id="scertificadonombre"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="trayecto">Trayecto</label>
                                        <select class="form-select" name="trayecto" id="trayecto">
                                            <option value="" disabled selected>Seleccione un trayecto</option>
                                            <?php
                                            if (!empty($trayectos)) {
                                                foreach ($trayectos as $trayecto) {
                                                    echo "<option value='" . $trayecto['tra_id'] . "'>" . $trayecto['tra_numero'] . " - " . $trayecto['tra_anio'] . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No hay trayectos disponibles</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 d-flex justify-content-center align-items-md-center">
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin del Modal -->
    </main>
    <!-- Footer -->
    <?php
    require_once("public/components/footer.php");
    ?>
    <!-- Scripts -->
    <script type="text/javascript" src="public/js/certificado.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>