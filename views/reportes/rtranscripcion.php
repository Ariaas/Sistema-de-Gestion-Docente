<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Transcripcion</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php");  ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Transcripcion</h2>
                    <p class="lead">Este reporte genera un archivo Excel con la asignación completa de todas las unidades curriculares a los docentes y sus secciones.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" target="_blank">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_transcripcion">
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
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>