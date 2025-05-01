<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión Docente</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <h1>Gestión Docente</h1>        
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a class="a-cards" href="?pagina=docente">
                        <div class="card h-100 text-center custom-card card-docentes">
                            <div class="card-body">
                                <img src="public/assets/icons/person-circle.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                                <h5 class="card-title">Docentes</h5>
                                <p class="card-text">Gestión de información de los docentes.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=secciones">
                        <div class="card h-100 text-center custom-card card-secciones">
                            <div class="card-body">
                                <img src="public/assets/icons/building.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Building Icon">
                                <h5 class="card-title">Secciones</h5>
                                <p class="card-text">Administración de las secciones académicas.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=unidades_curriculares">
                        <div class="card h-100 text-center custom-card card-materias">
                            <div class="card-body">
                                <img src="public/assets/icons/journal-text.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Journal Icon">
                                <h5 class="card-title">Unidades Curriculares</h5>
                                <p class="card-text">Gestión de las Unidades Curriculares impartidas.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=horarios">
                        <div class="card h-100 text-center custom-card card-horarios">
                            <div class="card-body">
                                <img src="public/assets/icons/calendar3.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Calendar Icon">
                                <h5 class="card-title">Horarios</h5>
                                <p class="card-text">Organización de horarios académicos.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a class="a-cards" href="?pagina=trayecto">
                        <div class="card h-100 text-center custom-card card-trayecto">
                            <div class="card-body">
                                <img src="public/assets/icons/map.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Map Icon">
                                <h5 class="card-title">Trayecto</h5>
                                <p class="card-text">Planificación de trayectos académicos.</p>
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