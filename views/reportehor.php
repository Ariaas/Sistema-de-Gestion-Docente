<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    <title>Gestión Docente</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="container-fluid p-4">
            <div class="dashboard-header">
                <h1>Sistema de Gestión Docente</h1>
                <p>Selecciona una opción para empezar.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <div class="col">
                    <a href="?pagina=#" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/user-graduate-solid.svg" alt="Reportes horarios por seccion">
                        </div>
                        <h5>Reportes horarios por seccion</h5>
                    </a>
                </div>
                <div class="col">
                    <a href="?pagina=rhordocente" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/building-solid.svg" alt="Reportes horarios de docentes">
                        </div>
                        <h5>Reportes horarios de docentes</h5>
                    </a>
                </div>
                <div class="col">
                    <a href="?pagina=raulario" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reportes de aulario">
                        </div>
                        <h5>Reportes de aulario</h5>
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>