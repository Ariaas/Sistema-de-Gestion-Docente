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

$puede_registrar = tiene_permiso_accion('turno', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('turno', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('turno', 'eliminar', $permisos);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestionar Turno</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Turno</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Turno</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaturno">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Hora de inicio</th>
                                <th>Hora de fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Turno</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" novalidate>
                            <input type="hidden" id="turnoid" name="turnoid">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="turnonombre" class="form-label">Nombre del Turno</label>
                                        <select class="form-select form-control" id="turnonombre" name="turnonombre" required>
                                            <option value="" disabled selected>Seleccione un turno</option>
                                            <option value="Mañana">Mañana</option>
                                            <option value="Tarde">Tarde</option>
                                            <option value="Noche">Noche</option>
                                        </select>
                                        <span class="text-danger" id="sturnonombre"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="horaInicio" class="form-label">Hora de inicio</label>
                                        <input class="form-control" type="time" id="horaInicio" name="horaInicio" required>
                                        <span class="text-danger" id="shoraInicio"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="horafin" class="form-label">Hora de fin</label>
                                        <input class="form-control" type="time" id="horafin" name="horafin" required>
                                        <span class="text-danger" id="shorafin"></span>
                                    </div>
                                    <div class="col-12 text-center mt-3">
                                        <span class="text-danger fw-bold" id="sSolapamiento"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso" disabled>REGISTRAR</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalEliminar">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="turnoid_eliminar">
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label">Nombre</label><input class="form-control" type="text" id="turnonombre_eliminar" disabled></div>
                            <div class="col-6"><label class="form-label">Inicio</label><input class="form-control" type="text" id="horaInicio_eliminar" disabled></div>
                            <div class="col-6"><label class="form-label">Fin</label><input class="form-control" type="text" id="horafin_eliminar" disabled></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
    <script type="text/javascript" src="public/js/turno.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>