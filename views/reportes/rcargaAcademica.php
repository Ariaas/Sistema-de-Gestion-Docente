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
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="public/css/reportes.css" />

    <title>Reporte de Carga Académica</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 950px;"> 
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Carga Académica</h2>
                <p class="text-muted">Seleccione los criterios para generar el reporte de carga académica.</p>
            </div>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteUnidadCurricular" target="_blank">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="anio_completo" class="form-label">Año Académico:</label>
                            <select class="form-select form-select-sm" name="anio_completo" id="anio_completo" required>
                                <option value="" selected>-- Seleccione un Año --</option>
                                <?php if (!empty($listaAnios)): ?>
                                    <?php foreach ($listaAnios as $anio): ?>
                                        <option value="<?= htmlspecialchars($anio['ani_anio'] . '|' . $anio['ani_tipo']) ?>">
                                            <?= htmlspecialchars($anio['anio_completo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4" id="fase_container">
                            <label for="fase_id" class="form-label">Fase:</label>
                            <select class="form-select form-select-sm" name="fase_id" id="fase_id" required>
                                <option value="" selected>-- Seleccione una Fase --</option>
                                <?php if (!empty($listaFases)): ?>
                                    <?php foreach ($listaFases as $fase): ?>
                                        <option value="<?= htmlspecialchars($fase['fase_numero']) ?>">
                                            Fase <?= htmlspecialchars($fase['fase_numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="trayecto" class="form-label">Trayecto:</label>
                            <select class="form-select form-select-sm" name="trayecto" id="trayecto">
                                <option value="">Todos los Trayectos</option>
                                <?php if (!empty($trayectos)): ?>
                                    <?php foreach ($trayectos as $Trayecto): ?>
                                        <option value="<?= htmlspecialchars($Trayecto['tra_id']) ?>">
                                            <?= htmlspecialchars($Trayecto['tra_numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="generar_uc" name="generar_uc">
                                <i class="fas fa-file-excel me-2"></i>Generar Reporte Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script type="text/javascript" src="public/js/rcargaAcademica.js"></script>
</body>

</html>