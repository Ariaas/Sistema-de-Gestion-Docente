<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Aulario (Horario por Aula)</title>
    <style>
        .form-label { font-weight: 500; }
        .required-mark { color: red; margin-left: 2px; }
    </style>
    </head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;"> 
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Aulario</h2>
                    <p class="text-muted">Seleccione un aula para ver su horario semanal.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteAulario" target="_blank">
                        <div class="row g-3 mb-4 justify-content-center">
                            <div class="col-md-8">
                                <label for="espacio_aul" class="form-label">Seleccione el Aula (Espacio):<span class="required-mark">*</span></label>
                                <select class="form-select form-select-sm" name="espacio_aul" id="espacio_aul" required>
                                    <option value="">-- Seleccione un Aula --</option>
                                    <?php
                                    if (!empty($listaEspacios)) { 
                                        foreach ($listaEspacios as $espacio) {
                                            echo "<option value='" . htmlspecialchars($espacio['esp_id']) . "'>"
                                               . htmlspecialchars($espacio['esp_codigo']) . " (" . htmlspecialchars($espacio['esp_tipo']) . ")"
                                               . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No hay aulas disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_aulario_btn" name="generar_aulario_report">
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

    <script type="text/javascript" src="public/js/raulario.js"></script>
</body>
</html>