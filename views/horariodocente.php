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

$puede_registrar = tiene_permiso_accion('horario docente', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('horario docente', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('horario docente', 'eliminar', $permisos);

if (!$puede_registrar && !$puede_modificar && !$puede_eliminar) {
    header('Location: ?pagina=principal');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Actividades Docentes</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Horario Docente</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Actividad</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="tablaHorarioDocente">
                        <thead>
                            <tr>
                                <th style="display: none;">Cédula</th>
                                <th>Docente</th>
                                <th>Lapso</th>
                                <th>Actividad</th>
                                <th>Descripción</th>
                                <th>Dependencia</th>
                                <th>Horas</th>
                                <th style="display: none;">Observación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Actividad Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off">
                            <input type="hidden" name="accion" id="accion">
                            <input type="hidden" id="original_cedula" name="original_cedula">
                            <input type="hidden" id="original_lapso" name="original_lapso">
                            <input type="hidden" id="original_actividad" name="original_actividad">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="docente" class="form-label">Docente</label>
                                    <select class="form-select" id="docente" name="docente"></select>
                                    <span id="sdocente"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="lapso" class="form-label">Lapso</label>
                                    <select class="form-select" id="lapso" name="lapso"></select>
                                    <span id="slapso"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="actividad" class="form-label">Tipo de Actividad</label>
                                    <input class="form-control" type="text" id="actividad" name="actividad">
                                    <span id="sactividad"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <input class="form-control" type="text" id="descripcion" name="descripcion">
                                    <span id="sdescripcion"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="dependencia" class="form-label">Dependencia</label>
                                    <input class="form-control" type="text" id="dependencia" name="dependencia">
                                    <span id="sdependencia"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="observacion" class="form-label">Observación</label>
                                    <input class="form-control" type="text" id="observacion" name="observacion">
                                </div>
                                <div class="col-md-4">
                                    <label for="horas" class="form-label">Horas</label>
                                    <input class="form-control" type="number" id="horas" name="horas" min="1">
                                    <span id="shoras"></span>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center mt-3">
                                <button type="button" class="btn btn-primary me-2" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerHorario">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Horario de Clases del Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 id="nombreDocenteHorario" class="text-center mb-3"></h4>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center" id="tablaVerHorario">
                                <thead>
                                    <tr class="table-light">
                                        <th>Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                        <th>Sábado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button></div>
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
    <script src="public/js/horariodocente.js"></script>
    <script src="public/js/validacion.js"></script>
</body>

</html>