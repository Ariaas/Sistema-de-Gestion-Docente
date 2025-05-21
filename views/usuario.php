<?php
// Verificar si el usuario ha iniciado sesión
// if (!isset($_SESSION['name'])) {
//     // Redirigir al usuario a la página de inicio de sesión
//     header('Location: .');
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>

    <title>Usuario</title>
</head>

<body>

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="d-flex flex-column align-items-md-center" style="margin-top: 110px;">
            <center>
                <h2 class="text-primary text-md-center">Gestionar Usuario</h2>
            </center>
            <br>
            <div class="container">
                <div class="text-md-left">
                    <button class="btn btn-success" id="registrar">Registrar</button>
                </div>
            </div>
            <br>
            <div class="container card shadow mb-4 "> <!-- todo el contenido ira dentro de esta etiqueta-->
                <br>
                <div class="container">
                </div>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablausuario">
                            <thead>
                                <tr>
                                    <th style="display: none;">ID</th>
                                    <th>Nombre</th>
                                    <th>Contraseña</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoconsulta"></tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- fin de container -->
        </section>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulario de usuarios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off">
                            <input autocomplete="off" type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="container">
                                <div class="row mb-3">

                                    <div class="col-md-4" style="display: none;">
                                        <label for="usuarioid">id</label>
                                        <input class="form-control" type="text" id="usuarioid" name="usuarioid" min="1">
                                        <span id="susuarioid"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="usuarionombre">Nombre</label>
                                        <input class="form-control" type="text" id="usuarionombre" name="usuarionombre" >
                                        <span id="susuarionombre"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="contraseña">Contraseña</label>
                                        <input class="form-control" type="password" id="contraseña" name="contraseña" >
                                        <span id="scontraseña"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="correo">Correo</label>
                                        <input class="form-control" type="text" id="correo" name="correo" >
                                        <span id="scorreo"></span>
                                    </div>
                                    
                                     <div class="col-md-4">
                                        <label for="rol">Rol</label>
                                        <select class="form-select" name="rol" id="rol">
                                            <option value="" disabled selected >Seleccione un rol</option>
                                            <option value="1" >Usuario</option>
                                            <option value="2" >Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 d-flex justify-content-center align-items-md-center">
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-dark" id="proceso"></button>
                                </div>
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
    <?php //require_once("public/components/body.php"); 
    ?>
    <!-- Footer -->
    </div>
    <!-- fin de container -->

    <!-- Scripts -->
    <script type="text/javascript" src="public/js/usuario.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <!-- Scripts -->
</body>

</html>