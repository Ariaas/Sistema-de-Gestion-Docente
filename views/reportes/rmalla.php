<?php 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Malla Curricular</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Malla Curricular</h2>
                    <p class="text-muted">Este reporte genera un documento PDF con el detalle de una malla curricular específica.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteMalla" target="_blank">
                        <div class="row justify-content-center">
                            <div class="col-md-10 col-lg-8">
                                <div class="mb-3">
                                    <label for="malla_id" class="form-label fw-bold">Seleccione la Malla Curricular:</label>
                                    <select class="form-select" id="malla_id" name="malla_id" required>
                                        <option value="" selected disabled>-- Elegir una opción --</option>
                                        <?php if (isset($listaMallas) && !empty($listaMallas)): ?>
                                            <?php foreach ($listaMallas as $malla): ?>
                                                <option value="<?php echo htmlspecialchars($malla['mal_id']); ?>">
                                                    <?php echo htmlspecialchars($malla['mal_nombre'] . ' - Cohorte ' . $malla['mal_cohorte']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No hay mallas curriculares activas</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center mt-3">
                                <p>Presione el botón para generar el reporte de la malla seleccionada.</p>
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_rmalla_btn" name="generar_rmalla_report">
                                    <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>

    <script type="text/javascript" src="public/js/reportes/rmalla.js"></script> 
</body>
</html>