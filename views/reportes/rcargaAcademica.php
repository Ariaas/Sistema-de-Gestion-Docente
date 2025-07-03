<?php

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");
    ?>
    <title>Reportes de Carga Académica</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php");
    ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reportes de Carga Académica por Sección</h2>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteUnidadCurricular" target="_blank">
                        <div class="row g-3 mb-4 align-items-center">
                            <div class="col-md-6 col-lg-5">
                                <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                                <select class="form-select" name="trayecto" id="trayecto">
                                    <option value="">Selecione un Trayecto</option>
                                    <?php
                                    if (!empty($trayectos)) {
                                        foreach ($trayectos as $Trayecto) {
                                            echo "<option value='" . htmlspecialchars($Trayecto['tra_id']) . "'>" . htmlspecialchars($Trayecto['tra_numero']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-7">
                                <label for="seccion" class="form-label">Filtrar por Sección:</label>
                                <select class="form-select" name="seccion" id="seccion">
                                    <option value="">Selecione una Seccion</option>
                                    <?php
                                    if (!empty($secciones)) {
                                        foreach ($secciones as $Seccion) {
                                            echo "<option value='" . htmlspecialchars($Seccion['sec_id']) . "'>" . htmlspecialchars($Seccion['sec_codigo']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" id="generar_uc" name="generar_uc">
                                    <i class="fas fa-file-excel me-2"></i>Generar Reporte EXCEL
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php");
    ?>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="public/js/rcargaAcademica.js"></script>
</body>

</html>