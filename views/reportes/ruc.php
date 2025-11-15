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
    function tiene_permiso_accion($modulo, $accion, $permisos_array){
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

    <title>Reporte de Unidades Curriculares</title>
</head>

<body>

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 950px;"> 
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Unidades Curriculares</h2>
                <p class="text-muted">Filtre por año, trayecto y/o unidad curricular para generar el reporte.</p>
            </div>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteUc" target="_blank">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <label for="anio_id" class="form-label">Filtrar por Año: <span style="color: red;">*</span></label>
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
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <label for="fase" class="form-label">Filtrar por Fase:</label>
                            <select class="form-select form-select-sm" name="fase" id="fase">
                                <option value="">Todas</option>
                                <option value="Fase I">Fase I</option>
                                <option value="Fase II">Fase II</option>
                                <option value="Anual">Anual</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <label for="trayecto" class="form-label">Filtrar por Trayecto:</label>
                            <select class="form-select form-select-sm" name="trayecto" id="trayecto">
                                <option value="">Todos los Trayectos</option>
                                <?php if (!empty($trayectos)): ?>
                                    <?php foreach ($trayectos as $trayecto): ?>
                                        <option value="<?= htmlspecialchars($trayecto['tra_id']) ?>">
                                            <?= htmlspecialchars($trayecto['tra_numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <label for="ucurricular" class="form-label">Filtrar por U.C.:</label>
                            <select class="form-select form-select-sm" name="ucurricular" id="ucurricular">
                                <option value="">Todas las Unidades</option>
                                <?php if (!empty($unidadesc)): ?>
                                    <?php foreach ($unidadesc as $uc): ?>
                                        <option value="<?= htmlspecialchars($uc['uc_id']) ?>">
                                            <?= htmlspecialchars($uc['uc_nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" name="generar_uc" id="generar_uc">
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
    <script src="public/js/ruc.js"></script>

</body>
</html>