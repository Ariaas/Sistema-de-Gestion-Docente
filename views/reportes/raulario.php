<?php
// El nombre del archivo es raulario.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte de Aulario (Horario por Aula)</title>
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
                <h2 class="text-primary">Reporte de Aulario</h2>
                <p class="text-muted">Seleccione los criterios para generar los horarios de las aulas.</p>
            </div>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteAulario" target="_blank">
                    <div class="row g-3 mb-4 justify-content-center">
                        
                        <div class="col-md-6">
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

                        <div class="col-md-6">
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

                        <div class="col-md-12">
                            <label for="espacio_id" class="form-label">Aula (Opcional)</label>
                            <select class="form-select form-select-sm" name="espacio_id" id="espacio_id">
                                <option value="">-- Todas las Aulas --</option>
                                 <?php
                                if (!empty($listaEspacios)) { 
                                    foreach ($listaEspacios as $espacio) {
                                        echo "<option value='" . htmlspecialchars($espacio['esp_codigo']) . "'>"
                                            . htmlspecialchars($espacio['esp_codigo']) . " (" . htmlspecialchars($espacio['esp_tipo']) . ")"
                                            . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="generar_aulario_btn" name="generar_aulario_report">
                                <i class="fas fa-file-excel me-2"></i>Generar Horario de Aulas
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/raulario.js"></script>
</body>
</html>