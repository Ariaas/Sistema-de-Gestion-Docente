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

<body>

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="d-flex flex-column align-items-md-center" style="margin-top: 110px;">
            <center>
                <h2 class="text-primary text-md-center">Gestionar Secciones</h2>
            </center>
            <br>
            <div class="container">
                <div class="text-md-left">
                    <button class="btn btn-success" id="registrar">Registrar</button>
                </div>
                <div class="text-md-left">
                    <button class="btn btn-success" id="unir">Unir</button>
                </div>
            </div>

            <br>
            <div class="container text-md-center">
                <button class="btn btn-primary" id="toggleTables">Cambiar Tabla</button>
            </div>
            <br>

            <div class="container card shadow mb-4" id="tablaseccionContainer"> <!-- Primera tabla -->
                <br>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaseccion">
                            <thead>
                                <tr>
                                    <th style="display: none;">ID</th>
                                    <th>Código</th>
                                    <th style="display: none;">ID Trayecto</th>
                                    <th>Trayecto</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoconsulta1"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="container card shadow mb-4" id="tablaunionContainer" style="display: none;"> <!-- Segunda tabla -->
                <br>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaunion">
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
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulario de Sección</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off">
                            <input type="hidden" name="accion" id="accion" value="registrar">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="secCodigo">Código</label>
                                        <input class="form-control" type="text" id="codigoSeccion" name="codigoSeccion" required>
                                        <span id="ssecCodigo"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="secCantidad">Cantidad</label>
                                        <input class="form-control" type="number" id="cantidadSeccion" name="cantidadSeccion" required>
                                        <span id="ssecCantidad"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="trayecto" class="form-label">Trayecto</label>
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
                            <div class="row mt-3 d-flex justify-content-center align-items-md-center">
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-dark" id="proceso">Registrar</button>
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
    <?php require_once("public/components/footer.php"); ?>
    <?php //require_once("public/components/body.php"); 
    ?>
    <!-- Footer -->
    </div>
    <!-- fin de container -->

    <!-- Scripts -->
    <script type="text/javascript" src="public/js/seccion.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>