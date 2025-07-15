<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Horario por Sección</title>
    <style>
        .form-label { font-weight: 500; }
        .required-mark { color: red; margin-left: 2px; }
    </style>
    </head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;"> 
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Horario por Sección</h2>
                    <p class="text-muted">Seleccione una sección para ver su horario semanal.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteSeccion" target="_blank">
                        <div class="row g-3 mb-4 justify-content-center">
                            <div class="col-md-8">
                                <label for="seccion_id" class="form-label">Seleccione la Sección:<span class="required-mark">*</span></label>
                                <select class="form-select form-select-sm" name="seccion_id" id="seccion_id" required>
                                    <option value="">-- Seleccione una Sección --</option>
                                    <?php
                                    if (!empty($listaSecciones)) { 
                                        foreach ($listaSecciones as $seccion) {
                                            echo "<option value='" . htmlspecialchars($seccion['sec_codigo']) . "'>"
                                               . htmlspecialchars($seccion['sec_codigo'])
                                               . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay secciones disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" id="generar_seccion_btn" name="generar_seccion_report">
                                    <i class="fas fa-file-excel me-2"></i>Generar Horario Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>

    <script type="text/javascript" src="public/js/rseccion.js"></script>
</body>
</html>