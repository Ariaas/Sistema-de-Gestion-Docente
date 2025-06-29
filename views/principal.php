<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

$permisos = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];

require_once 'public/components/head.php';
?>

<body class="d-flex flex-column min-vh-100">
    <?php require_once 'public/components/sidebar.php'; ?>

    <main class="main-content">
        <section class="container-fluid p-4">

            <!-- Encabezado del Dashboard -->
            <div class="dashboard-header">
                <h1>Panel de Control</h1>
                <p>Bienvenido de nuevo, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>. Selecciona una opción para empezar.</p>
            </div>

            <!-- Tarjetas de acceso rápido con nuevo diseño -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                
                <?php if (!empty($permisos['Docentes'])) : ?>
                <div class="col">
                    <a href="?pagina=docente" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/user-graduate-solid.svg" alt="Docentes">
                        </div>
                        <h5>Docentes</h5>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Horario'])) : ?>
                <div class="col">
                    <a href="?pagina=horario" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/calendar-solid.svg" alt="Horarios">
                        </div>
                        <h5>Horarios</h5>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Reportes'])) : ?>
                <div class="col">
                    <a href="?pagina=reportes" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/chart-bar-solid.svg" alt="Reportes">
                        </div>
                        <h5>Reportes</h5>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($permisos['Malla Curricular'])) : ?>
                <div class="col">
                    <a href="?pagina=mallacurricular" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/screwdriver-wrench-solid.svg" alt="Malla">
                        </div>
                        <h5>Malla Curricular</h5>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php require_once 'public/components/footer.php'; ?>

</body>
</html>