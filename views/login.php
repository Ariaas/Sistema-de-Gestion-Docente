<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <style>
    </style>
</head>

<div id="mensajes" style="display:none"
    data-mensaje="<?php echo !empty($mensaje) ? $mensaje : ''; ?>">
</div>

<body class="login-body-bg">
    <div class="login-card">
        <div class="d-flex justify-content-center">
            <img src="public/assets/img/logo.png" alt="Logo" style="width: 100px; height: auto;">
        </div>
        <h2 class="login-title">Iniciar Sesión</h2>
        <form method="post" autocomplete="off" id="f">
            <input type="hidden" name="accion" id="accion" value="acceder">
            <div class="mb-3">
                <label for="nombreUsuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" required autofocus>
            </div>
            <div class="mb-3">
                <label for="contraseniaUsuario" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseniaUsuario" name="contraseniaUsuario" required>
            </div>
            <button type="submit" class="btn bsb-btn-xl btn-primary" id="acceder">Iniciar Sesión</button>
        </form>
        <button type="button" class="btn btn-link" id="recuperarBtn">¿Olvidaste tu contraseña?</button>
    </div>

    <div class="modal fade" id="modalRecuperarUsuario" tabindex="-1" aria-labelledby="modalRecuperarUsuarioLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="formRecuperarUsuario" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Recuperar contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="usuarioRecuperar" class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" id="usuarioRecuperar" name="usuarioRecuperar"
                            required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enviar código</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCodigo" tabindex="-1" aria-labelledby="modalCodigoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formCodigo" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ingrese el código recibido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="codigoRecuperacion" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigoRecuperacion" name="codigoRecuperacion"
                            required>
                        <input type="hidden" id="usuarioCodigo" name="usuarioCodigo">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Validar código</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalNuevaClave" tabindex="-1" aria-labelledby="modalNuevaClaveLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="formNuevaClave" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="usuarioClave" name="usuarioClave">
                        <input type="hidden" id="codigoClave" name="codigoClave">
                        <label for="nuevaClave1" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="nuevaClave1" name="nuevaClave1" required>
                        <label for="nuevaClave2" class="form-label mt-2">Repita la nueva contraseña</label>
                        <input type="password" class="form-control" id="nuevaClave2" name="nuevaClave2" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Actualizar contraseña</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script type="text/javascript" src="public/js/login.js"></script>
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>