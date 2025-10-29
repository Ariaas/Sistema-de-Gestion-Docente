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


    <title>Reporte Definitivo EMITC</title>

    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte Definitivo EMITC</h2>
                    <p class="lead">Seleccione el año y la fase para generar el reporte</p>
                </div>
                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" id="fReporteDefinitivoEmit">
                        <div class="row g-3 mb-4 justify-content-center">

                            <div class="col-md-6">
                                <label for="anio_completo" class="form-label">Año Académico: <span class="text-danger">*</span></label>
                                <select class="form-select" name="anio_completo" id="anio_completo" required>
                                    <option value="" selected>-- Seleccione --</option>
                                    <?php if (!empty($listaAnios)): ?>
                                        <?php foreach ($listaAnios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_anio'] . '|' . $anio['ani_tipo']) ?>"><?= htmlspecialchars($anio['anio_completo']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6" id="fase_container">
                                <label for="fase" class="form-label">Filtrar por Fase:</label>
                                <select class="form-select" name="fase" id="fase">
                                    <option value="">-- Todas las Fases --</option>
                                    <option value="1">Fase I</option>
                                    <option value="2">Fase II</option>
                                </select>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_definitivo_emit" id="generar_btn">
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

    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="public/js/rdefinitivo.js"></script>
</body>

</html>