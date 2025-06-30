<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Perfil de Usuario</title>
    <style>
        .perfil-foto-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .perfil-foto {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #0d6efd;
            background: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .perfil-foto-label {
            margin-top: 1rem;
            font-weight: 500;
            color: #0d6efd;
            cursor: pointer;
        }

        .perfil-form {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            padding: 2rem 2.5rem 1.5rem 2.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Mi Perfil</h2>
            <div class="perfil-form">
                <form id="formPerfil" enctype="multipart/form-data" autocomplete="off">
                    <div class="perfil-foto-container">
                        <img id="fotoPerfil" src="public/assets/icons/user-circle.svg" class="perfil-foto" alt="Foto de perfil">
                        <label for="fotoPerfilInput" class="perfil-foto-label">Cambiar foto</label>
                        <input type="file" id="fotoPerfilInput" name="fotoPerfilInput" accept=".png,.jpg,.jpeg,.svg" style="display:none;">
                    </div>

                    <div class="mb-3">
                        <label for="nombreUsuario" class="form-label">Nombre</label>
                        <input type="text" readonly class="form-control" id="nombreUsuario" name="nombreUsuario">
                    </div>
                    <div class="mb-3">
                        <label for="correoUsuario" class="form-label">Correo</label>
                        <input type="email" class="form-control" id="correoUsuario" name="correoUsuario" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/perfil.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    <script>
        $(document).on('click', '.perfil-foto-label', function() {
            $('#fotoPerfilInput').click();
        });
    </script>
</body>

</html>