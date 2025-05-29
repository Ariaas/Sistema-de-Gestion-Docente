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
        <div class="d-flex justify-content-center">
            <img src="public/assets/img/logo.png" alt="Logo" style="width: 170px; height: auto;">
        </div>
        <div class="d-flex justify-content-center">
            <h1 class="text-center text-primary fw-bold my-4">Administrar Configuración</h1>
        </div>
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if (!empty($permisos['Certificados'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=certificado">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/circle-user-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                    <h5 class="card-title">Gestionar Certificado</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Area'])): 
                ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=area">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/building-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Building Icon">
                                    <h5 class="card-title">Gestionar Area</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Categorias'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=categoria">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/folder-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Journal Icon">
                                    <h5 class="card-title">Gestionar Categoria</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Eje'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=eje">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/calendar3.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                    <h5 class="card-title">Gestionar Eje Integrador</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Titulo'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=titulo">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/diploma.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                    <h5 class="card-title">Gestionar Titulo</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($permisos['Archivos'])): ?>
                    <div class="col">
                        <a class="a-cards" href="?pagina=archivo">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/folder-open-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                    <h5 class="card-title">Resguardar Notas</h5>
                                    <p class="card-text"></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                    <div class="col">
                        <a class="a-cards" href="?pagina=preguntas">
                            <div class="card h-100 text-center custom-card">
                                <div class="card-body">
                                    <img src="public/assets/icons/folder-open-solid.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                    <h5 class="card-title">Preguntas Frecuentes</h5>
                                    <p class="card-text"></p>
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