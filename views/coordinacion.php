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

$puede_registrar = tiene_permiso_accion('coordinacion', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('coordinacion', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('coordinacion', 'eliminar', $permisos);

if (!$puede_registrar && !$puede_modificar && !$puede_eliminar) {
    header('Location: ?pagina=principal');
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Coordinaciones</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Coordinaciones</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Coordinación</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablacoordinacion">
                        <thead>
                            <tr>
                                <th>Coordinación</th>
                                <th>Hora de Descarga</th>
                                <th style="width: 20%;">Acciones</th>
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
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Coordinación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="coordinacionOriginalNombre" id="coordinacionOriginalNombre">

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="coordinacionNombre" class="form-label">Nombre de la Coordinación</label>
                                    <input class="form-control" type="text" id="coordinacionNombre" name="coordinacionNombre" required placeholder="Ej: Proyecto">
                                    <span id="scoordinacionNombre" class="text-danger"></span>
                                </div>
                                <div class="col-12">
                                    <label for="coordinacionHoraDescarga" class="form-label">Hora de Descarga</label>
                                    <input class="form-control" type="number" id="coordinacionHoraDescarga" name="coordinacionHoraDescarga" min="1" max="24" placeholder="Ej: 8">
                                    <span id="scoordinacionHoraDescarga" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center mt-4">
                                <button type="submit" class="btn btn-primary px-4 me-2" id="proceso">REGISTRAR</button>
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">CANCELAR</button>
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
    <script type="text/javascript" src="public/js/coordinacion.js"></script>
</body>

</html>