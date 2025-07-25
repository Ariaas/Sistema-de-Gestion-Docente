<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Roles</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Roles</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Rol</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablarol">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Nombre del Rol</th>
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
                        <h5 class="modal-title">Formulario de Roles</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">

                            <div class="col-md-4" style="display: none;">
                                <label for="rolId" class="form-label">ID</label>
                                <input class="form-control" type="text" id="rolId" name="rolId" min="1">
                                <span id="srolId" class="form-text"></span>
                            </div>

                            <div class="col-md-12">
                                <label for="nombreRol" class="form-label">Nombre del Rol</label>
                                <input class="form-control" type="text" id="nombreRol" name="nombreRol" min="1">
                                <span id="snombreRol" class="form-text"></span>
                                <br>
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
        <div class="modal fade" tabindex="-1" role="dialog" id="modal2">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Permisos de Rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="fPermisos" autocomplete="off">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Permisos por módulo</label>
                                        <table class="table table-bordered" id="tablaPermisos">
                                            <thead>
                                                <tr>
                                                    <th>Módulo</th>
                                                    <th class="text-center">Registrar</th>
                                                    <th class="text-center">Modificar</th>
                                                    <th class="text-center">Eliminar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="guardarPermisos">Guardar Permisos
                                </button>
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
    <script type="text/javascript" src="public/js/rol.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>