<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Transcripción</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php");  ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Transcripción de Asignaciones</h2>
                    <p class="lead">Seleccione los filtros para generar el reporte de asignación de U.C. a docentes y secciones.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" target="_blank">
                        <div class="row g-3 justify-content-center mb-4">
                            <div class="col-md-6">
                                <label for="anio_id" class="form-label">Filtrar por Año Académico:</label>
                                <select class="form-select" name="anio_id" id="anio_id" required>
                                    <option value="" disabled selected>-- Seleccione un Año --</option>
                                    <?php if (!empty($listaAnios)): ?>
                                        <?php foreach ($listaAnios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_id']) ?>"><?= htmlspecialchars($anio['ani_anio']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fase" class="form-label">Filtrar por Fase (Opcional):</label>
                                <select class="form-select" name="fase" id="fase">
                                    <option value="">-- Todas las Fases --</option>
                                    <option value="1">Fase I</option>
                                    <option value="2">Fase II</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_transcripcion" id="generar_transcripcion_btn">
                                    <i class="fas fa-file-excel me-2"></i>Generar Reporte EXCEL
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
    <?php require_once("public/components/footer.php");  ?>
</body>
<script src="public/js/rtranscripcion.js"></script>

</html>