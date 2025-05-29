<?php
// views/reportes/rhordocenteview.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte de Horario por Docente</title>
    <style>
        .form-label { font-weight: 500; }
        .required-mark { color: red; margin-left: 2px; }
    </style>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Horario por Docente</h2>
                    <p class="text-muted">Seleccione un docente para ver su horario semanal.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                   
                    <form method="post" action="" id="fReporthorariodocente" target="_blank">
                        <div class="row g-3 mb-4 justify-content-center">
                            <div class="col-md-8">
                                <label for="docente_rhd" class="form-label">Seleccione el Docente:<span class="required-mark">*</span></label>
                                <select class="form-select form-select-sm" name="docente_rhd_name" id="docente_rhd_id" required> 
                                    <option value="">-- Seleccione un Docente --</option>
                                    <?php
                                    if (!empty($listaDocentes)) { 
                                        foreach ($listaDocentes as $docente) {
                                            echo "<option value='" . htmlspecialchars($docente['doc_id']) . "'>"
                                               . htmlspecialchars($docente['doc_apellido'] . ' ' . $docente['doc_nombre'] . ' (C.I: ' . $docente['doc_cedula']) . ")"
                                               . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay docentes disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_rhd_btn" name="generar_rhd_report"> 
                                    <i class="fas fa-file-pdf me-2"></i>Generar Horario PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
 
    <script type="text/javascript" src="public/js/rhordocente.js"></script>
</body>
</html>