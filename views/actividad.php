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

$puede_registrar = tiene_permiso_accion('area', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('area', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('area', 'eliminar', $permisos);
?>


<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Actividades</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0" data-total-docentes="<?= $totalDocentes ?>">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Actividades</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Actividad</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaactividad">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Docente</th>
                                <th>Creación Intelectual (h)</th>
                                <th>Integración Comunidad (h)</th>
                                <th>Gestión Académica (h)</th>
                                <th>Otras Actividades (h)</th>
                                <th>Horas Totales</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta">
                           </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header text-white">
                        <h5 class="modal-title">Formulario de Actividades</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <input type="hidden" id="actId" name="actId">

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="docId" class="form-label">Docente</label>
                                    <select class="form-control" id="docId" name="docId" required>
                                        <option value="">Seleccione un docente</option>
                                    </select>
                                    <span id="sdocId" class="text-danger fw-bold"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="actCreacion" class="form-label">Horas de Creación Intelectual</label>
                                    <input class="form-control horas-actividad" type="number" id="actCreacion" name="actCreacion" required min="0" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="actIntegracion" class="form-label">Horas de Integración a la Comunidad</label>
                                    <input class="form-control horas-actividad" type="number" id="actIntegracion" name="actIntegracion" required min="0" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="actGestion" class="form-label">Horas de Gestión Académica</label>
                                    <input class="form-control horas-actividad" type="number" id="actGestion" name="actGestion" required min="0" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="actOtras" class="form-label">Otras Horas de Actividad</label>
                                    <input class="form-control horas-actividad" type="number" id="actOtras" name="actOtras" required min="0" value="0">
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <span id="sHorasTotales" class="text-danger fw-bold"></span>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center mt-4">
                                <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
     <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/actividad.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>