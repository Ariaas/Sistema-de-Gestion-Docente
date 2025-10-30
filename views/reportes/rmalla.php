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
    <?php require_once("public/components/head.php");  ?>
    
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="public/css/reportes.css" />

    <title>Reporte de Malla Curricular</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 950px;"> 
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Malla Curricular</h2>
                <p class="text-muted">Este reporte genera un documento PDF con el detalle de una malla curricular específica.</p>
            </div>

            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteMalla" target="_blank">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="malla_codigo" class="form-label">Seleccione la Malla Curricular:</label>
                            <select class="form-select form-select-sm" id="malla_codigo" name="malla_codigo" required>
                                <option value="" selected>-- Seleccione una Malla --</option>
                                <?php if (isset($listaMallas) && !empty($listaMallas)): ?>
                                    <?php foreach ($listaMallas as $malla): ?>
                                        <option value="<?= htmlspecialchars($malla['mal_codigo']) ?>">
                                            <?= htmlspecialchars($malla['mal_nombre'] . ' - Cohorte ' . $malla['mal_cohorte']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay mallas curriculares activas</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="generar_rmalla_btn" name="generar_rmalla_report">
                                <i class="fas fa-file-pdf me-2"></i>Generar Reporte PDF
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
    <script type="text/javascript" src="public/js/rmalla.js"></script> 
</body>
</html>