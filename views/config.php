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
    <title>Gestión Docente</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="container-fluid p-4">
            <div class="dashboard-header">
                <h1>Administrar Configuración</h1>
                <p>Selecciona una opción para empezar.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <div class="col">
                    <a href="?pagina=anio" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/circle-user-solid.svg" alt="Gestionar Año">
                        </div>
                        <h5>Gestionar Año</h5>
                    </a>
                </div>
                <div class="col">
                    <a href="?pagina=coordinacion" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/circle-user-solid.svg" alt="Gestionar Coordinación">
                        </div>
                        <h5>Gestionar Coordinación</h5>
                    </a>
                </div>
                <?php if (!empty($permisos['Area'])) : ?>
                    <div class="col">
                        <a href="?pagina=area" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/building-solid.svg" alt="Gestionar Area">
                            </div>
                            <h5>Gestionar Area</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Categorias'])) : ?>
                    <div class="col">
                        <a href="?pagina=categoria" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/folder-solid.svg" alt="Gestionar Categoria">
                            </div>
                            <h5>Gestionar Categoria</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Eje'])) : ?>
                    <div class="col">
                        <a href="?pagina=eje" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/calendar3.svg" alt="Gestionar Eje Integrador">
                            </div>
                            <h5>Gestionar Eje Integrador</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Titulo'])) : ?>
                    <div class="col">
                        <a href="?pagina=titulo" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/diploma.svg" alt="Gestionar Titulo">
                            </div>
                            <h5>Gestionar Titulo</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Archivos'])) : ?>
                    <div class="col">
                        <a href="?pagina=archivo" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/folder-open-solid.svg" alt="Resguardar Notas">
                            </div>
                            <h5>Resguardar Notas</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="col">
                    <a href="?pagina=prosecusion" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Gestionar Prosecución">
                        </div>
                        <h5>Gestionar Prosecución</h5>
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>