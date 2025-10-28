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
    <title>Reporte de Cuenta de Cupos</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Cuenta de Cupos </h2>
                    <p class="lead">Seleccione el año académico para generar el reporte de inscritos.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteCuentaCupos" target="_blank">
                        <div class="row g-3 justify-content-center mb-4">
                            <div class="col-md-8 col-lg-7">
                                <label for="anio" class="form-label"><strong>Filtrar por Año Académico:</strong></label>
                                <select class="form-select" name="anio" id="anio" required>
                                    <option value="" disabled selected>-- Seleccione un Año --</option>
                                    <?php if (!empty($anios)): ?>
                                        <?php foreach ($anios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_anio']) ?>"><?= htmlspecialchars($anio['ani_anio']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_reporte" id="generar_reporte_btn">
                                    <i class="fas fa-file-excel me-2"></i>Generar Reporte Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script src="public/js/rcuentacupos.js"></script>
</body>

</html>