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

    <title>Trayectos</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Trayectos</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Trayecto</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablatrayecto">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Número</th>
                                <th>Año</th>
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
                        <h5 class="modal-title">Formulario de Trayectos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6" style="display: none;">
                                        <label for="trayectoId">ID</label>
                                        <input class="form-control" type="text" id="trayectoId" name="trayectoId">
                                        <span id="strayectoId"></span>
                                    </div>
                                    <!-- Campo Número -->
                                    <div class="col-md-6">
                                        <label for="trayectoNumero" class="form-label">Número</label>
                                        <input class="form-control" type="text" id="trayectoNumero" name="trayectoNumero" required>
                                        <span id="strayectoNumero"></span>
                                    </div>

                                    <!-- Campo Año -->
                                    <div class="col-md-6">
                                        <label for="trayectoAnio" class="form-label">Año</label>
                                        <select class="form-select" name="trayectoAnio" id="trayectoAnio" required>
                                            <option value="" disabled selected>Seleccione un Año</option>
                                            <?php
                                            $anoActual = date('Y');
                                            for ($year = 1999; $year <= 2070; $year++):
                                                $selected = ($year == $anoActual) ? ' selected' : '';
                                            ?>
                                                <option value="<?= $year ?>" <?= $selected ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <span id="strayectoAnio"></span>
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
    <script type="text/javascript" src="public/js/trayecto.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>