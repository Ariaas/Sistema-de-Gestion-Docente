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
    <title>Gestión de Fases</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Fases</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Fase</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablafase">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Trayecto</th>
                                <th>N° Fase</th>
                                <th>Fecha Apertura</th>
                                <th>Fecha Cierre</th>
                                <th style="display: none;">tra_id</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta">

                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Fase</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="faseId" id="faseId">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="traId" class="form-label">Trayecto</label>
                                    <select class="form-select" id="traId" name="traId" required>
                                        <option value="">Seleccione un trayecto</option>
                                    </select>
                                    <span id="straId" class="text-danger"></span>
                                </div>
                                <div class="col-md-12">
                                    <label for="faseNumero" class="form-label">Número de Fase</label>
                                    <select class="form-select" id="faseNumero" name="faseNumero" required>
                                        <option value="">Seleccione una fase</option>
                                        <option value="1">Fase 1</option>
                                        <option value="2">Fase 2</option>
                                    </select>
                                    <span id="sfaseNumero" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="faseApertura" class="form-label">Fecha de Apertura</label>
                                    <input class="form-control" type="date" id="faseApertura" name="faseApertura" required>
                                    <span id="sfaseApertura" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="faseCierre" class="form-label">Fecha de Cierre</label>
                                    <input class="form-control" type="date" id="faseCierre" name="faseCierre" required>
                                    <span id="sfaseCierre" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center mt-4">
                                <button type="button" class="btn btn-primary px-4 me-2" id="proceso">Guardar</button>
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script type="text/javascript" src="public/js/fase.js"></script>
</body>

</html>