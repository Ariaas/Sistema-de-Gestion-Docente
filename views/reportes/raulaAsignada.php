<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Asignación de Aulas</title>
    <style>
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Asignación de Aulas</h2>
                    <p class="text-muted">Este reporte muestra las aulas asignadas para cada día de la semana.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteAsignacionAulas" target="_blank">
                        <div class="row">
                            <div class="col-12 text-center">
                                <p>Presione el botón para generar el reporte en formato Excel.</p>
                                <button type="submit" class="btn btn-success btn-lg px-5" id="generar_asignacion_aulas_btn" name="generar_asignacion_aulas_report">
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

    <script type="text/javascript" src="public/js/raulaAsignada.js"></script> 
    
</body>
</html>