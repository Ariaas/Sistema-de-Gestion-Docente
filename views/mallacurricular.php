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

$puede_registrar = tiene_permiso_accion('malla curricular', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('malla curricular', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('malla curricular', 'eliminar', $permisos);
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Malla Curricular</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .btn-xs {
            --bs-btn-padding-y: .1rem;
            --bs-btn-padding-x: .5rem;
            --bs-btn-font-size: .75rem;
        }

        .horas-input {
            min-width: 60px;
        }

        .nav-link {
            font-size: 0.9rem;
        }

        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Malla Curricular</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <div class="text-end">
                    <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Malla Curricular</button>
                    <div>
                        <span id="mensaje-validacion" class="text-danger small"></span>
                    </div>
                </div>
            </div>

            <div class="datatable-ui w-100" id="tablaMallaPrincipalContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover w-100" id="tablamalla">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Cohorte</th>
                                <th>Descripción</th>
                                <th>Activa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" role="dialog" id="modal1">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modal1Titulo">Formulario de Malla (Paso 1 de 2)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">

                            <div id="pagina1">
                                <fieldset class="border p-3 mb-4">
                                    <legend class="w-auto px-2 h6">Datos de la Malla</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label for="mal_codigo" class="form-label">Código</label><input class="form-control" type="text" id="mal_codigo" name="mal_codigo" placeholder="Ej: PNF-INF-2024" required><span id="smalcodigo" class="text-danger small validation-span"></span></div>
                                        <div class="col-md-6 mb-3"><label for="mal_nombre" class="form-label">Nombre</label><input class="form-control" type="text" id="mal_nombre" name="mal_nombre" placeholder="Ej: Malla 2024" required><span id="smalnombre" class="text-danger small validation-span"></span></div>
                                        <div class="col-md-6 mb-3"><label for="mal_cohorte" class="form-label">Cohorte</label><input class="form-control" type="text" id="mal_cohorte" name="mal_cohorte" placeholder="Número de la cohorte. Ej: 4" required><span id="smalcohorte" class="text-danger small validation-span"></span></div>
                                        <div class="col-md-6 mb-3"><label for="mal_descripcion" class="form-label">Descripción</label><input class="form-control" type="text" id="mal_descripcion" name="mal_descripcion" placeholder="Descripción breve de la malla" required><span id="smaldescripcion" class="text-danger small validation-span"></span></div>
                                    </div>
                                </fieldset>
                            </div>
                            <div id="pagina2" style="display: none;">
                                <fieldset class="border p-3">
                                    <legend class="w-auto px-2 h6">Asignar Unidades Curriculares</legend>
                                    <div class="row g-2 align-items-end mb-3">
                                        <div class="col-md-9"><label for="select_uc" class="form-label">Unidad Curricular</label><select id="select_uc" class="form-select" style="width: 100%;"></select></div>
                                        <div class="col-md-3"><button type="button" class="btn btn-info w-100" id="btn_agregar_uc">Agregar Unidad</button></div>
                                    </div>
                                    <div id="contenedorAcordeonUC" class="accordion">
                                    </div>
                                </fieldset>
                            </div>
                            <div id="botones-pagina1" class="modal-footer justify-content-end mt-4"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button><button type="button" class="btn btn-primary" id="btn-siguiente">Siguiente &raquo;</button></div>
                            <div id="botones-pagina2" class="modal-footer justify-content-between mt-4" style="display: none;"><button type="button" class="btn btn-secondary" id="btn-anterior">&laquo; Anterior</button>
                                <div><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button><button type="button" class="btn btn-primary px-4" id="proceso" disabled>GUARDAR</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerMalla">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-dark">
                        <h5 class="modal-title" id="modalVerMallaTitulo">Detalles de la Malla</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="cuerpoModalVer">

                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
                </div>
            </div>
        </div>

    </main>
    <?php require_once("public/components/footer.php"); ?>

    <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="public/js/mallacurricular.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>