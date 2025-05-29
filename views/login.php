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
    </div>
    <script type="text/javascript" src="public/js/login.js"></script>
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>