<?php
// Vista de Login - Solo la vista, siguiendo la estructura del proyecto
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <style>   
    </style>
</head>
<body class="login-body-bg">
    <div class="login-card">
        <div class="d-flex justify-content-center">
            <img src="public/assets/img/logo.png" alt="Logo" style="width: 100px; height: auto;">        
        </div>
        <h2 class="login-title">Iniciar Sesión</h2>
        <form method="post" autocomplete="off">
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
