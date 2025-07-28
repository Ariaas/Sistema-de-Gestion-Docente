<?php
<<<<<<< HEAD
// El nombre del archivo sigue siendo rseccion.php por consistencia
=======

>>>>>>> e49ad21f436d00715071ce8c78621385678fa505
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte de Horarios por Filtro</title>
    <style>
        .form-label { font-weight: 500; }
        .required-mark { color: red; margin-left: 2px; }
    </style>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Horarios por Filtro</h2>
                <p class="text-muted">Seleccione los criterios para generar los horarios de las secciones.</p>
            </div>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteSeccion" target="_blank">
                    <div class="row g-3 mb-4 justify-content-center">
                        <div class="col-md-4">
                            <label for="anio_id" class="form-label">Año Académico<span class="required-mark">*</span></label>
                            <select class="form-select form-select-sm" name="anio_id" id="anio_id" required>
                                <option value="">-- Seleccione un Año --</option>
                                <?php
                                if (!empty($listaAnios)) {
                                    foreach ($listaAnios as $anio) {
                                        echo "<option value='" . htmlspecialchars($anio['ani_anio']) . "'>" . htmlspecialchars($anio['ani_anio'] . ' (' . $anio['ani_tipo'] . ')') . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="fase_id" class="form-label">Fase<span class="required-mark">*</span></label>
                            <select class="form-select form-select-sm" name="fase_id" id="fase_id" required>
                                <option value="">-- Seleccione una Fase --</option>
                                <?php
                                if (!empty($listaFases)) {
                                    foreach ($listaFases as $fase) {
                                        echo "<option value='" . htmlspecialchars($fase['fase_numero']) . "'>Fase " . htmlspecialchars($fase['fase_numero']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="trayecto_id" class="form-label">Trayecto</label>
                            <select class="form-select form-select-sm" name="trayecto_id" id="trayecto_id">
                                <option value="">-- Todos los Trayectos --</option>
                                <?php
                                if (!empty($listaTrayectos)) {
                                    foreach ($listaTrayectos as $trayecto) {
                                        echo "<option value='" . htmlspecialchars($trayecto['uc_trayecto']) . "'>Trayecto " . htmlspecialchars($trayecto['uc_trayecto']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="generar_seccion_btn" name="generar_seccion_report">
                                <i class="fas fa-file-excel me-2"></i>Generar Horarios Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/rseccion.js"></script>
</body>
</html>