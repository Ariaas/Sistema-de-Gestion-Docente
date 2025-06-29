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
    <title>Gestión de Años</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Años</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Año</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaanio">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Año</th>
                                <th>Apertura Fase 1</th>
                                <th>Cierre Fase 1</th>
                                <th>Apertura Fase 2</th>
                                <th>Cierre Fase 2</th>
                                <th>Activo</th>
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
                        <h5 class="modal-title">Formulario de Años</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <input type="hidden" id="aniId" name="aniId">

                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="aniAnio" class="form-label">Año</label>
                                        <select class="form-select" name="aniAnio" id="aniAnio" required>
                                            <option value="" disabled>Seleccione un Año</option>
                                            <?php
                                            $anoActual = date('Y');
                                            for ($year = 1999; $year <= 2070; $year++):
                                                $selected = ($year == $anoActual) ? ' selected' : '';
                                            ?>
                                                <option value="<?= $year ?>" <?= $selected ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <span id="saniAnio"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="aniAperturaFase1" class="form-label">Apertura Fase 1</label>
                                        <input type="date" class="form-control" id="aniAperturaFase1" name="aniAperturaFase1" required>
                                        <span id="saniAperturaFase1"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="aniCierraFase1" class="form-label">Cierre Fase 1</label>
                                        <input type="date" class="form-control" id="aniCierraFase1" name="aniCierraFase1" required>
                                        <span id="saniCierraFase1"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="aniAperturaFase2" class="form-label">Apertura Fase 2</label>
                                        <input type="date" class="form-control" id="aniAperturaFase2" name="aniAperturaFase2" required>
                                        <span id="saniAperturaFase2"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="aniCierraFase2" class="form-label">Cierre Fase 2</label>
                                        <input type="date" class="form-control" id="aniCierraFase2" name="aniCierraFase2" required>
                                        <span id="saniCierraFase2"></span>
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
    <?php
    require_once("public/components/footer.php");
    ?>
    <script type="text/javascript" src="public/js/anio.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>