<?php
// Vista de Login - Solo la vista, siguiendo la estructura del proyecto
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Iniciar Sesión</title>
    <style>   
    </style>
</head>
<body class="login-body-bg">
    <div class="login-card">
        <h2 class="login-title">Iniciar Sesión</h2>
        <form method="get" autocomplete="off">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <a href="?pagina=principal" class="btn btn-primary w-100">Iniciar Sesión</a>
        </form>
    </div>
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
