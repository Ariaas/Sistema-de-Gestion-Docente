<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />

    <title>Reporte de Unidades Curriculares</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 800px;"> 
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Unidades Curriculares</h2>
                <p class="text-muted">Filtre por trayecto y/o unidad curricular para generar el reporte.</p>
            </div>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-warning text-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteUc">
                    <div class="row g-3 mb-4 justify-content-center">
                        <div class="col-md-5">
                            <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                            <select class="form-select form-select-sm" name="trayecto" id="trayecto">
                                <option value="">Todos los Trayectos</option>
                                <?php
                                if (!empty($trayectos)) { 
                                    foreach ($trayectos as $trayecto) {
                                        echo "<option value='" . htmlspecialchars($trayecto['tra_id']) . "'>"
                                           . htmlspecialchars($trayecto['tra_numero'])
                                           . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="ucurricular" class="form-label">Filtrar por Unidad Curricular:</label>
                            <select class="form-select form-select-sm" name="ucurricular" id="ucurricular">
                                <option value="">Todas las Unidades</option>
                                <?php
                                if (!empty($unidadesc)) { 
                                    foreach ($unidadesc as $uc) {
                                        echo "<option value='" . htmlspecialchars($uc['uc_id']) . "'>"
                                           . htmlspecialchars($uc['uc_nombre'])
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
                            <button type="submit" class="btn btn-success btn-lg px-5" name="generar_uc">
                                <i class="fas fa-file-excel me-2"></i>Generar Reporte Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            try {
                // Aplicar Select2 al dropdown de Trayecto
                $('#trayecto').select2({
                    theme: "bootstrap-5"
                });
                
                // Aplicar Select2 al dropdown de Unidad Curricular
                $('#ucurricular').select2({
                    theme: "bootstrap-5"
                });
            } catch (e) {
                // Este mensaje aparecerá en la consola del navegador si hay un error
                console.error("Error al inicializar Select2. Verifica que jQuery esté cargado y que las rutas a los archivos sean correctas.", e);
            }
        });
    </script>
</body>
</html>