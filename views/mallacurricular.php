<?php
// Verificar si el usuario ha iniciado sesión
// if (!isset($_SESSION['name'])) {
//     // Redirigir al usuario a la página de inicio de sesión
//     header('Location: .');
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Malla Curricular</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Malla Curricular</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Malla Curricular</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablamalla">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Año</th>
                                 <th>Cohorte</th>
                                <th>Descripcion</th>
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
                        <h5 class="modal-title">Formulario de Malla</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-4" style="display: none;">
                                        <label for="mal_id"></label>
                                        <input class="form-control" type="text" id="mal_id" name="mal_id" min="1">
                                        <span id="smalla"></span>
                                    </div>

                                    <!-- Campo Código -->
                                    <div class="col-md-6">
                                        <label for="mal_codigo" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="mal_codigo" name="mal_codigo" placeholder="Ejemplo: 1123" required>
                                        <span id="smalcodigo" class="form-text"></span>
                                    </div>

                                    <!-- Campo Nombre -->
                                    <div class="col-md-6">
                                        <label for="mal_nombre" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="mal_nombre" name="mal_nombre" placeholder="Ejemplo: Malla 2022" required>
                                        <span id="smalnombre" class="form-text"></span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Campo Año -->
                                    <div class="col-md-4">
                                        <label for="mal_Anio" class="form-label">Año</label>
                                        <select class="form-select" name="mal_Anio" id="mal_Anio" required>
                                            <option value="" disabled selected>Seleccione un Año</option>
                                            <?php
                                            $anoActual = date('Y');
                                            for ($year = 1999; $year <= 2070; $year++):
                                                $selected = ($year == $anoActual) ? ' selected' : '';
                                            ?>
                                                <option value="<?= $year ?>" <?= $selected ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <span id="smalanio" class="form-text"></span>
                                    </div>

                                    <!-- Campo Cohorte -->
                                    <div class="col-md-4">
                                        <label for="mal_cohorte" class="form-label">Cohorte</label>
                                        <input class="form-control" type="text" id="mal_cohorte" name="mal_cohorte" placeholder="Ejemplo: 3" required>
                                        <span id="smalcohorte" class="form-text"></span>
                                    </div>

                                    <!-- Campo Descripción -->
                                    <div class="col-md-4">
                                        <label for="mal_descripcion" class="form-label">Descripción</label>
                                        <input class="form-control" type="text" id="mal_descripcion" name="mal_descripcion" placeholder="Descripción breve" required>
                                        <span id="smaldescripcion" class="form-text"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 d-flex justify-content-center align-items-md-center">
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso">GUARDAR</button>
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
    <script type="text/javascript" src="public/js/mallacurricular.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>