<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />

    <title>Reporte de Organización Docente</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Organización Docente (ROD)</h2>
                    <p class="lead">Seleccione el año y la fase para generar el cuadro resumen.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteRod">
                        <div class="row g-3 justify-content-center mb-4">

                            <div class="col-md-6">
                                <label for="anio_id" class="form-label">Año Académico:</label>
                                <select class="form-select" name="anio_id" id="anio_id" required>
                                    <option value="" disabled selected>-- Seleccione un Año --</option>
                                    <?php if (!empty($listaAnios)): ?>
                                        <?php foreach ($listaAnios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_anio']) ?>"><?= htmlspecialchars($anio['ani_anio']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="fase_id" class="form-label">Fase:</label>
                                <select class="form-select" name="fase_id" id="fase_id" required>
                                    <option value="" disabled selected>-- Seleccione una Fase --</option>
                                    <option value="1">Fase I</option>
                                    <option value="2">Fase II</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_reporte_rod" id="generar_reporte_rod_btn">
                                    <i class="fas fa-file-excel me-2"></i>Generar Reporte EXCEL
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
    <?php require_once("public/components/footer.php"); ?>
    
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script src="public/js/rod.js"></script>
</body>
</html>