<?php include_once("public/components/head.php"); ?>

<style>
    #btnVolverArriba {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 30px;
        z-index: 99;
        border: none;
        outline: none;
        background-color: #0d6efd;
        color: white;
        cursor: pointer;
        padding: 10px 16px;
        border-radius: 50%;
        font-size: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s, opacity 0.5s, visibility 0.5s;
    }

    #btnVolverArriba:hover {
        background-color: #0a58ca;
    }

    .accordion-button:not(.collapsed) {
        color: #0c63e4;
        background-color: #e7f1ff;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
    }
</style>

<body id="page-top" class="bg-light">
    <?php include_once("public/components/sidebar.php"); ?>
    <main>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-10">
                    <div class="bg-white rounded-3 shadow-lg p-4 p-md-5">
                        <div class="text-center mb-5">
                            <h1 class="display-5 fw-bold mt-2">Manual de Usuario Interactivo</h1>
                            <p class="text-muted">Guía completa para el Sistema de Gestión Docente</p>
                        </div>

                        <div class="card mb-5 shadow-sm">
                            <div class="card-header">
                                <h3 class="mb-0">Índice de Contenidos</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php
                                    $modulos = [
                                        'login' => 'Inicio de Sesión',
                                        'principal' => 'Pantalla Principal',
                                        'modulo-docentes' => 'Módulo Docentes',
                                        'modulo-espacios' => 'Módulo Espacios',
                                        'modulo-actividad' => 'Módulo Actividad',
                                        'modulo-anio' => 'Módulo Año',
                                        'modulo-archivo' => 'Módulo Archivo',
                                        'modulo-area' => 'Módulo Área',
                                        'modulo-backup' => 'Módulo Backup',
                                        'modulo-bitacora' => 'Módulo Bitácora',
                                        'modulo-categoria' => 'Módulo Categoría',
                                        'modulo-coordinacion' => 'Módulo Coordinación',
                                        'modulo-eje' => 'Módulo Eje',
                                        'modulo-horariodocente' => 'Módulo Horario Docente',
                                        'modulo-mallacurricular' => 'Módulo Malla Curricular',
                                        'modulo-mantenimiento' => 'Módulo Mantenimiento',
                                        'modulo-notificaciones' => 'Módulo Notificaciones',
                                        'modulo-perfil' => 'Módulo Perfil',
                                        'modulo-prosecusion' => 'Módulo Prosecusión',
                                        'modulo-reportes' => 'Módulo Reportes',
                                        'modulo-rol' => 'Módulo Rol',
                                        'modulo-seccion' => 'Módulo Sección',
                                        'modulo-titulo' => 'Módulo Título',
                                        'modulo-turno' => 'Módulo Turno',
                                        'modulo-uc' => 'Módulo UC',
                                        'modulo-usuario' => 'Módulo Usuario'
                                    ];
                                    $columnCount = 3;
                                    $itemsPerColumn = ceil(count($modulos) / $columnCount);
                                    $chunks = array_chunk($modulos, $itemsPerColumn, true);
                                    foreach ($chunks as $chunk) {
                                        echo '<div class="col-md-4">';
                                        echo '<ul class="list-unstyled">';
                                        foreach ($chunk as $id => $text) {
                                            echo '<li><a href="#' . $id . '" class="text-decoration-none d-block p-1">' . $text . '</a></li>';
                                        }
                                        echo '</ul></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="manualAccordion">

                            <div class="accordion-item" id="login">
                                <h2 class="accordion-header" id="headingLogin">
                                    <button class="accordion-button fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin" aria-expanded="true" aria-controls="collapseLogin">
                                        Pantalla de Inicio de Sesión (Login)
                                    </button>
                                </h2>
                                <div id="collapseLogin" class="accordion-collapse collapse show" aria-labelledby="headingLogin" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="text-center mb-4">
                                            <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/login.png" alt="Pantalla Login" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                                        </div>
                                        <p class="text-center">
                                            Al acceder al sistema, primero verás la pantalla de inicio de sesión. Debes ingresar tu <strong>usuario</strong> y <strong>contraseña</strong> para poder acceder a las funcionalidades del sistema.<br>
                                            Si los datos son correctos, serás redirigido al Panel de Control. Si olvidas tu contraseña, contacta al administrador del sistema para recuperarla.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item" id="principal">
                                <h2 class="accordion-header" id="headingPrincipal">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrincipal" aria-expanded="false" aria-controls="collapsePrincipal">
                                        Pantalla Principal
                                    </button>
                                </h2>
                                <div id="collapsePrincipal" class="accordion-collapse collapse" aria-labelledby="headingPrincipal" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="text-center mb-4">
                                            <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal.png" alt="Pantalla Principal" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                                        </div>
                                        <p class="text-center">
                                            Al ingresar al sistema, se muestra el <strong>Panel de Control</strong> con un diseño limpio y moderno. Aquí puedes ver tres opciones principales:
                                        </p>
                                        <div class="row justify-content-center mb-4">
                                            <div class="col-md-4 mb-3">
                                                <div class="card text-center shadow-sm h-100">
                                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                                        <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-docente.png" alt="Docentes" style="width:120px;height:auto;">
                                                        <h5 class="card-title mt-3">Docentes</h5>
                                                        <p class="card-text">Acceso a la gestión de docentes. Permite registrar, consultar, modificar y eliminar información de los docentes.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card text-center shadow-sm h-100">
                                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                                        <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-reportes.png" alt="Reportes" style="width:120px;height:auto;">
                                                        <h5 class="card-title mt-3">Reportes</h5>
                                                        <p class="card-text">Acceso a los reportes estadísticos y de gestión. Puedes generar y visualizar reportes según los filtros disponibles.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card text-center shadow-sm h-100">
                                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                                        <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-malla.png" alt="Malla Curricular" style="width:120px;height:auto;">
                                                        <h5 class="card-title mt-3">Malla Curricular</h5>
                                                        <p class="card-text">Acceso a la gestión de la malla curricular. Permite administrar la estructura académica y sus componentes.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <ul class="list-group list-group-flush mb-4">
                                            <li class="list-group-item"><strong>Barra superior:</strong> Desde el menú de navegación puedes acceder rápidamente a los módulos principales, reportes, administración y ayuda. A la derecha, verás tu nombre de usuario y un icono de notificaciones.</li>
                                            <li class="list-group-item"><strong>Pie de página:</strong> Muestra los derechos reservados del sistema.</li>
                                            <li class="list-group-item">Para navegar, haz clic en cualquiera de las opciones del panel o utiliza el menú superior para acceder a otras secciones del sistema.</li>
                                        </ul>
                                        <div class="alert alert-info text-center mt-4">
                                            <strong>Nota:</strong> Puedes acceder a este manual en cualquier momento desde el menú <strong>Ayuda &gt; Manual de Usuario</strong> en la barra de navegación.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $all_modules = [
                                ['id' => 'modulo-docentes', 'title' => 'Módulo Docentes', 'img' => 'docente', 'modal_img_1' => 'docente-modal-1', 'modal_img_2' => 'docente-modal-2', 'desc' => 'En el módulo <strong>Docentes</strong> puedes gestionar toda la información relacionada con los docentes de la institución.', 'actions' => ['Registrar un nuevo docente usando el botón <strong>Registrar Docente</strong>.', 'Consultar la lista de docentes registrados.', 'Utilizar los botones de acción para editar, eliminar o ver los datos adicionales de cada docente.']],
                                ['id' => 'modulo-espacios', 'title' => 'Módulo Espacios', 'img' => 'espacios', 'modal_img_1' => 'espacios-modal', 'desc' => 'El módulo <strong>Espacios</strong> permite gestionar los espacios físicos o virtuales disponibles en la institución, como aulas, laboratorios, etc.', 'actions' => ['Registrar un nuevo espacio usando el botón <strong>Registrar Espacio</strong>.', 'Consultar la lista de espacios registrados.', 'Editar la información de un espacio existente haciendo clic en el ícono de edición.', 'Eliminar un espacio si es necesario, usando el ícono de eliminar.']],
                                ['id' => 'modulo-actividad', 'title' => 'Módulo Actividad', 'img' => 'actividad', 'desc' => 'En el módulo <strong>Actividad</strong> puedes gestionar las actividades académicas y administrativas.', 'actions' => ['Registrar nuevas actividades usando el botón <strong>Registrar Actividad</strong>.', 'Consultar la lista de actividades registradas.', 'Editar o eliminar actividades existentes mediante los botones de acción.']],
                                ['id' => 'modulo-anio', 'title' => 'Módulo Año', 'img' => 'anio', 'desc' => 'El módulo <strong>Año</strong> permite administrar los años académicos del sistema.', 'actions' => ['Agregar nuevos años académicos.', 'Modificar o eliminar años existentes.', 'Visualizar la lista completa de años académicos registrados.']],
                                ['id' => 'modulo-archivo', 'title' => 'Módulo Archivo', 'img' => 'archivo', 'desc' => 'En el módulo <strong>Archivo</strong> puedes gestionar documentos y archivos relacionados con la gestión docente.', 'actions' => ['Subir nuevos archivos y documentos.', 'Consultar y descargar archivos existentes.', 'Eliminar archivos que ya no sean necesarios.']],
                                ['id' => 'modulo-area', 'title' => 'Módulo Área', 'img' => 'area', 'desc' => 'El módulo <strong>Área</strong> permite administrar las áreas académicas o departamentos.', 'actions' => ['Registrar nuevas áreas.', 'Editar o eliminar áreas existentes.', 'Visualizar la lista de áreas registradas.']],
                                ['id' => 'modulo-backup', 'title' => 'Módulo Backup', 'img' => 'backup', 'desc' => 'En el módulo <strong>Backup</strong> puedes realizar copias de seguridad del sistema para proteger la información.', 'actions' => ['Crear nuevas copias de seguridad.', 'Restaurar datos desde copias anteriores.', 'Gestionar y eliminar backups existentes.']],
                                ['id' => 'modulo-bitacora', 'title' => 'Módulo Bitácora', 'img' => 'bitacora', 'desc' => 'El módulo <strong>Bitácora</strong> permite visualizar el registro de actividades y eventos del sistema para auditoría y seguimiento.', 'actions' => ['Consultar el historial de acciones realizadas por los usuarios.', 'Filtrar registros por fecha, usuario o tipo de evento.']],
                                ['id' => 'modulo-categoria', 'title' => 'Módulo Categoría', 'img' => 'categoria', 'desc' => 'En el módulo <strong>Categoría</strong> puedes gestionar las categorías utilizadas para clasificar diferentes elementos del sistema.', 'actions' => ['Registrar nuevas categorías.', 'Editar o eliminar categorías existentes.', 'Visualizar la lista de categorías registradas.']],
                                ['id' => 'modulo-coordinacion', 'title' => 'Módulo Coordinación', 'img' => 'coordinacion', 'desc' => 'El módulo <strong>Coordinación</strong> permite administrar las coordinaciones académicas o administrativas.', 'actions' => ['Registrar nuevas coordinaciones.', 'Editar o eliminar coordinaciones existentes.', 'Visualizar la lista de coordinaciones registradas.']],
                                ['id' => 'modulo-eje', 'title' => 'Módulo Eje', 'img' => 'eje', 'desc' => 'En el módulo <strong>Eje</strong> puedes gestionar los ejes temáticos o áreas de conocimiento.', 'actions' => ['Registrar nuevos ejes.', 'Editar o eliminar ejes existentes.', 'Visualizar la lista de ejes registrados.']],
                                ['id' => 'modulo-horariodocente', 'title' => 'Módulo Horario Docente', 'img' => 'horariodocente', 'desc' => 'El módulo <strong>Horario Docente</strong> permite gestionar los horarios asignados a los docentes.', 'actions' => ['Registrar y modificar horarios de los docentes.', 'Consultar los horarios asignados.', 'Eliminar horarios si es necesario.']],
                                ['id' => 'modulo-mallacurricular', 'title' => 'Módulo Malla Curricular', 'img' => 'mallacurricular', 'desc' => 'En el módulo <strong>Malla Curricular</strong> puedes administrar la estructura académica y sus componentes.', 'actions' => ['Registrar y modificar la malla curricular.', 'Consultar los componentes y asignaturas.', 'Eliminar elementos si es necesario.']],
                                ['id' => 'modulo-mantenimiento', 'title' => 'Módulo Mantenimiento', 'img' => 'mantenimiento', 'desc' => 'El módulo <strong>Mantenimiento</strong> permite realizar tareas de mantenimiento del sistema.', 'actions' => ['Ejecutar tareas programadas de mantenimiento.', 'Gestionar configuraciones y parámetros del sistema.', 'Realizar respaldos y restauraciones.']],
                                ['id' => 'modulo-notificaciones', 'title' => 'Módulo Notificaciones', 'img' => 'notificaciones', 'desc' => 'En el módulo <strong>Notificaciones</strong> puedes gestionar las notificaciones del sistema.', 'actions' => ['Consultar el historial de notificaciones enviadas.']],
                                ['id' => 'modulo-perfil', 'title' => 'Módulo Perfil', 'img' => 'perfil', 'desc' => 'El módulo <strong>Perfil</strong> permite a los usuarios gestionar su información personal y configuración.', 'actions' => ['Actualizar datos personales.', 'Cambiar la contraseña de acceso.', 'Configurar preferencias del sistema.']],
                                ['id' => 'modulo-prosecusion', 'title' => 'Módulo Prosecusión', 'img' => 'prosecusion', 'desc' => 'El módulo <strong>Prosecusión</strong> permite gestionar la prosecución académica de los estudiantes.', 'actions' => ['Registrar y modificar datos de prosecusión.', 'Consultar la información de prosecusión académica.', 'Eliminar registros si es necesario.']],
                                ['id' => 'modulo-reportes', 'title' => 'Módulo Reportes', 'img' => 'reportes', 'desc' => 'En el módulo <strong>Reportes</strong> puedes generar y visualizar reportes estadísticos y de gestión.', 'actions' => ['Generar reportes según filtros disponibles.', 'Visualizar reportes en diferentes formatos.', 'Exportar reportes para análisis externo.']],
                                ['id' => 'modulo-rol', 'title' => 'Módulo Rol', 'img' => 'rol', 'desc' => 'El módulo <strong>Rol</strong> permite gestionar los roles y permisos de los usuarios.', 'actions' => ['Registrar nuevos roles.', 'Editar o eliminar roles existentes.', 'Asignar permisos a los roles.']],
                                ['id' => 'modulo-seccion', 'title' => 'Módulo Sección', 'img' => 'seccion', 'desc' => 'En el módulo <strong>Sección</strong> puedes gestionar las secciones académicas.', 'actions' => ['Registrar nuevas secciones.', 'Editar o eliminar secciones existentes.', 'Visualizar la lista de secciones registradas.', 'Crear horarios para las secciones.']],
                                ['id' => 'modulo-titulo', 'title' => 'Módulo Título', 'img' => 'titulo', 'desc' => 'El módulo <strong>Título</strong> permite gestionar los títulos académicos.', 'actions' => ['Registrar nuevos títulos.', 'Editar o eliminar títulos existentes.', 'Visualizar la lista de títulos registrados.']],
                                ['id' => 'modulo-turno', 'title' => 'Módulo Turno', 'img' => 'turno', 'desc' => 'En el módulo <strong>Turno</strong> puedes gestionar los turnos académicos.', 'actions' => ['Registrar nuevos turnos.', 'Editar o eliminar turnos existentes.', 'Visualizar la lista de turnos registrados.']],
                                ['id' => 'modulo-uc', 'title' => 'Módulo UC', 'img' => 'uc', 'desc' => 'El módulo <strong>UC</strong> permite gestionar las unidades curriculares.', 'actions' => ['Registrar nuevas unidades curriculares.', 'Editar o eliminar unidades existentes.', 'Visualizar la lista de unidades curriculares registradas.', 'Asignar unidades a los docentes.', 'Consultar las unidades curriculares asignadas a cada docente.']],
                                ['id' => 'modulo-usuario', 'title' => 'Módulo Usuario', 'img' => 'usuario', 'desc' => 'En el módulo <strong>Usuario</strong> puedes gestionar los usuarios del sistema.', 'actions' => ['Registrar nuevos usuarios.', 'Editar o eliminar usuarios existentes.', 'Asignar roles y permisos a los usuarios.']]
                            ];

                            foreach ($all_modules as $module) {
                                $headingId = 'heading' . ucfirst(str_replace('-', '', $module['id']));
                                $collapseId = 'collapse' . ucfirst(str_replace('-', '', $module['id']));

                                echo '<div class="accordion-item" id="' . $module['id'] . '">';
                                echo '<h2 class="accordion-header" id="' . $headingId . '">';
                                echo '<button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '">';
                                echo $module['title'];
                                echo '</button></h2>';
                                echo '<div id="' . $collapseId . '" class="accordion-collapse collapse" aria-labelledby="' . $headingId . '" data-bs-parent="#manualAccordion">';
                                echo '<div class="accordion-body">';
                                echo '<div class="text-center mb-4">';
                                echo '<img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/' . $module['img'] . '.png" alt="Pantalla ' . $module['title'] . '" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">';
                                echo '</div>';
                                echo '<p class="text-center">' . $module['desc'] . ' Desde aquí puedes:</p>';
                                echo '<ul class="list-group mb-4">';

                                foreach ($module['actions'] as $action) {
                                    echo '<li class="list-group-item border-0">&bull; ' . $action . '</li>';
                                }
                                echo '</ul>';

                                if (isset($module['modal_img_1'])) {
                                    echo '<div class="text-center mb-4">';
                                    echo '<img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/' . $module['modal_img_1'] . '.png" alt="Modal ' . $module['title'] . '" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">';
                                    echo '</div>';
                                }

                                if ($module['id'] === 'modulo-docentes') {
                                    echo '<div class="row justify-content-center">
                                            <div class="col-md-8">
                                                <div class="alert alert-secondary">
                                                    <strong>Botón Modificar:</strong> El botón de modificar te permite cambiar la información de un docente existente. Al hacer clic, se abrirá el mismo formulario modal que para agregar, pero con los datos del docente ya cargados. Realiza los cambios necesarios y guarda para actualizar la información.<br><br>
                                                    <strong>Botón Eliminar:</strong> El botón de eliminar te permite borrar un docente del sistema. Al hacer clic, se te pedirá una confirmación para evitar eliminaciones accidentales. Una vez confirmado, el docente será eliminado de la lista y no podrá recuperarse.
                                                </div>
                                                <div class="alert alert-info">
                                                    <strong>Botón Ver Datos Adicionales:</strong> Este botón te permite consultar información complementaria del docente. Al hacer clic, se abrirá una ventana que muestra:
                                                    <ul>
                                                        <li><strong>Horas de Actividad:</strong> Un resumen de las horas del docente distribuidas en diferentes categorías (Académicas, Creación Intelectual, etc.).</li>
                                                        <li><strong>Preferencia de Horario para Clases:</strong> Muestra los días y horas que el docente prefiere para impartir clases, facilitando la creación de horarios.</li>
                                                    </ul>
                                                    <div class="text-center mt-3">
                                                        <img src="/Sistema-de-Gestion-Docente/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/docente-modal-2.png" alt="Modal Datos Adicionales" class="img-fluid rounded shadow" style="max-width:600px;width:100%;height:auto;border:2px solid #e0e0e0;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                                }

                                if ($module['id'] === 'modulo-espacios') {
                                    echo '<div class="row justify-content-center">
                                            <div class="col-md-6">
                                                <div class="alert alert-secondary">
                                                    <strong>Botón Modificar:</strong> El botón de editar te permite modificar la información de un espacio existente. Al hacer clic, se abrirá el mismo formulario modal que para agregar, pero con los datos del espacio ya cargados. Realiza los cambios necesarios y guarda para actualizar la información.<br><br>
                                                    <strong>Botón Eliminar:</strong> El botón de eliminar te permite borrar un espacio del sistema. Al hacer clic, se te pedirá una confirmación para evitar eliminaciones accidentales. Una vez confirmado, el espacio será eliminado de la lista y no podrá recuperarse.
                                                </div>
                                            </div>
                                        </div>';
                                }

                                echo '</div></div></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once("public/components/footer.php"); ?>

    <button onclick="topFunction()" id="btnVolverArriba" title="Ir arriba">&uarr;</button>

    <script>
        let mybutton = document.getElementById("btnVolverArriba");

        window.onscroll = function() {
            scrollFunction()
        };

        function scrollFunction() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                mybutton.style.display = "block";
            } else {
                mybutton.style.display = "none";
            }
        }

        function topFunction() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        document.querySelectorAll('.card-body a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {

                const targetId = this.getAttribute('href');
                const accordionItem = document.querySelector(targetId);

                if (accordionItem) {
                    const collapseElement = accordionItem.querySelector('.accordion-collapse');
                    if (collapseElement) {
                        const bsCollapse = bootstrap.Collapse.getInstance(collapseElement) || new bootstrap.Collapse(collapseElement, {
                            toggle: false
                        });
                        bsCollapse.show();
                    }
                }
            });
        });
    </script>

</body>

</html>