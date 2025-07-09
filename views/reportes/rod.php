<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php");  ?>
    <title>Reporte de Organización Docente</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php");  ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Organización Docente (ROD)</h2>
                    <p class="lead">Seleccione la fase y año para generar el cuadro resumen.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" target="_blank">
                        <div class="row g-3 justify-content-center mb-4">
                            <div class="col-md-8">
                                <label for="fase_id" class="form-label">Filtrar por Fase y Año:</label>
                                <select class="form-select" name="fase_id" id="fase_id" required>
                                    <option value="" disabled selected>-- Seleccione una Fase --</option>
                                    <?php if (!empty($listaFases)): ?>
                                        <?php foreach ($listaFases as $fase):
                                            // Se crea un valor combinado ej: "1-2022"
                                            $valor = $fase['fase_numero'] . '-' . $fase['ani_anio'];
                                            // Se crea el texto para mostrar ej: "Fase 1 - Año 2022"
                                            $texto = 'Fase ' . $fase['fase_numero'] . ' - Año ' . $fase['ani_anio'];
                                        ?>
                                            <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($texto) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_reporte_rod" id="generar_reporte_rod_btn">
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
</body>
<script src="public/js/rod.js"></script>

</html>