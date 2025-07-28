<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte de Prosecución Académica</title>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4">Reporte de Prosecución Académica</h2>

            <div class="card p-4 shadow" style="max-width: 600px; width: 100%;">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="anio_academico" class="form-label"><strong>Año Académico:</strong></label>
                        <select class="form-select" id="anio_academico" name="anio_academico" required>
                            <option value="">-- Seleccione un año --</option>
                            <?php if (!empty($aniosAcademicos)): ?>
                                <?php foreach ($aniosAcademicos as $anio): ?>
                                    <option value="<?php echo htmlspecialchars($anio['ani_anio']); ?>">
                                        <?php echo htmlspecialchars($anio['ani_anio']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay años disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="submit" name="generar_reporte_prosecucion" class="btn btn-success btn-lg px-5">
                             <i class="fas fa-file-excel me-2"></i>Generar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>
</body>
</html>