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
            <h1 class="text-center text-primary fw-bold my-4">Reportes</h1>
        </div>
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">

                <div class="col">
                    <a class="a-cards" href="?pagina=ruc">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/user-graduate-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                <h5 class="card-title">Reporte Unidad Curricular</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=reportehor">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/building-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                <h5 class="card-title">Reporte de horarios</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=rtranscripcion">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/book-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Book Icon">
                                <h5 class="card-title">Reporte de transcripción por fase</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=rcargaAcademica">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/map-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Reporte carga académica</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=rdefinitivo">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/map-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Reporte definitivo emtic por fase</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=raulasAsignadas">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/map-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Reporte aulas asginadas</h5>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a class="a-cards" href="?pagina=rprosecucion">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/map-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Reporte de prosecución</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </main>
    <?php require_once("public/components/footer.php"); ?>

</body>

</html>