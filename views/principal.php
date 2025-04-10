<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once("components/head.php"); ?>
    <title>Gestión Docente</title>
</head>
<body>
    <?php require_once("components/sidebar.php"); ?>
    <main class="main-content">
        <h1>Gestión Docente</h1>        
        <div class="container mt-4">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a class="a-cards" href="?pagina=docente">
                        <div class="card h-100 text-center custom-card card-docentes">
                            <div class="card-body">
                                <i class="bi bi-person-circle card-icon"></i>
                                <h5 class="card-title">Docentes</h5>
                                <p class="card-text">Gestión de información de los docentes.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <div class="card h-100 text-center custom-card card-secciones">
                        <div class="card-body">
                        <img src="bootstrap/icons/building.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                            <h5 class="card-title">Secciones</h5>
                            <p class="card-text">Administración de las secciones académicas.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center custom-card card-alumnos">
                        <div class="card-body">
                            <img src="bootstrap/icons/people.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                            <h5 class="card-title">Alumnos</h5>
                            <p class="card-text">Control de datos de los estudiantes.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center custom-card card-materias">
                        <div class="card-body">
                        <img src="bootstrap/icons/journal-text.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                            <h5 class="card-title">Materias</h5>
                            <p class="card-text">Gestión de las materias impartidas.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center custom-card card-horarios">
                        <div class="card-body">
                        <img src="bootstrap/icons/calendar3.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                            
                            <h5 class="card-title">Horarios</h5>
                            <p class="card-text">Organización de horarios académicos.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center custom-card card-trayecto">
                        <div class="card-body">
                           
                            <img src="bootstrap/icons/map.svg" class="card-icon" style="width: 1.5em; height: 1.5em; fill: currentColor;" alt="Person Icon">
                            <h5 class="card-title">Trayecto</h5>
                            <p class="card-text">Planificación de trayectos académicos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once("components/footer.php"); ?>

</body>
</html>