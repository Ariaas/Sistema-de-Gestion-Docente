
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

if (!function_exists('tiene_permiso_accion')) {
    function tiene_permiso_accion($modulo, $accion, $permisos_array)
    {
        $modulo = strtolower($modulo);
        if (isset($permisos_array[$modulo]) && is_array($permisos_array[$modulo])) {
            return in_array($accion, $permisos_array[$modulo]);
        }
        return false;
    }
}

$puede_registrar = tiene_permiso_accion('reportes', 'registrar', $permisos);

if (!$puede_registrar) {
    header('Location: ?pagina=principal');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Reporte Estadístico - Aprobados Totales</title>
</head>

<body class="d-flex flex-column min-vh-100" data-hay-datos="<?php echo $hayDatos ? 'true' : 'false'; ?>">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4 px-3">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Estudiantes Aprobados Totales</h2>

            <div class="card w-100 shadow-sm" style="max-width: 900px;">
                <div class="card-body">
                    <form id="formReporte">
                        <div class="row align-items-end">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="anio_reporte" class="form-label fw-bold">Año Académico</label>
                                <select class="form-select" id="anio_reporte" name="anio_completo" required>
                                    <option value="" selected disabled>Seleccionar...</option>
                                    <?php foreach ($anios as $anio) {
                                        $texto = "{$anio['ani_anio']} - Periodo {$anio['ani_tipo']}";
                                        echo "<option value='{$anio['anio_completo']}'>{$texto}</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="tipo_reporte" class="form-label fw-bold">Tipo de Reporte</label>
                                <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                    <option value="general" selected>General por Año</option>
                                    <option value="seccion">Por Sección</option>
                                    <option value="uc">Por Unidad Curricular</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3" id="filtro_seccion_container" style="display: none;">
                                <label for="seccion_codigo" class="form-label fw-bold">Sección</label>
                                <select class="form-select" id="seccion_codigo" name="seccion_codigo" disabled>
                                    <option>Seleccione un año</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3" id="filtro_uc_container" style="display: none;">
                                <label for="uc_codigo" class="form-label fw-bold">Unidad Curricular</label>
                                <select class="form-select" id="uc_codigo" name="uc_codigo" disabled>
                                    <option>Seleccione un año</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <button type="submit" class="btn btn-primary w-100">Generar Reporte</button>
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
    <script type="text/javascript" src="public/js/rAprobados.js"></script>
    <script src="public/package/dist/chart.umd.js"></script>
</body>

</html>