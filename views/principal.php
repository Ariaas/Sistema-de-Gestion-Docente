<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

$permisos_sesion = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
$permisos = array_change_key_case($permisos_sesion, CASE_LOWER);

if (!function_exists('tiene_permiso')) {
    function tiene_permiso($modulo, $permisos_array)
    {
        return !empty($permisos_array[strtolower($modulo)]);
    }
}

require_once 'public/components/head.php';
?>

<body class="d-flex flex-column min-vh-100">
    <?php require_once 'public/components/sidebar.php'; ?>

    <main class="main-content">
        <section class="container-fluid p-4">

            <div class="dashboard-header">
                <h1>Panel de Control</h1>
                <p>Bienvenido de nuevo, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>. Selecciona una opción para empezar.</p>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">

                <?php if (tiene_permiso('Docentes', $permisos)) : ?>
                    <div class="col">
                        <a href="?pagina=docente" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/user-graduate-solid.svg" alt="Docentes">
                            </div>
                            <h5>Docentes</h5>
                        </a>
                    </div>
                <?php endif; ?>





                <?php if (tiene_permiso('Espacio', $permisos)) : ?>
                    <div class="col">
                        <a href="?pagina=espacios" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/screwdriver-wrench-solid.svg" alt="espacio">
                            </div>
                            <h5>Espacios</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (tiene_permiso('Seccion', $permisos)) : ?>
                    <div class="col">
                        <a href="?pagina=seccion" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/people.svg" alt="Seccion">
                            </div>
                            <h5>Seccion</h5>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (tiene_permiso('Unidad Curricular', $permisos)) : ?>
                    <div class="col">
                        <a href="?pagina=uc" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/book-solid.svg" alt="Unidad Curricular">
                            </div>
                            <h5>Unidad Curricular</h5>
                        </a>
                    </div>

                    <?php if (tiene_permiso('Malla Curricular', $permisos)) : ?>
                        <div class="col">
                            <a href="?pagina=mallacurricular" class="dashboard-card">
                                <div class="icon-circle">
                                    <img src="public/assets/icons/journal-text.svg" alt="Malla Curricular">
                                </div>
                                <h5>Malla Curricular</h5>
                            </a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
                <?php if (tiene_permiso('Reportes', $permisos)) : ?>
                    <div class="col">
                        <a href="?pagina=reportes" class="dashboard-card">
                            <div class="icon-circle">
                                <img src="public/assets/icons/chart-bar-solid.svg" alt="Reportes">
                            </div>
                            <h5>Reportes</h5>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <?php require_once 'public/components/footer.php'; ?>

</body>

</html>