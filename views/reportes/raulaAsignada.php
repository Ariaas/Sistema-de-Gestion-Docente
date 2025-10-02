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
    <title>Reporte de Asignación de Aulas</title>
    <style>
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 800px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte Detallado de Asignación de Aulas</h2>
                    <p class="text-muted">Seleccione un año académico para generar un reporte detallado en formato Excel.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteAsignacionAulas" target="_blank">
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-3">
                                <label for="ani_anio" class="form-label">Año Académico</label>
                                <select class="form-select" id="ani_anio" name="ani_anio" required>
                                    <option value="" disabled selected>-- Seleccione un año --</option>
                                    <?php if (!empty($anios)): ?>
                                        <?php foreach ($anios as $anio): ?>
                                            <option value="<?php echo htmlspecialchars($anio['ani_anio']); ?>">
                                                <?php echo htmlspecialchars($anio['ani_anio']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center mt-3">
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
</html