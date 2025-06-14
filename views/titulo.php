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

    <title>Títulos</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Títulos</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablatitulo">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Tipo (prefijo)</th>
                                <th>Nombre</th>
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
                        <h5 class="modal-title">Formulario de Títulos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4" style="display: none;">
                                        <label for="tituloid">ID</label>
                                        <input class="form-control" type="text" id="tituloid" name="tituloid">
                                        <span id="stituloid"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tituloprefijo" class="form-label">Tipo (prefijo)</label>
                                        <select class="form-select" name="tituloprefijo" id="tituloprefijo" required>
                                            <option value="" disabled selected>Seleccione un tipo (prefijo)</option>
                                            <option value="Ingeniero">Ingeniero</option>
                                            <option value="Master">Maestría</option>
                                            <option value="Doctorado">Doctorado</option>
                                        </select>
                                        <span id="stituloprefijo"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="titulonombre" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="titulonombre" name="titulonombre" required>
                                        <span id="stitulonombre"></span>
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
    <script type="text/javascript" src="public/js/titulo.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>