<?php
// Verificar si el usuario ha iniciado sesión
// if (!isset($_SESSION['name'])) {
//     // Redirigir al usuario a la página de inicio de sesión
//     header('Location: .');
//     exit();
// }
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
                <button class="btn btn-success px-4" id="unir">Unir</button>
            </div>

            <div class="w-100 text-center mb-3" style="max-width: 1100px;">
                <button class="btn btn-primary px-4" id="toggleTables">Cambiar Tabla</button>
            </div>

            <div class="datatable-ui w-100" id="tablaseccionContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaseccion">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Código</th>
                                <th>Trayecto</th>
                                <th>Cantidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta1"></tbody>
                    </table>
                </div>
            </div>

            <div class="datatable-ui w-100" id="tablaunionContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem; display: none;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaunion">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Secciones</th>
                                <th>Trayecto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta2"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Modal -->
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
                                    <div class="col-md-6">
                                        <label for="seccionId" class="form-label">ID</label>
                                        <input class="form-control" type="text" id="seccionId" name="seccionId" required>
                                        <span id="ssecId"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="codigoSeccion" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoSeccion" name="codigoSeccion" required>
                                        <span id="ssecCodigo"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cantidadSeccion" class="form-label">Cantidad</label>
                                        <input class="form-control" type="number" id="cantidadSeccion" name="cantidadSeccion" required>
                                        <span id="ssecCantidad"></span>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="trayectoSeccion" class="form-label">Trayecto</label>
                                        <select class="form-select" name="trayectoSeccion" id="trayectoSeccion" required>
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
                                        <span id="strayecto"></span>
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
        <!-- Fin del Modal -->

    </main>
    <!-- Footer -->
    <?php require_once("public/components/footer.php"); ?>
    <!-- Scripts -->
    <script type="text/javascript" src="public/js/seccion.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>