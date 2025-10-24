<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

$permisos_sesion = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
$permisos = array_change_key_case($permisos_sesion, CASE_LOWER);

$puede_registrar = isset($permisos['reportes']) && in_array('registrar', $permisos['reportes']);

if (!$puede_registrar) {
    header('Location: ?pagina=principal');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte de Prosecución Estudiantil</title>
</head>

<body class="d-flex flex-column min-vh-100" data-hay-datos="<?php echo $hayDatos ? 'true' : 'false'; ?>">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4 px-3">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Reporte de Prosecución Estudiantil</h2>

            <div class="card w-100 shadow-sm" style="max-width: 900px;">
                <div class="card-body">
                    <form id="formReporte">
                        <div class="row align-items-end">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="anio_reporte" class="form-label fw-bold">Año Académico (Origen)</label>
                                <select class="form-select" id="anio_reporte" name="anio_origen" required>
                                    <?php if (!empty($anios_disponibles)) {
                                        
                                        foreach ($anios_disponibles as $index => $anio) {
                                            $texto = $anio['ani_origen'];
                                            
                                            $selected = ($index == 0) ? 'selected' : '';
                                            echo "<option value='{$texto}' {$selected}>{$texto}</option>";
                                        }
                                    } else {
                                        echo "<option value='' selected disabled>No hay años con datos</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="tipo_reporte" class="form-label fw-bold">Tipo de Reporte</label>
                                <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                    <option value="general" selected>General por Año</option>
                                    <option value="seccion">Por Sección Origen</option>
                                    <option value="trayecto">Por Trayecto Origen
                    </form>
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <button type="submit" class="btn btn-primary w-100" <?php echo (empty($anios_disponibles)) ? 'disabled' : ''; ?>>Generar Reporte</button>
                </div>
            </div>
            </form>
            </div>
            </div>

            <div class="card w-100 mt-4 shadow-sm" style="max-width: 900px;">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    Gráfico Estadístico
                    <div class="col-lg-3 col-md-4">
                        <select class="form-select form-select-sm" id="tipo_grafico">
                            <option value="bar" selected>Gráfico de Barras</option>
                            <option value="pie">Gráfico de Torta</option>
                            <option value="doughnut">Gráfico de Anillo</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; height:45vh; width:100%">
                        <canvas id="reporteChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script type="text/javascript" src="public/js/reporteP.js"></script>
    <script src="public/package/dist/chart.umd.js"></script>
</body>

</html>