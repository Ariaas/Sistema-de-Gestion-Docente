<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

$permisos = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestionar Mantenimiento</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="container-fluid p-4">
            <div class="dashboard-header">
                <h1>Gestionar Mantenimiento</h1>
                <p>Selecciona una opción para empezar.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php if (!empty($permisos['Usuarios'])) : ?>
                    <div class="col">
                        <a href="?pagina=usuario" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/person.svg" alt="Gestionar Usuario">
                            </div>
                            <h5>Gestionar Usuario</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Bitacora'])) : ?>
                    <div class="col">
                        <a href="?pagina=bitacora" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/clock-fill.svg" alt="Gestionar Bitacora">
                            </div>
                            <h5>Gestionar Bitacora</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Respaldo'])) :
                ?>
                    <div class="col">
                        <a href="?pagina=backup" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/device-hdd-fill.svg" alt="Gestionar Respaldo">
                            </div>
                            <h5>Gestionar Respaldo</h5>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>