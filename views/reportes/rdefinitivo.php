<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte Definitivo EMITC</title>
    <style>
        .form-label {
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php require_once("public/components/sidebar.php");  ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte Definitivo EMITC</h2>
                </div>
                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteDefinitivoEmit" target="_blank">
                        <div class="row g-3 mb-4 justify-content-center">
                            <div class="col-md-8">
                                <label for="docente_id" class="form-label">Filtrar por Docente:</label>
                                <select class="form-select" name="docente_id" id="docente_id">
                                    <option value="">-- Todos los Docentes --</option>
                                    <?php
                                    if (!empty($listaDocentes)) {
                                        foreach ($listaDocentes as $docente) {
                                            echo "<option value='" . htmlspecialchars($docente['doc_id']) . "'>" . htmlspecialchars($docente['NombreCompleto']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="form-text">Si no selecciona un docente, se mostrarán todos.</div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_definitivo_emit">
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
    <script type="text/javascript" src="public/js/rdefinitivo.js"></script>
</body>

</html>