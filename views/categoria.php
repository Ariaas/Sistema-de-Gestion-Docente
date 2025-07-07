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

$puede_registrar = tiene_permiso_accion('categoría', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('categoría', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('categoría', 'eliminar', $permisos);
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Categoría</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Categoría</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>
                    <i class="fas fa-plus me-2"></i>Registrar Categoría
                </button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablacategoria">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-center">Acciones</th>
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
                        <h5 class="modal-title">Formulario de Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="categoriaNombre" class="form-label">Nombre de la Categoría</label>
                                        <input class="form-control" type="text" id="categoriaNombre" name="categoriaNombre" required>
                                        <span id="scategoriaNombre" class="form-text"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="categoriaDescripcion" class="form-label">Descripción de la Categoría</label>
                                        <input class="form-control" type="text" id="categoriaDescripcion" name="categoriaDescripcion" required>
                                        <span id="scategoriaDescripcion" class="form-text"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
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
    <script type="text/javascript" src="public/js/categoria.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>