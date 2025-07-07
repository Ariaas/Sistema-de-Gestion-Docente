<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
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
                <button class="btn btn-success px-4" id="registrar">Registrar Título</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablatitulo">
                        <thead>
                            <tr>
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

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Títulos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" id="tituloprefijo_original" name="tituloprefijo_original">
                            <input type="hidden" id="titulonombre_original" name="titulonombre_original">
                            
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="tituloprefijo" class="form-label">Tipo (prefijo)</label>
                                        <select class="form-select" name="tituloprefijo" id="tituloprefijo" required>
                                            <option value="" disabled selected>Seleccione un tipo</option>
                                            <option value="Ing.">Ingeniero</option>
                                            <option value="MSc.">Maestría</option>
                                            <option value="Dr.">Doctorado</option>
                                            <option value="TSU.">TSU</option>
                                            <option value="Lic.">Licenciado</option>
                                        </select>
                                        <span id="stituloprefijo"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="titulonombre" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="titulonombre" name="titulonombre" placeholder="Ej: Informática" required>
                                        <span id="stitulonombre" class="text-danger"></span>
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
    <script type="text/javascript" src="public/js/titulo.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>
</html>