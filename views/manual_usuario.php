<?php include_once("public/components/head.php"); ?>
<body>
    <?php include_once("public/components/sidebar.php"); ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="bg-white rounded shadow p-4">
                    <h1 class="text-center mb-4">Manual de Usuario</h1>
                    <hr>
                    <h2 class="text-center mb-3">Pantalla de Inicio de Sesión (Login)</h2>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/login.png" alt="Pantalla Login" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <p class="text-center">
                        Al acceder al sistema, primero verás la pantalla de inicio de sesión. Debes ingresar tu <strong>usuario</strong> y <strong>contraseña</strong> para poder acceder a las funcionalidades del sistema.<br>
                        Si los datos son correctos, serás redirigido al Panel de Control. Si olvidas tu contraseña, contacta al administrador del sistema para recuperarla.
                    </p>
                    <hr class="my-5">
                    <h2 class="text-center mb-3">Pantalla Principal</h2>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal.png" alt="Pantalla Principal" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <p class="text-center">
                        Al ingresar al sistema, se muestra el <strong>Panel de Control</strong> con un diseño limpio y moderno. Aquí puedes ver tres opciones principales:
                    </p>
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card text-center shadow-sm h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-docente.png" alt="Docentes" style="width:120px;height:auto;">
                                    <h5 class="card-title mt-3">Docentes</h5>
                                    <p class="card-text">Acceso a la gestión de docentes. Permite registrar, consultar, modificar y eliminar información de los docentes.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center shadow-sm h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-reportes.png" alt="Reportes" style="width:120px;height:auto;">
                                    <h5 class="card-title mt-3">Reportes</h5>
                                    <p class="card-text">Acceso a los reportes estadísticos y de gestión. Puedes generar y visualizar reportes según los filtros disponibles.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center shadow-sm h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/principal-malla.png" alt="Malla Curricular" style="width:120px;height:auto;">
                                    <h5 class="card-title mt-3">Malla Curricular</h5>
                                    <p class="card-text">Acceso a la gestión de la malla curricular. Permite administrar la estructura académica y sus componentes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="mb-4">
                        <li><strong>Barra superior:</strong> Desde el menú de navegación puedes acceder rápidamente a los módulos principales, reportes, administración y ayuda. A la derecha, verás tu nombre de usuario y un icono de notificaciones.</li>
                        <li><strong>Pie de página:</strong> Muestra los derechos reservados del sistema.</li>
                        <li>Para navegar, haz clic en cualquiera de las opciones del panel o utiliza el menú superior para acceder a otras secciones del sistema.</li>
                    </ul>
                    <div class="alert alert-info text-center mt-4">
                        <strong>Nota:</strong> Puedes acceder a este manual en cualquier momento desde el menú <strong>Ayuda &gt; Manual de Usuario</strong> en la barra de navegación.
                    </div>
                    <hr class="my-5">
                    <h2 class="text-center mb-3">Módulo Docentes</h2>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/docente.png" alt="Pantalla Docentes" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <p class="text-center">
                        En el módulo <strong>Docentes</strong> puedes gestionar toda la información relacionada con los docentes de la institución. Desde aquí puedes:
                    </p>
                    <ul class="mb-4">
                        <li>Registrar un nuevo docente usando el botón <strong>Registrar Docente</strong>.</li>
                        <li>Consultar la lista de docentes registrados.</li>
                        <li>Utilizar los botones de acción para editar, eliminar o ver los datos adicionales de cada docente.</li>
                    </ul>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/docente-modal-1.png" alt="Modal Docente" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-secondary">
                                <strong>Botón Modificar:</strong> El ícono de lápiz te permite modificar la información de un docente existente. Al hacer clic, se abrirá el mismo formulario modal que para agregar, pero con los datos del docente ya cargados. Realiza los cambios necesarios y guarda para actualizar la información.<br><br>
                                <strong>Botón Eliminar:</strong> El ícono de papelera te permite borrar un docente del sistema. Al hacer clic, se te pedirá una confirmación para evitar eliminaciones accidentales. Una vez confirmado, el docente será eliminado de la lista y no podrá recuperarse.
                           </div>
                           <div class="alert alert-info">
                                <strong>Botón Ver Datos Adicionales:</strong> El ícono del ojo te permite consultar información complementaria del docente. Al hacer clic, se abrirá una ventana que muestra:
                                <ul>
                                    <li><strong>Horas de Actividad:</strong> Un resumen de las horas del docente distribuidas en diferentes categorías (Académicas, Creación Intelectual, etc.).</li>
                                    <li><strong>Preferencia de Horario para Clases:</strong> Muestra los días y horas que el docente prefiere para impartir clases, facilitando la creación de horarios.</li>
                                </ul>
                                <div class="text-center mt-3">
                                    <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/docente-modal-2.png" alt="Modal Datos Adicionales" class="img-fluid rounded shadow" style="max-width:600px;width:100%;height:auto;border:2px solid #e0e0e0;">
                                </div>
                           </div>
                        </div>
                    </div>
                    <hr class="my-5">
                    <h2 class="text-center mb-3">Módulo Espacios</h2>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/espacios.png" alt="Pantalla Espacios" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <p class="text-center">
                        El módulo <strong>Espacios</strong> permite gestionar los espacios físicos o virtuales disponibles en la institución, como aulas, laboratorios, etc. Desde aquí puedes:
                    </p>
                    <ul class="mb-4">
                        <li>Registrar un nuevo espacio usando el botón <strong>Registrar Espacio</strong>.</li>
                        <li>Consultar la lista de espacios registrados.</li>
                        <li>Editar la información de un espacio existente haciendo clic en el ícono de edición.</li>
                        <li>Eliminar un espacio si es necesario, usando el ícono de eliminar.</li>
                    </ul>
                    <div class="text-center mb-4">
                        <img src="/Sistema-de-Gestion-Docente/public/assets/img/manual-imagenes/espacios-modal.png" alt="Modal Espacios" class="img-fluid rounded shadow" style="max-width:800px;width:100%;height:auto;border:2px solid #e0e0e0;">
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="alert alert-secondary">
                                <strong>Botón Modificar:</strong> El ícono de lápiz o editar te permite modificar la información de un espacio existente. Al hacer clic, se abrirá el mismo formulario modal que para agregar, pero con los datos del espacio ya cargados. Realiza los cambios necesarios y guarda para actualizar la información.<br><br>
                                <strong>Botón Eliminar:</strong> El ícono de papelera o eliminar te permite borrar un espacio del sistema. Al hacer clic, se te pedirá una confirmación para evitar eliminaciones accidentales. Una vez confirmado, el espacio será eliminado de la lista y no podrá recuperarse.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>