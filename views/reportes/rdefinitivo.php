<?php
// views/reportes/rdefinitivoemit.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); // Global head components ?>
    <title>Reporte Definitivo EMIT por Fase y Año</title>
    <style>
        .form-label { font-weight: 500; }
        .required-mark { color: red; margin-left: 2px;}
    </style>
    </head>
<body>
    <?php require_once("public/components/sidebar.php"); // Sidebar component ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte Definitivo EMIT por Fase y Año</h2>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteDefinitivoEmit" target="_blank">
                        <div class="row g-3 mb-4 align-items-center">
                            <div class="col-md-6">
                                <label for="anio_def" class="form-label">Seleccione el Año:<span class="required-mark">*</span></label>
                                <select class="form-select form-select-sm" name="anio_def" id="anio_def" required>
                                    <option value="">-- Seleccione Año --</option>
                                    <?php
                                    if (!empty($listaAnios)) {
                                        foreach ($listaAnios as $itemAnio) {
                                            echo "<option value='" . htmlspecialchars($itemAnio['tra_anio']) . "'>" . htmlspecialchars($itemAnio['tra_anio']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay años disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fase_def" class="form-label">Seleccione la Fase:</label>
                                <select class="form-select form-select-sm" name="fase_def" id="fase_def">
                                    <option value="">-- Todas las Fases --</option> <?php
                                    if (!empty($listaFases)) {
                                        foreach ($listaFases as $itemFase) {
                                            echo "<option value='" . htmlspecialchars($itemFase['hor_fase']) . "'>Fase " . htmlspecialchars($itemFase['hor_fase']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay fases disponibles</option>";
                                    }
                                    ?>
                                </select>
                                 <div class="form-text">Seleccione Año. Si no selecciona Fase, se mostrarán todas las fases disponibles.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_definitivo_emit_btn" name="generar_definitivo_emit">
                                    <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); // Footer component ?>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="public/js/rdefinitivo.js"></script>
</body>
</html>