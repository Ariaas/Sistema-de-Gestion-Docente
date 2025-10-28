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
    <title>Reporte - Horario Docente</title>
    <style>
        .form-label {
            font-weight: 500;
        }

        .required-mark {
            color: red;
            margin-left: 2px;
        }
    </style>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 900px;">
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte de Horario del Personal Docente</h2>
                <p class="text-muted">Seleccione los criterios para generar el horario detallado en formato Excel.</p>
            </div>
            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" id="fReporteHorDocente" target="_blank">
                    <div class="row g-3 justify-content-center mb-4">

                        <div class="col-md-4">
                            <label for="anio_id" class="form-label">Año Académico<span class="required-mark">*</span></label>
                            <select class="form-select form-select-sm" name="anio_id" id="anio_id" required>
                                <option value="">-- Seleccione --</option>
                                <?php if (!empty($listaAnios)):
                                    foreach ($listaAnios as $anio): ?>
                                        <option value="<?= htmlspecialchars($anio['ani_anio']) ?>"><?= htmlspecialchars($anio['ani_anio'] . ' (' . $anio['ani_tipo'] . ')') ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="fase_id" class="form-label">Fase<span class="required-mark">*</span></label>
                            <select class="form-select form-select-sm" name="fase_id" id="fase_id" required>
                                <option value="">-- Seleccione --</option>
                                <?php if (!empty($listaFases)):
                                    foreach ($listaFases as $fase): ?>
                                        <option value="<?= htmlspecialchars($fase['fase_numero']) ?>">Fase <?= htmlspecialchars($fase['fase_numero']) ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="cedula_docente" class="form-label">Docente<span class="required-mark">*</span></label>
                            <select class="form-select" name="cedula_docente" id="cedula_docente" required>
                                <option value="">-- Seleccione --</option>
                                <?php if (!empty($listaDocentes)):
                                    foreach ($listaDocentes as $docente): ?>
                                        <option value="<?= htmlspecialchars($docente['doc_cedula']) ?>"><?= htmlspecialchars($docente['nombreCompleto']) . ' (C.I: ' . htmlspecialchars($docente['doc_cedula']) . ')' ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" name="generar_rhd_report">
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
    <script src="public/js/rhordocente.js"></script>
</body>

</html>