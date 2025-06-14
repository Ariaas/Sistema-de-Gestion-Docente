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
        <div class="d-flex justify-content-center">
            <img src="public/assets/img/logo.png" alt="Logo" style="width: 170px; height: auto;">
        </div>
        <div class="d-flex justify-content-center">
            <h1 class="text-center text-primary fw-bold my-4">Sistema de Gestión Docente</h1>
        </div>
        <?php if (isset($_SESSION['name'])): ?>
            <div class="d-flex justify-content-center">
                <h3 class="text-center text-secondary my-3">¡Bienvenid@, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h3>
            </div>
        <?php endif; ?>
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">

                <?php if (!empty($permisos['Docentes'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=docente">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/user-graduate-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                    <h5 class="card-title">Gestionar Docente</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Espacios'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=espacios">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/building-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                    <h5 class="card-title">Gestionar Espacio</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Seccion'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=seccion">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/book-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                    <h5 class="card-title">Gestionar Sección</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Trayecto'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=trayecto">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/map-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                    <h5 class="card-title">Gestionar Trayecto</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Unidad Curricular'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=uc">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/book-open-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                    <h5 class="card-title">Gestionar Unidad Curricular</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Horario Docente'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=horariodocente">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/calendar-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                    <h5 class="card-title">Gestionar Horario Docente</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Malla Curricular'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=mallacurricular">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/screwdriver-wrench-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Gear Icon">
                                    <h5 class="card-title">Gestionar Malla Curricular</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="col">
                    <a class="a-cards" href="?pagina=config">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/gear-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Gear Icon">
                                <h5 class="card-title">Administrar Configuración</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <?php if (
                    !empty($permisos['Bitacora']) ||
                    !empty($permisos['Usuarios']) ||
                    !empty($permisos['Respaldo'])
                ): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=mantenimiento">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/database-fill-gear.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Gear Icon">
                                    <h5 class="card-title">Gestionar Mantenimiento</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Reportes'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=reportes">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/chart-bar-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Journal Icon">
                                    <h5 class="card-title">Generar Reportes</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>