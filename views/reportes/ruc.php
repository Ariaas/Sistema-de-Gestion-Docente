<?php

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); 
    ?>
    <title>Reportes Unidades Curriculares</title>

</head>

<body>
    <?php require_once("public/components/sidebar.php"); 
    ?>

    <main class="main-content flex-shrink-0" style="padding-top: 20px;">
        <div class="container" style="width: 80%; max-width: 850px;">
            <section class="py-4">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reportes de Unidades Curriculares</h2>
                </div>

                <div class="card p-4 shadow-sm bg-white rounded">
                    <form method="post" action="" id="fReporteUnidadCurricular" target="_blank">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-6"> 
                                <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                                <select class="form-select" name="trayecto" id="trayecto">
                                    <option value="">Seleccione un trayecto</option>
                                    <?php
                                    
                                    if (!empty($trayectos)) {
                                        foreach ($trayectos as $Trayecto) { 
                                            echo "<option value='" . htmlspecialchars($Trayecto['tra_id']) . "'>" . htmlspecialchars($Trayecto['tra_numero']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay trayectos</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6"> 
                                <label for="ucurricular" class="form-label">Filtrar por Unidad curricular:</label>
                                <select class="form-select" name="ucurricular" id="ucurricular">
                                    <option value="">Seleccione una unidad curricular</option>
                                    <?php
                                    
                                    if (!empty($unidadesc)) {
                                        foreach ($unidadesc as $unidadc) { 
                                            echo "<option value='" . htmlspecialchars($unidadc['uc_id']) . "'>" . htmlspecialchars($unidadc['uc_nombre']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay unidades</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md text-center">
                                <button type="submit" class="btn btn-primary btn-lg" id="generar_uc" name="generar_uc">
                                    <i class="fas fa-file-pdf me-2"></i>Crear Reporte PDF
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
    <script type="text/javascript" src="public/js/ruc.js"></script>
</body>

</html>