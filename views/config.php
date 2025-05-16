<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión Docente</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <h1>Adminsitrar Configuración</h1>        
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a class="a-cards" href="?pagina=certificado">
                        <div class="card h-100 text-center custom-card card-docentes">
                            <div class="card-body">
                                <img src="public/assets/icons/person-circle.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                <h5 class="card-title">Gestionar Certificado</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=area">
                        <div class="card h-100 text-center custom-card card-secciones">
                            <div class="card-body">
                                <img src="public/assets/icons/building.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Building Icon">
                                <h5 class="card-title">Gestionar Area</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=categoria">
                        <div class="card h-100 text-center custom-card card-materias">
                            <div class="card-body">
                                <img src="public/assets/icons/journal-text.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Journal Icon">
                                <h5 class="card-title">Gestionar Categoria</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=eje">
                        <div class="card h-100 text-center custom-card card-horarios">
                            <div class="card-body">
                                <img src="public/assets/icons/calendar3.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Gestionar Eje Integrador</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=titulo">
                        <div class="card h-100 text-center custom-card card-trayecto">
                            <div class="card-body">
                                <img src="public/assets/icons/map.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                <h5 class="card-title">Gestionar Titulo</h5>
                                <p class="card-text"></p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=rnota">
                        <div class="card h-100 text-center custom-card card-trayecto">
                            <div class="card-body">
                                <img src="public/assets/icons/map.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                <h5 class="card-title">Resguardar Notas</h5>
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