<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestionar Mantenimiento</title>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <div class="d-flex justify-content-center">
            <img src="public/assets/img/logo.png" alt="Logo" style="width: 170px; height: auto;">
        </div>
        <div class="d-flex justify-content-center">
            <h1 class="text-center text-primary fw-bold my-4">Gestionar Mantenimiento</h1>
        </div>
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a class="a-cards" href="?pagina=usuario">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/person.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                <h5 class="card-title">Gestionar Usuario</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=bitacora">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/clock-fill.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                <h5 class="card-title">Gestionar Bitacora</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=respaldo">
                        <div class="card h-100 text-center custom-card">
                            <div class="card-body">
                                <img src="public/assets/icons/device-hdd-fill.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Building Icon">
                                <h5 class="card-title">Gestionar Respaldo</h5>
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