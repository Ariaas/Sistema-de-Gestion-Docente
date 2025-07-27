<?php
// ...
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    <title>Reportes de Carga Académica</title>
    
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reportes de Carga Académica por Sección</h2>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteUnidadCurricular">
                        <div class="row g-3 mb-4 align-items-center">

                            <div class="col-md-4">
                                <label for="anio_id" class="form-label">Filtrar por Año:</label>
                                <select class="form-select" name="anio_id" id="anio_id">
                                    <option value="" selected>-- Seleccione un Año --</option>
                                    <?php if (!empty($listaAnios)): ?>
                                        <?php foreach ($listaAnios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_anio']) ?>"><?= htmlspecialchars($anio['ani_anio']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                                <select class="form-select" name="trayecto" id="trayecto">
                                    <option value="">Todos los Trayectos</option>
                                    <?php if (!empty($trayectos)): ?>
                                        <?php foreach ($trayectos as $Trayecto): ?>
                                            <option value="<?= htmlspecialchars($Trayecto['tra_id']) ?>"><?= htmlspecialchars($Trayecto['tra_numero']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="seccion" class="form-label">Filtrar por Sección:</label>
                                <select class="form-select" name="seccion" id="seccion">
                                    <option value="">Todas las Secciones</option>
                                    <?php if (!empty($secciones)): ?>
                                        <?php foreach ($secciones as $Seccion): ?>
                                            <option value="<?= htmlspecialchars($Seccion['sec_codigo']) ?>"><?= htmlspecialchars($Seccion['sec_codigo']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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

    <?php require_once("public/components/footer.php"); ?>
      <script type="text/javascript" src="public/js/validacion.js"></script>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="public/js/rcargaAcademica.js"></script>
</body>

</html>