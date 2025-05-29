<?php
// views/reportes/ruc.php
// El manejo de sesión (comentado) es importante, pero puede hacerse globalmente.
// if (session_status() == PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['name'])) { header('Location: index.php?pagina=login'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); // Incluye tus metas, viewport, y CSS globales 
    ?>
    <title>Reportes Unidades Curriculares por Sección</title>
    <style>
        

        .form-label {
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); // Tu menú lateral 
    ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reportes de Unidades Curriculares por Sección</h2>
                  
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteUnidadCurricular" target="_blank">
                        <div class="row g-3 mb-4 align-items-center">
                            <div class="col-md-6 col-lg-5">
                                <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                                <select class="form-select form-select-sm" name="trayecto" id="trayecto">
                                    <option value="">-- Todos los Trayectos --</option>
                                    <?php
                                    if (!empty($trayectos)) { // $trayectos viene del controlador
                                        foreach ($trayectos as $itemTrayecto) {
                                            echo "<option value='" . htmlspecialchars($itemTrayecto['tra_id']) . "'>" . htmlspecialchars($itemTrayecto['tra_numero']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay trayectos disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-7">
                                <label for="seccion" class="form-label">Filtrar por Sección:</label>
                                <select class="form-select form-select-sm" name="seccion" id="seccion">
                                    <option value="">Seleccione una seccion</option>
                                    <?php
                                    // $secciones será pasada desde el controlador
                                    if (!empty($secciones)) {
                                        foreach ($secciones as $itemSeccion) {
                                            // Asumimos que obtenerSecciones() devuelve 'sec_id' y 'sec_codigo'
                                            echo "<option value='" . htmlspecialchars($itemSeccion['sec_id']) . "'>" . htmlspecialchars($itemSeccion['sec_codigo']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay secciones disponibles</option>";
                                    }
                                    ?>
                                </select>
                                <div class="form-text">Deje vacío para no filtrar por sección.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_uc" name="generar_uc">
                                    <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); // Tu pie de página 
    ?>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="public/js/rcargaAcademica.js"></script> {/* Asegúrate que este archivo JS exista */}
</body>

</html>