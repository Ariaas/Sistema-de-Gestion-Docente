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
    <title>Gestión de Categoría</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Categoría</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">
                    <i class="fas fa-plus me-2"></i>Registrar Categoría
                </button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablacategoria">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Categoría</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-6" style="display: none;">
                                        <label for="categoriaId">ID</label>
                                        <input class="form-control" type="text" id="categoriaId" name="categoriaId">
                                        <div class="invalid-feedback" id="scategoriaId"></div>
                                    </div>
                                    <div class="col-12">
                                        <label for="categoriaNombre" class="form-label">Categoría</label>
                                        <input class="form-control" type="text" id="categoriaNombre" name="categoriaNombre" required>
                                        <span id="scategoriaNombre"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    require_once("public/components/footer.php");
    ?>
    <script type="text/javascript" src="public/js/categoria.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>