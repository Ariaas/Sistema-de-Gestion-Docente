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
                    <a href="?pagina=ruc" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/user-graduate-solid.svg" alt="Reporte Unidad Curricular">
                        </div>
                        <h5>Reporte Unidad Curricular</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=reportehor" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/building-solid.svg" alt="Reporte de horarios">
                        </div>
                        <h5>Reporte de horarios</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=rtranscripcion" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/book-solid.svg" alt="Reporte de transcripción por fase">
                        </div>
                        <h5>Reporte de transcripción por fase</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=rcargaAcademica" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte carga académica">
                        </div>
                        <h5>Reporte carga académica</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=rdefinitivo" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte definitivo emtic por fase">
                        </div>
                        <h5>Reporte definitivo emtic por fase</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=raulaAsignada" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte aulas asignadas">
                        </div>
                        <h5>Reporte aulas asignadas</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=rprosecucion" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte de prosecución">
                        </div>
                        <h5>Reporte de prosecución</h5>
                    </a>
                </div>

                 <div class="col">
                    <a href="?pagina=rmalla" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte de malla curricular">
                        </div>
                        <h5>Reporte de malla</h5>
                    </a>
                </div>

                <div class="col">
                    <a href="?pagina=rod" class="dashboard-card">
                        <div class="icon-circle">
                            <img src="public/assets/icons/map-solid.svg" alt="Reporte de malla curricular">
                        </div>
                        <h5>Reporte de OD</h5>
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>