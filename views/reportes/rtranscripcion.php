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
    <title>Reporte de Transcripción</title>
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />

</head>

<body>
    <?php require_once("public/components/sidebar.php");  ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <section class="py-3">
                <div class="text-center mb-4">
                    <h2 class="text-primary">Reporte de Transcripción de Asignaciones</h2>
                    <p class="lead">Seleccione los filtros para generar el reporte de asignación de U.C. a docentes y secciones.</p>
                </div>

                <div class="card p-4 shadow-sm bg-light rounded">
                    <form method="post" action="" target="_blank" id="fReporteTranscripcion">
                        <div class="row g-3 justify-content-center mb-4">
                            <div class="col-md-6">
                                <label for="anio_completo" class="form-label">Año Académico<span style="color:red;">*</span></label>
                                <select class="form-select form-select-sm" name="anio_completo" id="anio_completo" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php if (!empty($listaAnios)): ?>
                                        <?php foreach ($listaAnios as $anio): ?>
                                            <option value="<?= htmlspecialchars($anio['ani_anio'] . '|' . $anio['ani_tipo']) ?>"><?= htmlspecialchars($anio['anio_completo']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="fase_container">
                                <label for="fase_id" class="form-label">Fase<span style="color:red;">*</span></label>
                                <select class="form-select form-select-sm" name="fase_id" id="fase_id" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php if (!empty($listaFases)): ?>
                                        <?php foreach ($listaFases as $fase): ?>
                                            <option value="<?= htmlspecialchars($fase['fase_numero']) ?>">Fase <?= htmlspecialchars($fase['fase_numero']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5" name="generar_transcripcion" id="generar_transcripcion_btn">
                                    <i class="fas fa-file-excel me-2"></i>Generar Reporte Excel
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
<script type="text/javascript" src="public/js/validacion.js"></script>
<script src="vendor/select2/select2/dist/js/select2.min.js"></script>
<script src="public/js/rtranscripcion.js"></script>

</html>