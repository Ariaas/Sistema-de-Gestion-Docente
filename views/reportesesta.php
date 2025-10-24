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

if (!function_exists('tiene_permiso_accion')) {
    function tiene_permiso_accion($modulo, $accion, $permisos_array)
    {
        $modulo = strtolower($modulo);
        if (isset($permisos_array[$modulo]) && is_array($permisos_array[$modulo])) {
            return in_array($accion, $permisos_array[$modulo]);
        }
        return false;
    }
}

$puede_registrar = tiene_permiso_accion('reportes', 'registrar', $permisos);

if (!$puede_registrar) {
    header('Location: ?pagina=principal');
    exit();
}
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
                <h1>Reportes</h1>
                <p>Selecciona un reporte para empezar.</p>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">

                <div class="col">
                    <a href="?pagina=reporteG" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte General">
                        </div>
                        <h5>Reporte General de Estudiantes</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=reporteD" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte General">
                        </div>
                        <h5>Reporte de Horas Docentes</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=reporteA" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte General">
                        </div>
                        <h5>Reporte de Uso de Aulas</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=reporteM" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte General">
                        </div>
                        <h5>Reporte de Días con Más Aulas Asignadas</h5>
                    </a>
                </div>
                <div class="col">
                    <a href="?pagina=reporteP" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte General">
                        </div>
                        <h5>Reporte de Prosecución Estudiantil</h5>
                    </a>
                </div>

        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>