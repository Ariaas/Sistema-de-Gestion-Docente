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
    <title>Unidades Curriculares</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Unidades Curriculares</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px; gap: 10px;">
                <button class="btn btn-success px-4" id="registrar">Registrar</button>
                <button class="btn btn-success px-4" id="unir">Unir</button>
            </div>

            <div class="w-100 text-center mb-3" style="max-width: 1100px;">
                <button class="btn btn-primary px-4" id="toggleTables">Cambiar Tabla</button>
            </div>

            <div class="datatable-ui w-100" id="tablaucContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablauc">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Horas Independientes</th>
                                <th>Horas Asistidas</th>
                                <th>Horas Totales</th>
                                <th>Trayecto</th>
                                <th>Eje</th>
                                <th>Área</th>
                                <th>Créditos</th>
                                <th>Horas Acedemicas</th>
                                <th>Periodo</th>
                                <th>Electiva</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta1"></tbody>
                    </table>
                </div>
            </div>

            <!-- <div class="datatable-ui w-100" id="tablaunionContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem; display: none;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaunion">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Docente</th>
                                <th>Unidad Curricular</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta2"></tbody>
                    </table>
                </div>
            </div> -->
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
                                    <div style="display: none;" class="col-md-6">
                                        <label for="idUC" class="form-label">ID</label>
                                        <input class="form-control" type="text" id="idUC" name="idUC" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="codigoUC" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoUC" name="codigoUC" required>
                                        <span id="scodigoUC"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nombreUC" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="nombreUC" name="nombreUC" required>
                                        <span id="snombreUC"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="creditosUC" class="form-label">Unidades de Crédito</label>
                                        <input class="form-control" type="number" id="creditosUC" name="creditosUC" required>
                                        <span id="screditosUC"></span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="independienteUC" class="form-label">Horas Independientes</label>
                                        <input class="form-control" type="text" id="independienteUC" name="independienteUC" required>
                                        <span id="sindependienteUC"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="asistidaUC" class="form-label">Horas Asistidas</label>
                                        <input class="form-control" type="text" id="asistidaUC" name="asistidaUC" required>
                                        <span id="sasistidaUC"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="academicaUC" class="form-label">Horas Académicas</label>
                                        <input class="form-control" type="number" id="academicaUC" name="academicaUC" required>
                                        <span id="sacademicaUC"></span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="trayectoUC" class="form-label">Trayecto</label>
                                        <select class="form-select" name="trayectoUC" id="trayectoUC" required>
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

                                    <div class="col-md-4">
                                        <label for="ejeUC" class="form-label">Eje</label>
                                        <select class="form-select" name="ejeUC" id="ejeUC" required>
                                            <option value="" disabled selected>Seleccione un eje</option>
                                            <?php
                                            if (!empty($ejes)) {
                                                foreach ($ejes as $eje) {
                                                    echo "<option value='" . $eje['eje_id'] . "'>" . $eje['eje_nombre'] . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No hay ejes disponibles</option>";
                                            }
                                            ?>
                                        </select>
                                        <span id="seje"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="areaUC" class="form-label">Área</label>
                                        <select class="form-select" name="areaUC" id="areaUC" required>
                                            <option value="" disabled selected>Seleccione un Área</option>
                                            <?php
                                            if (!empty($areas)) {
                                                foreach ($areas as $area) {
                                                    echo "<option value='" . $area['area_id'] . "'>" . $area['area_nombre'] . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No hay areas disponibles</option>";
                                            }
                                            ?>
                                        </select>
                                        <span id="sarea"></span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="periodoUC" class="form-label">Periodo</label>
                                        <select class="form-select" name="periodoUC" id="periodoUC">
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="anual">Anual</option>
                                            <option value="1">Fase 1</option>
                                            <option value="2">Fase 2</option>
                                        </select>
                                        <span id="speriodoUC"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="electivaUC" class="form-label">Electiva</label>
                                        <select class="form-select" name="electivaUC" id="electivaUC">
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="0">No Electiva</option>
                                            <option value="1">Electiva</option>
                                        </select>
                                        <span id="selectivaUC"></span>
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
    <script type="text/javascript" src="public/js/uc.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>