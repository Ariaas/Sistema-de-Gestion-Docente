<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>

    <title>Usuario</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Usuario
            </h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Usuario</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablausuario">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>

        </section>
        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de usuarios</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off">
                            <input autocomplete="off" type="text" class="form-control" name="accion" id="accion"
                                style="display: none;">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-6" style="display: none;">
                                        <label for="usuarioId" class="form-label">ID</label>
                                        <input class="form-control" type="text" id="usuarioId" name="usuarioId" min="1">
                                        <span id="susuarioId" class="form-text"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="usuarionombre" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="usuarionombre" name="usuarionombre">
                                        <span id="susuarionombre" class="form-text"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="correo" class="form-label">Correo</label>
                                        <input class="form-control" type="text" id="correo" name="correo">
                                        <span id="scorreo" class="form-text"></span>
                                    </div>

                                </div>
                                <div class="row mb-3">
                                    <div class="grupo-modificar">
                                        <div class="col-md-6">
                                            <label for="contrasenia" class="form-label">contraseña</label>
                                            <input class="form-control" type="password" id="contrasenia" name="contrasenia">
                                            <span id="scontrasenia" class="form-text"></span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="usuarioRol" class="form-label">Roles</label>
                                                <select class="form-select" name="usuarioRol" id="usuarioRol" required>
                                                    <option value="" disabled selected>Seleccione un rol</option>
                                                    <?php
                                                    if (!empty($roles)) {
                                                        foreach ($roles as $rol) {
                                                            echo "<option value='" . $rol['rol_id'] . "'>" . $rol['rol_nombre'] . "</option>";
                                                        }
                                                    } else {
                                                        echo "<option value='' disabled>No hay roles disponibles</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <span id="srol"></span>
                                            </div>
                                        </div>
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
        <!-- Fin del Modal -->
    </main>
    <!-- Footer -->
    <?php require_once("public/components/footer.php"); ?>
    <!-- Scripts -->
    <script type="text/javascript" src="public/js/usuario.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>