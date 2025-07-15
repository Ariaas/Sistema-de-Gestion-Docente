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

$puede_registrar = tiene_permiso_accion('espacio', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('espacio', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('espacio', 'eliminar', $permisos);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>

    <title>Espacios</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Espacios</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Espacio</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaespacio">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Edificio</th>
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
                        <h5 class="modal-title">Formulario de Espacios</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion" value="modificar">

                            <div class="container">
                                <div class="row mb-3">

                                    <div class="col-md-6">
                                        <label for="codigoEspacio" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoEspacio" name="codigoEspacio" placeholder="Ejemplo: 12" required>
                                        <span id="scodigoEspacio" class="form-text"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tipoEspacio" class="form-label">Tipo</label>
                                        <select class="form-select" name="tipoEspacio" id="tipoEspacio" required>
                                            <option value="" disabled>Seleccione un tipo</option>
                                            <option value="aula" selected>Aula</option>
                                            <option value="laboratorio">Laboratorio</option>
                                        </select>
                                        <span id="stipoEspacio" class="form-text"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="edificio" class="form-label">Edificio</label>
                                        <select class="form-select" name="edificio" id="edificio" required>
                                            <option value="" disabled selected>Seleccione un edificio</option>
                                            <option value="Hilandera">Hilandera</option>
                                            <option value="Giraluna">Giraluna</option>
                                            <option value="Rio 7 Estrellas">Río 7 Estrellas</option>
                                        </select>
                                        <span id="sedificio" class="form-text"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php
    require_once("public/components/footer.php");
    ?>

    <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <script type="text/javascript" src="public/js/espacios.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>

</body>

</html>