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

    <title>Ejes</title>
</head>

<body>

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="d-flex flex-column align-items-md-center" style="margin-top: 110px;">
            <center>
                <h2 class="text-primary text-md-center">Gestionar Ejes</h2>
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

            <div class="container card shadow mb-4" id="tabla1Container"> <!-- Primera tabla -->
                <br>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabla1">
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
            </div>

            <div class="container card shadow mb-4" id="tabla2Container" style="display: none;"> <!-- Segunda tabla -->
                <br>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabla2">
                            <thead>
                                <tr>
                                    <th style="display: none;">ID</th>
                                    <th>Nombre</th>
                                    <th>Secciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoconsulta2"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('toggleTables').addEventListener('click', function() {
                    const tabla1 = document.getElementById('tabla1Container');
                    const tabla2 = document.getElementById('tabla2Container');

                    if (tabla1.style.display === 'none') {
                        tabla1.style.display = 'block';
                        tabla2.style.display = 'none';
                    } else {
                        tabla1.style.display = 'none';
                        tabla2.style.display = 'block';
                    }
                });
            </script>
        </section>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulario de Ejes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <input autocomplete="off" type="text" class="form-control" name="accion" id="accion" style="display: none;">
                        <form method="post" id="f" autocomplete="off">
                            <input autocomplete="off" type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="container">
                                <div class="row mb-3">
                                    <div style="display: none;" class="col-md-6">
                                        <label for="secId">ID</label>
                                        <input class="form-control" type="text" id="secId" name="secId">
                                        <span id="sejeId"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="secCodigo">Codigo</label>
                                        <input class="form-control" type="text" id="secCodigo" name="secCodigo">
                                        <span id="ssecCodigo"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="secCantidad">Cantidad</label>
                                        <input class="form-control" type="text" id="secCantidad" name="secCantidad">
                                        <span id="ssecCantidad"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="trayecto" class="form-label">Trayecto</label>
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
                                    <span id="strayecto" class="error"></span>
                                </div>
                            </div>
                            <div class="row mt-3 d-flex justify-content-center align-items-md-center">
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-dark" id="proceso"></button>
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
    <script type="text/javascript" src="public/js/eje.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>