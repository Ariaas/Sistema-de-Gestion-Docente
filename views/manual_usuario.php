<?php include_once("public/components/head.php"); ?>

<head>
    <title>Manual de Usuario Interactivo</title>
</head>

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
                            <h1 class="display-5 fw-bold mt-2">Manual de Usuario</h1>
                            <p class="text-muted">Guía completa del Sistema de Gestión Docente</p>
                        </div>

                        <div class="card mb-5 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">Índice de Contenidos</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="text-primary">Primeros Pasos</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#intro" class="text-decoration-none d-block p-1">1. Introducción</a></li>
                                            <li><a href="#login" class="text-decoration-none d-block p-1">2. Inicio de Sesión</a></li>
                                            <li><a href="#principal" class="text-decoration-none d-block p-1">3. Panel Principal</a></li>
                                            <li><a href="#navegacion" class="text-decoration-none d-block p-1">4. Navegación</a></li>
                                            <li><a href="#permisos" class="text-decoration-none d-block p-1">5. Sistema de Permisos</a></li>
                                        </ul>
                                        <h6 class="text-primary mt-3">Gestión Principal</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#modulo-docentes" class="text-decoration-none d-block p-1">6. Gestión de Docentes</a></li>
                                            <li><a href="#modulo-espacios" class="text-decoration-none d-block p-1">7. Gestión de Espacios</a></li>
                                            <li><a href="#modulo-uc" class="text-decoration-none d-block p-1">8. Unidades Curriculares</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-primary">Módulos Avanzados</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#modulo-seccion" class="text-decoration-none d-block p-1">9. Gestión de Secciones</a></li>
                                            <li><a href="#modulo-mallacurricular" class="text-decoration-none d-block p-1">10. Malla Curricular</a></li>
                                            <li><a href="#modulo-reportes" class="text-decoration-none d-block p-1">11. Reportes</a></li>
                                        </ul>
                                        <h6 class="text-primary mt-3">Administración</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#modulo-usuario" class="text-decoration-none d-block p-1">12. Gestión de Usuarios</a></li>
                                            <li><a href="#modulo-rol" class="text-decoration-none d-block p-1">13. Roles y Permisos</a></li>
                                            <li><a href="#modulo-bitacora" class="text-decoration-none d-block p-1">14. Bitácora</a></li>
                                            <li><a href="#modulo-backup" class="text-decoration-none d-block p-1">15. Respaldos</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-primary">Configuración</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#modulo-anio" class="text-decoration-none d-block p-1">16. Gestionar Año</a></li>
                                            <li><a href="#modulo-area" class="text-decoration-none d-block p-1">17. Gestionar Área</a></li>
                                            <li><a href="#modulo-categoria" class="text-decoration-none d-block p-1">18. Gestionar Categoría</a></li>
                                            <li><a href="#modulo-coordinacion" class="text-decoration-none d-block p-1">19. Gestionar Coordinación</a></li>
                                            <li><a href="#modulo-eje" class="text-decoration-none d-block p-1">20. Gestionar Eje</a></li>
                                            <li><a href="#modulo-titulo" class="text-decoration-none d-block p-1">21. Gestionar Título</a></li>
                                            <li><a href="#modulo-turno" class="text-decoration-none d-block p-1">22. Gestionar Turno</a></li>
                                        </ul>
                                        <h6 class="text-primary mt-3">Otros</h6>
                                        <ul class="list-unstyled">
                                            <li><a href="#modulo-perfil" class="text-decoration-none d-block p-1">23. Perfil de Usuario</a></li>
                                            <li><a href="#modulo-notificaciones" class="text-decoration-none d-block p-1">24. Notificaciones</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="manualAccordion">

                            <!-- 1. Introducción -->
                            <div class="accordion-item" id="intro">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIntro" aria-expanded="true">
                                        1. Introducción al Sistema
                                    </button>
                                </h2>
                                <div id="collapseIntro" class="accordion-collapse collapse show" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h5>¿Qué es el Sistema de Gestión Docente?</h5>
                                        <p>Es una plataforma integral diseñada para administrar todos los aspectos relacionados con la organización docente de la Universidad Politécnica Territorial Andrés Eloy Blanco.</p>
                                        
                                        <h6 class="text-primary mt-3">Módulos Principales:</h6>
                                        <ul>
                                            <li><strong>Gestión de Docentes:</strong> Administra información del personal docente (datos personales, categoría, dedicación, títulos)</li>
                                            <li><strong>Gestión de Espacios:</strong> Controla aulas, laboratorios y espacios físicos</li>
                                            <li><strong>Unidades Curriculares:</strong> Gestiona asignaturas del programa académico</li>
                                            <li><strong>Secciones y Horarios:</strong> Crea secciones, asigna docentes, espacios y genera horarios</li>
                                            <li><strong>Malla Curricular:</strong> Estructura completa del programa académico</li>
                                            <li><strong>Reportes:</strong> Genera informes de organización docente y estadísticos</li>
                                            <li><strong>Administración:</strong> Gestiona usuarios, roles, permisos y respaldos</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Sistema de Permisos:</strong> El sistema utiliza un control de acceso basado en roles. Cada usuario solo verá y podrá acceder a las funcionalidades autorizadas por su rol asignado.
                                        </div>

                                        <h6 class="text-primary mt-3">Características Destacadas:</h6>
                                        <ul>
                                            <li>Interfaz moderna y responsive</li>
                                            <li>Control granular de permisos por módulo y acción</li>
                                            <li>Bitácora completa de auditoría</li>
                                            <li>Sistema de notificaciones en tiempo real</li>
                                            <li>Generación de reportes en múltiples formatos</li>
                                            <li>Respaldos automáticos de base de datos</li>
                                            <li>Validación de datos con reCAPTCHA</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Login -->
                            <div class="accordion-item" id="login">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin">
                                        2. Inicio de Sesión
                                    </button>
                                </h2>
                                <div id="collapseLogin" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h6 class="text-primary">Acceso al Sistema</h6>
                                        <p>Para ingresar necesitas credenciales válidas proporcionadas por el administrador:</p>
                                        
                                        <ol>
                                            <li>Abre tu navegador web y accede a la URL del sistema</li>
                                            <li>Ingresa tu <strong>nombre de usuario</strong></li>
                                            <li>Ingresa tu <strong>contraseña</strong></li>
                                            <li>Completa el <strong>captcha</strong> de seguridad (reCAPTCHA de Google)</li>
                                            <li>Haz clic en <strong>"Acceder"</strong></li>
                                        </ol>

                                        <div class="alert alert-success">
                                            <strong>Acceso Exitoso:</strong> Si los datos son correctos, serás redirigido automáticamente al Panel de Control.
                                        </div>

                                        <h6 class="text-primary mt-4">Recuperación de Contraseña</h6>
                                        <p>Si olvidaste tu contraseña, puedes recuperarla siguiendo estos pasos:</p>
                                        <ol>
                                            <li>En la pantalla de login, haz clic en <strong>"¿Olvidaste tu contraseña?"</strong></li>
                                            <li>Ingresa tu <strong>nombre de usuario</strong></li>
                                            <li>Completa el captcha de verificación</li>
                                            <li>El sistema enviará un <strong>código de recuperación</strong> a tu correo registrado</li>
                                            <li>Ingresa el código recibido</li>
                                            <li>Define tu <strong>nueva contraseña</strong></li>
                                            <li>Confirma la nueva contraseña</li>
                                        </ol>

                                        <div class="alert alert-warning">
                                            <strong>Seguridad:</strong> El sistema tiene protección contra intentos de acceso no autorizado. Las sesiones expiran automáticamente por inactividad para proteger tu información.
                                        </div>

                                        <div class="alert alert-danger">
                                            <strong>Problemas de Acceso:</strong> Si no puedes acceder después de varios intentos, contacta al administrador del sistema. No compartas tus credenciales con nadie.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Panel Principal -->
                            <div class="accordion-item" id="principal">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrincipal">
                                        3. Panel Principal
                                    </button>
                                </h2>
                                <div id="collapsePrincipal" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h6 class="text-primary">Vista General</h6>
                                        <p>Al iniciar sesión exitosamente, accedes al <strong>Panel de Control</strong> que muestra:</p>
                                        
                                        <h6 class="text-primary mt-3">Elementos de la Interfaz:</h6>
                                        <ul>
                                            <li><strong>Barra de Navegación Superior:</strong>
                                                <ul>
                                                    <li>Logo y nombre del sistema (clic para volver al inicio)</li>
                                                    <li>Menú <strong>Inicio</strong></li>
                                                    <li>Menú <strong>Gestionar</strong> (Docentes, Espacios, Sección, UC, Malla)</li>
                                                    <li>Menú <strong>Administrar</strong> (Configurar, Mantenimiento)</li>
                                                    <li>Menú <strong>Gestionar Reportes</strong> (Organización Docente, Estadísticos)</li>
                                                    <li>Menú <strong>Ayuda</strong> (Manual de Usuario)</li>
                                                    <li>Icono de <strong>Notificaciones</strong> (con contador de pendientes)</li>
                                                    <li>Menú de <strong>Usuario</strong> (Foto de perfil, Perfil, Cerrar Sesión)</li>
                                                </ul>
                                            </li>
                                            <li><strong>Área de Contenido:</strong> Tarjetas de acceso rápido a módulos según tus permisos</li>
                                            <li><strong>Pie de Página:</strong> Información de derechos reservados</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Personalización:</strong> Los menús y tarjetas visibles dependen de tu rol y permisos. No todos los usuarios ven las mismas opciones.
                                        </div>

                                        <h6 class="text-primary mt-3">Tarjetas de Acceso Rápido:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul>
                                                    <li><strong>Docentes:</strong> Gestión del personal docente</li>
                                                    <li><strong>Espacios:</strong> Administración de aulas y laboratorios</li>
                                                    <li><strong>Sección:</strong> Creación y gestión de secciones</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul>
                                                    <li><strong>Unidad Curricular:</strong> Gestión de asignaturas</li>
                                                    <li><strong>Malla Curricular:</strong> Estructura académica</li>
                                                    <li><strong>Reportes:</strong> Generación de informes</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="alert alert-success">
                                            <strong>Ayuda Rápida:</strong> Puedes acceder a este manual en cualquier momento desde <strong>Menú Ayuda → Manual de Usuario</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Navegación -->
                            <div class="accordion-item" id="navegacion">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNavegacion">
                                        4. Navegación en el Sistema
                                    </button>
                                </h2>
                                <div id="collapseNavegacion" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h6 class="text-primary">Estructura de Menús</h6>
                                        
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <strong>Menú "Gestionar"</strong>
                                            </div>
                                            <div class="card-body">
                                                <p>Acceso a los módulos principales de gestión:</p>
                                                <ul>
                                                    <li><strong>Gestionar Docente:</strong> CRUD completo de docentes</li>
                                                    <li><strong>Gestionar Espacio:</strong> Administración de espacios físicos</li>
                                                    <li><strong>Gestionar Seccion:</strong> Creación de secciones y horarios</li>
                                                    <li><strong>Gestionar Unidad Curricular:</strong> Gestión de asignaturas</li>
                                                    <li><strong>Gestionar Malla Curricular:</strong> Estructura del programa</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <strong>Menú "Administrar"</strong>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Submenu Configurar:</strong></p>
                                                <ul>
                                                    <li>Gestionar Año</li>
                                                    <li>Gestionar Coordinación</li>
                                                    <li>Gestionar Área</li>
                                                    <li>Gestionar Categoría</li>
                                                    <li>Gestionar Eje Integrador</li>
                                                    <li>Gestionar Título</li>
                                                    <li>Gestionar Prosecusión</li>
                                                    <li>Gestionar Turno</li>
                                                </ul>
                                                <p><strong>Submenu Mantenimiento:</strong></p>
                                                <ul>
                                                    <li>Gestionar Usuario</li>
                                                    <li>Gestionar Rol</li>
                                                    <li>Gestionar Bitacora</li>
                                                    <li>Gestionar Respaldo</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <strong>Menú "Gestionar Reportes"</strong>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Reportes Organización Docente:</strong></p>
                                                <ul>
                                                    <li>Reporte Unidad Curricular</li>
                                                    <li>Reporte de horarios</li>
                                                    <li>Reporte de transcripción por fase</li>
                                                    <li>Reporte carga académica</li>
                                                    <li>Reporte definitivo EMTIC por fase</li>
                                                    <li>Reporte aulas asignadas</li>
                                                    <li>Reporte de prosecución</li>
                                                    <li>Reporte de malla</li>
                                                    <li>Reporte de OD</li>
                                                    <li>Reporte de cuenta cupos</li>
                                                </ul>
                                                <p><strong>Reportes Estadísticos:</strong></p>
                                                <ul>
                                                    <li>Aprobados Directos</li>
                                                    <li>Reporte Aprobados</li>
                                                    <li>Reporte PER</li>
                                                    <li>Reporte Reprobados</li>
                                                    <li>Reporte General</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="alert alert-primary">
                                            <strong>Tip de Navegación:</strong> Usa el botón "Inicio" en la barra superior para volver al panel principal en cualquier momento.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Sistema de Permisos -->
                            <div class="accordion-item" id="permisos">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePermisos">
                                        5. Sistema de Permisos
                                    </button>
                                </h2>
                                <div id="collapsePermisos" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h6 class="text-primary">¿Cómo Funcionan los Permisos?</h6>
                                        <p>El sistema implementa un control de acceso basado en <strong>Roles y Permisos Granulares</strong>:</p>
                                        
                                        <ul>
                                            <li><strong>Rol:</strong> Conjunto de permisos asignado a un usuario (Ej: Administrador, Coordinador, Secretaria)</li>
                                            <li><strong>Módulo:</strong> Área funcional del sistema (Ej: Docentes, Espacios, Reportes)</li>
                                            <li><strong>Acción:</strong> Operación específica dentro de un módulo</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Tipos de Acciones:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Acción</th>
                                                        <th>Descripción</th>
                                                        <th>Ejemplo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Registrar</strong></td>
                                                        <td>Crear nuevos registros</td>
                                                        <td>Agregar un nuevo docente</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Modificar</strong></td>
                                                        <td>Editar registros existentes</td>
                                                        <td>Actualizar datos de un docente</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Eliminar / Desactivar</strong></td>
                                                        <td>Borrar o desactivar registros</td>
                                                        <td>Eliminar o desactivar un espacio</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Consultar</strong></td>
                                                        <td>Ver información (implícito)</td>
                                                        <td>Visualizar lista de docentes</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Seguridad:</strong> Si intentas acceder a un módulo sin permisos, serás redirigido al panel principal. Los botones de acciones no autorizadas aparecen deshabilitados.
                                        </div>

                                        <h6 class="text-primary mt-3">Rol Administrador:</h6>
                                        <p>El rol <strong>"Administrador"</strong> tiene características especiales:</p>
                                        <ul>
                                            <li>Acceso completo a todos los módulos</li>
                                            <li>Todas las acciones habilitadas</li>
                                            <li>Sus permisos NO pueden ser modificados (protección del sistema)</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Nota:</strong> Para ver o modificar permisos de un rol, ve a <strong>Administrar → Mantenimiento → Gestionar Rol</strong> y haz clic en "Asignar Permisos".
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Gestión de Docentes -->
                            <div class="accordion-item" id="modulo-docentes">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocentes">
                                        6. Gestión de Docentes
                                    </button>
                                </h2>
                                <div id="collapseDocentes" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar → Gestionar Docente
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Módulo central para administrar toda la información del personal docente de la institución.</p>

                                        <h6 class="text-primary mt-3">Información que se Gestiona:</h6>
                                        <ul>
                                            <li><strong>Identificación:</strong> Prefijo (V/E) + Cédula (7-8 dígitos)</li>
                                            <li><strong>Datos Personales:</strong> Apellido, Nombre, Correo Electrónico</li>
                                            <li><strong>Categoría Docente:</strong> Instructor, Asistente, Agregado, Asociado, Titular</li>
                                            <li><strong>Dedicación:</strong> Exclusiva, Tiempo Completo, Medio Tiempo, Tiempo Convencional</li>
                                            <li><strong>Condición:</strong> Ordinario, Contratado, Jubilado</li>
                                            <li><strong>Títulos Académicos:</strong> Múltiples títulos (configurados previamente)</li>
                                            <li><strong>Coordinación:</strong> Coordinación asignada (opcional)</li>
                                            <li><strong>Estado:</strong> Activo / Inactivo</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Registrar un Docente:</h6>
                                        <ol>
                                            <li>Haz clic en el botón <strong>"Registrar Docente"</strong></li>
                                            <li>Se abrirá un formulario modal con varios pasos</li>
                                            <li><strong>Paso 1 - Datos Básicos:</strong>
                                                <ul>
                                                    <li>Selecciona prefijo de cédula (V o E)</li>
                                                    <li>Ingresa número de cédula (solo números, 7-8 dígitos)</li>
                                                    <li>Ingresa apellido y nombre</li>
                                                    <li>Ingresa correo electrónico válido</li>
                                                </ul>
                                            </li>
                                            <li><strong>Paso 2 - Información Académica:</strong>
                                                <ul>
                                                    <li>Selecciona categoría docente</li>
                                                    <li>Selecciona dedicación</li>
                                                    <li>Selecciona condición</li>
                                                    <li>Asigna títulos académicos (múltiples)</li>
                                                    <li>Asigna coordinación (opcional)</li>
                                                </ul>
                                            </li>
                                            <li>Haz clic en <strong>"Guardar"</strong></li>
                                            <li>El sistema validará los datos y mostrará mensaje de confirmación</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Modificar un Docente:</h6>
                                        <ol>
                                            <li>Localiza el docente en la tabla</li>
                                            <li>Haz clic en el botón de <strong>edición</strong> (ícono de lápiz)</li>
                                            <li>Se abrirá el formulario con los datos actuales cargados</li>
                                            <li>Modifica los campos necesarios</li>
                                            <li>Haz clic en <strong>"Actualizar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Desactivar un Docente:</h6>
                                        <ol>
                                            <li>Localiza el docente en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Desactivar</strong> (ícono de apagado/power)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>El docente será desactivado y no aparecerá en las listas activas</li>
                                        </ol>


                                        <h6 class="text-primary mt-3">Ver Datos Adicionales:</h6>
                                        <p>El botón "Ver Datos Adicionales" muestra información complementaria:</p>
                                        <ul>
                                            <li><strong>Horas de Actividad:</strong> Distribución de horas por categorías:
                                                <ul>
                                                    <li>Horas Académicas</li>
                                                    <li>Horas de Creación Intelectual</li>
                                                    <li>Horas de Investigación</li>
                                                    <li>Horas Administrativas</li>
                                                </ul>
                                            </li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Funciones de la Tabla:</h6>
                                        <ul>
                                            <li><strong>Búsqueda:</strong> Campo de búsqueda rápida en todas las columnas</li>
                                            <li><strong>Ordenamiento:</strong> Clic en encabezados para ordenar ascendente/descendente</li>
                                            <li><strong>Paginación:</strong> Navegación por páginas de resultados</li>
                                            <li><strong>Columnas visibles:</strong> Cédula, Apellido, Nombre, Correo, Categoría, Dedicación, Condición, Estado, Acciones</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Tip:</strong> Antes de registrar docentes, asegúrate de tener configurados los catálogos de Títulos, Categorías y Coordinaciones en el módulo de Configuración.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos específicos de <em>registrar</em>, <em>modificar</em> o <em>eliminar</em> en el módulo Docentes para realizar estas acciones.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Gestión de Espacios -->
                            <div class="accordion-item" id="modulo-espacios">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEspacios">
                                        7. Gestión de Espacios
                                    </button>
                                </h2>
                                <div id="collapseEspacios" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar → Gestionar Espacio
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Administra los espacios físicos disponibles en la institución: aulas, laboratorios, talleres, auditorios, etc.</p>

                                        <h6 class="text-primary mt-3">Información que se Gestiona:</h6>
                                        <ul>
                                            <li><strong>Nombre del Espacio:</strong> Identificación única (Ej: Aula 101, Lab. Computación)</li>
                                            <li><strong>Capacidad:</strong> Número de estudiantes que puede albergar</li>
                                            <li><strong>Tipo:</strong> Clasificación del espacio (Aula, Laboratorio, Taller, Auditorio)</li>
                                            <li><strong>Ubicación:</strong> Edificio o piso donde se encuentra</li>
                                            <li><strong>Estado:</strong> Disponible / No disponible</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Registrar un Espacio:</h6>
                                        <ol>
                                            <li>Haz clic en el botón <strong>"Registrar Espacio"</strong></li>
                                            <li>Ingresa el nombre del espacio</li>
                                            <li>Ingresa la capacidad (número de personas)</li>
                                            <li>Selecciona el tipo de espacio</li>
                                            <li>Haz clic en <strong>"Guardar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Modificar un Espacio:</h6>
                                        <ol>
                                            <li>Localiza el espacio en la tabla</li>
                                            <li>Haz clic en el botón de <strong>editar</strong> (ícono de lápiz)</li>
                                            <li>Modifica los campos necesarios</li>
                                            <li>Haz clic en <strong>"Actualizar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Desactivar un Espacio:</h6>
                                        <ol>
                                            <li>Localiza el espacio en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Desactivar</strong> (ícono de apagado/power)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>El espacio será desactivado y no aparecerá en las listas activas</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Funciones Adicionales:</h6>
                                        <ul>
                                            <li><strong>Consultar:</strong> Visualiza todos los espacios con sus características</li>
                                            <li><strong>Filtrar:</strong> Busca espacios por nombre, tipo o capacidad</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Uso:</strong> Los espacios registrados aquí se utilizan al crear secciones y horarios para asignar aulas a las clases.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Espacio.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Unidades Curriculares -->
                            <div class="accordion-item" id="modulo-uc">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUC">
                                        8. Unidades Curriculares (UC)
                                    </button>
                                </h2>
                                <div id="collapseUC" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar → Gestionar Unidad Curricular
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Gestiona las unidades curriculares (asignaturas) del programa académico.</p>

                                        <h6 class="text-primary mt-3">Información que se Gestiona:</h6>
                                        <ul>
                                            <li><strong>Código:</strong> Identificador único (Ej: MAT101, FIS201)</li>
                                            <li><strong>Nombre:</strong> Nombre de la asignatura</li>
                                            <li><strong>Unidades de Crédito:</strong> Valor crediticio de la UC</li>
                                            <li><strong>Trayecto:</strong> Nivel académico (1, 2, 3, 4)</li>
                                            <li><strong>Área:</strong> Área de conocimiento (debe estar configurada previamente)</li>
                                            <li><strong>Fase:</strong> Fase dentro del trayecto</li>
                                            <li><strong>Eje Integrador:</strong> Eje temático asociado (debe estar configurado)</li>
                                            <li><strong>Estado:</strong> Activo / Inactivo</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Registrar una UC:</h6>
                                        <ol>
                                            <li>Haz clic en el botón <strong>"Registrar Unidad Curricular"</strong></li>
                                            <li>Ingresa el código de la UC</li>
                                            <li>Ingresa el nombre de la UC</li>
                                            <li>Ingresa las unidades de crédito</li>
                                            <li>Selecciona el trayecto</li>
                                            <li>Selecciona el área de conocimiento</li>
                                            <li>Selecciona la fase</li>
                                            <li>Selecciona el eje integrador</li>
                                            <li>Haz clic en <strong>"Guardar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Modificar una UC:</h6>
                                        <ol>
                                            <li>Localiza la UC en la tabla</li>
                                            <li>Haz clic en el botón de <strong>editar</strong> (ícono de lápiz)</li>
                                            <li>Modifica los campos necesarios</li>
                                            <li>Haz clic en <strong>"Actualizar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Desactivar una UC:</h6>
                                        <ol>
                                            <li>Localiza la UC en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Desactivar</strong> (ícono de apagado/power)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>La UC será desactivada y no aparecerá en las listas activas</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Asignar UC a Docentes:</h6>
                                        <p>Puedes vincular unidades curriculares con docentes capacitados para impartirlas:</p>
                                        <ol>
                                            <li>Selecciona la UC en la tabla</li>
                                            <li>Haz clic en <strong>"Asignar Docentes"</strong></li>
                                            <li>Selecciona los docentes que pueden impartir esta UC</li>
                                            <li>Guarda la asignación</li>
                                        </ol>

                                        <div class="alert alert-warning">
                                            <strong>Importante:</strong> El sistema valida que existan Ejes y Áreas registradas antes de crear UCs. Configúralos primero en el módulo de Configuración.
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Unidad Curricular.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Gestión de Secciones -->
                            <div class="accordion-item" id="modulo-seccion">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeccion">
                                        9. Gestión de Secciones
                                    </button>
                                </h2>
                                <div id="collapseSeccion" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar → Gestionar Seccion
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Administra las secciones académicas y sus horarios. Este es uno de los módulos más complejos del sistema.</p>

                                        <h6 class="text-primary mt-3">Información que se Gestiona:</h6>
                                        <ul>
                                            <li><strong>Código de Sección:</strong> Identificador único generado automáticamente</li>
                                            <li><strong>Año académico:</strong> Año y tipo (Regular, Intensivo, etc.)</li>
                                            <li><strong>Cantidad de estudiantes:</strong> Cupos disponibles</li>
                                            <li><strong>Tipo:</strong> Clasificación de la sección</li>
                                            <li><strong>Horarios:</strong> Días, horas, docentes, UCs y espacios asignados</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Registrar una Sección:</h6>
                                        <p><strong>Paso 1 - Crear la Sección:</strong></p>
                                        <ol>
                                            <li>Haz clic en <strong>"Registrar Sección"</strong></li>
                                            <li>Selecciona el año académico</li>
                                            <li>Ingresa la cantidad de estudiantes</li>
                                            <li>Haz clic en <strong>"Siguiente"</strong></li>
                                        </ol>

                                        <p><strong>Paso 2 - Asignar Horarios:</strong></p>
                                        <ol>
                                            <li>Selecciona el docente</li>
                                            <li>Selecciona la unidad curricular</li>
                                            <li>Selecciona el espacio (aula)</li>
                                            <li>Selecciona el día de la semana</li>
                                            <li>Selecciona la hora de inicio y fin</li>
                                            <li>Haz clic en <strong>"Agregar Horario"</strong></li>
                                            <li>Repite para agregar más horarios a la sección</li>
                                            <li>Haz clic en <strong>"Guardar Sección"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Unir Horarios:</h6>
                                        <p>Permite combinar múltiples horarios en una sola sección:</p>
                                        <ol>
                                            <li>Haz clic en <strong>"Unir Horarios"</strong></li>
                                            <li>Selecciona los horarios que deseas unir</li>
                                            <li>Confirma la operación</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Duplicar Sección:</h6>
                                        <p>Copia una sección existente para crear una nueva rápidamente:</p>
                                        <ol>
                                            <li>Selecciona la sección en la tabla</li>
                                            <li>Haz clic en <strong>"Duplicar"</strong></li>
                                            <li>Modifica los datos necesarios</li>
                                            <li>Guarda la nueva sección</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Eliminar una Sección:</h6>
                                        <ol>
                                            <li>Localiza la sección en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Eliminar</strong> (ícono de papelera)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>La sección y todos sus horarios serán eliminados permanentemente</li>
                                        </ol>

                                        <div class="alert alert-danger">
                                            <strong>Advertencia:</strong> Eliminar una sección es permanente y eliminará todos los horarios asociados.
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Nota:</strong> El sistema valida conflictos de horarios automáticamente para evitar que un docente o espacio esté asignado en el mismo horario.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Seccion.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Malla Curricular -->
                            <div class="accordion-item" id="modulo-mallacurricular">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMalla">
                                        10. Malla Curricular
                                    </button>
                                </h2>
                                <div id="collapseMalla" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar → Gestionar Malla Curricular
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Administra la estructura completa del programa académico, organizando las unidades curriculares por trayectos y fases.</p>

                                        <h6 class="text-primary mt-3">Funciones:</h6>
                                        <ul>
                                            <li><strong>Visualizar Malla:</strong> Consulta la estructura completa organizada por trayectos</li>
                                            <li><strong>Agregar UCs:</strong> Incorpora unidades curriculares a la malla</li>
                                            <li><strong>Organizar:</strong> Define el orden y distribución de las UCs</li>
                                            <li><strong>Modificar:</strong> Actualiza la estructura según cambios curriculares</li>
                                            <li><strong>Desactivar:</strong> Desactiva UCs de la malla sin eliminarlas permanentemente</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Gestionar la Malla:</h6>
                                        <ol>
                                            <li>Accede al módulo de Malla Curricular</li>
                                            <li>Visualiza las UCs organizadas por trayecto y fase</li>
                                            <li>Para agregar una UC, selecciónala de la lista disponible</li>
                                            <li>Para modificar el orden, usa las opciones de organización</li>
                                            <li>Para desactivar una UC, usa el botón correspondiente</li>
                                        </ol>

                                        <div class="alert alert-info">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Malla Curricular.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Reportes -->
                            <div class="accordion-item" id="modulo-reportes">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReportes">
                                        11. Reportes
                                    </button>
                                </h2>
                                <div id="collapseReportes" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Gestionar Reportes
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Genera reportes de organización docente y estadísticos para análisis y toma de decisiones.</p>

                                        <h6 class="text-primary mt-3">Reportes de Organización Docente:</h6>
                                        <ul>
                                            <li>Reporte Unidad Curricular</li>
                                            <li>Reporte de horarios</li>
                                            <li>Reporte de transcripción por fase</li>
                                            <li>Reporte carga académica</li>
                                            <li>Reporte definitivo EMTIC por fase</li>
                                            <li>Reporte aulas asignadas</li>
                                            <li>Reporte de prosecución</li>
                                            <li>Reporte de malla curricular</li>
                                            <li>Reporte de OD (Organización Docente)</li>
                                            <li>Reporte de cuenta cupos</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Reportes Estadísticos:</h6>
                                        <ul>
                                            <li>Aprobados Directos</li>
                                            <li>Reporte Aprobados</li>
                                            <li>Reporte PER</li>
                                            <li>Reporte Reprobados</li>
                                            <li>Reporte General</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Generar un Reporte:</h6>
                                        <ol>
                                            <li>Selecciona el tipo de reporte que necesitas</li>
                                            <li>Aplica los filtros disponibles (año, trayecto, fase, etc.)</li>
                                            <li>Haz clic en <strong>"Generar"</strong></li>
                                            <li>Visualiza el reporte en pantalla</li>
                                            <li>Exporta en formato PDF o Excel según disponibilidad</li>
                                        </ol>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos de "registrar" en el módulo Reportes.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 12. Gestión de Usuarios -->
                            <div class="accordion-item" id="modulo-usuario">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsuario">
                                        12. Gestión de Usuarios
                                    </button>
                                </h2>
                                <div id="collapseUsuario" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Administrar → Mantenimiento → Gestionar Usuario
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Gestiona las cuentas de acceso al sistema.</p>

                                        <h6 class="text-primary mt-3">Información que se Gestiona:</h6>
                                        <ul>
                                            <li><strong>Nombre:</strong> Nombre completo del usuario</li>
                                            <li><strong>Correo:</strong> Email para notificaciones</li>
                                            <li><strong>Rol:</strong> Rol asignado que determina los permisos</li>
                                            <li><strong>Docente Asignado:</strong> Vinculación opcional con un registro de docente</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Registrar un Usuario:</h6>
                                        <ol>
                                            <li>Haz clic en <strong>"Registrar Usuario"</strong></li>
                                            <li>Ingresa el nombre completo</li>
                                            <li>Ingresa el correo electrónico</li>
                                            <li>Selecciona el rol</li>
                                            <li>Opcionalmente, vincula con un docente</li>
                                            <li>Haz clic en <strong>"Guardar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Modificar un Usuario:</h6>
                                        <ol>
                                            <li>Localiza el usuario en la tabla</li>
                                            <li>Haz clic en el botón de <strong>editar</strong> (ícono de lápiz)</li>
                                            <li>Modifica los campos necesarios</li>
                                            <li>Haz clic en <strong>"Actualizar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Eliminar un Usuario:</h6>
                                        <ol>
                                            <li>Localiza el usuario en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Eliminar</strong> (ícono de papelera)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>El usuario será eliminado permanentemente</li>
                                        </ol>

                                        <div class="alert alert-danger">
                                            <strong>Advertencia:</strong> La eliminación de usuarios es permanente y no se puede deshacer.
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Nota:</strong> El sistema genera automáticamente una contraseña temporal que debe ser cambiada en el primer acceso.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Usuario.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Roles y Permisos -->
                            <div class="accordion-item" id="modulo-rol">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRol">
                                        13. Roles y Permisos
                                    </button>
                                </h2>
                                <div id="collapseRol" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Administrar → Mantenimiento → Gestionar Rol
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Define roles y asigna permisos granulares para controlar el acceso a las funcionalidades del sistema.</p>

                                        <h6 class="text-primary mt-3">Cómo Crear un Rol:</h6>
                                        <ol>
                                            <li>Haz clic en <strong>"Registrar Rol"</strong></li>
                                            <li>Ingresa el nombre del rol (Ej: Coordinador, Secretaria)</li>
                                            <li>Haz clic en <strong>"Guardar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Modificar un Rol:</h6>
                                        <ol>
                                            <li>Localiza el rol en la tabla</li>
                                            <li>Haz clic en el botón de <strong>editar</strong> (ícono de lápiz)</li>
                                            <li>Modifica el nombre del rol</li>
                                            <li>Haz clic en <strong>"Actualizar"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Eliminar un Rol:</h6>
                                        <ol>
                                            <li>Localiza el rol en la tabla</li>
                                            <li>Haz clic en el botón de <strong>Eliminar</strong> (ícono de papelera)</li>
                                            <li>Confirma la acción en el diálogo de confirmación</li>
                                            <li>El rol será eliminado permanentemente</li>
                                        </ol>

                                        <div class="alert alert-danger mb-3">
                                            <strong>Advertencia:</strong> No puedes eliminar roles que estén asignados a usuarios activos.
                                        </div>

                                        <h6 class="text-primary mt-3">Cómo Asignar Permisos:</h6>
                                        <ol>
                                            <li>Selecciona el rol en la tabla</li>
                                            <li>Haz clic en <strong>"Asignar Permisos"</strong></li>
                                            <li>Se mostrará una lista de todos los módulos disponibles</li>
                                            <li>Para cada módulo, selecciona las acciones permitidas:
                                                <ul>
                                                    <li>Registrar</li>
                                                    <li>Modificar</li>
                                                    <li>Eliminar</li>
                                                    <li>Consultar (implícito)</li>
                                                </ul>
                                            </li>
                                            <li>Haz clic en <strong>"Guardar Permisos"</strong></li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Módulos Disponibles:</h6>
                                        <p>Docentes, Espacio, Seccion, Unidad Curricular, Malla Curricular, Reportes, Usuario, Bitacora, Backup, Año, Coordinación, Área, Categoría, Eje, Título, Turno, y más.</p>

                                        <div class="alert alert-danger">
                                            <strong>Importante:</strong> El rol "Administrador" tiene permisos completos y NO puede ser modificado ni eliminado (protección del sistema).
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Usuario.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 14. Bitácora -->
                            <div class="accordion-item" id="modulo-bitacora">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBitacora">
                                        14. Bitácora
                                    </button>
                                </h2>
                                <div id="collapseBitacora" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Administrar → Mantenimiento → Gestionar Bitacora
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Sistema de auditoría que registra todas las operaciones realizadas en el sistema.</p>

                                        <h6 class="text-primary mt-3">Información Registrada:</h6>
                                        <ul>
                                            <li><strong>Usuario:</strong> Quién realizó la acción</li>
                                            <li><strong>Acción:</strong> Tipo de operación (registrar, modificar, eliminar)</li>
                                            <li><strong>Módulo:</strong> Dónde se realizó la acción</li>
                                            <li><strong>Fecha y Hora:</strong> Timestamp de la operación</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Funciones:</h6>
                                        <ul>
                                            <li><strong>Consultar:</strong> Visualiza el historial completo de operaciones</li>
                                            <li><strong>Filtrar:</strong> Busca por usuario, fecha, módulo o tipo de acción</li>
                                            <li><strong>Exportar:</strong> Genera reportes de auditoría</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Uso:</strong> La bitácora es fundamental para auditorías, seguimiento de cambios y resolución de problemas.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Bitacora.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 15. Respaldos -->
                            <div class="accordion-item" id="modulo-backup">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup">
                                        15. Respaldos (Backup)
                                    </button>
                                </h2>
                                <div id="collapseBackup" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Administrar → Mantenimiento → Gestionar Respaldo
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Protege la información del sistema mediante copias de seguridad de la base de datos.</p>

                                        <h6 class="text-primary mt-3">Cómo Crear un Backup:</h6>
                                        <ol>
                                            <li>Haz clic en <strong>"Crear Backup"</strong></li>
                                            <li>El sistema genera una copia completa de la base de datos</li>
                                            <li>Se crea un archivo con timestamp (fecha y hora)</li>
                                            <li>Aparecerá en la lista de backups disponibles</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Cómo Restaurar un Backup:</h6>
                                        <ol>
                                            <li>Selecciona el backup en la lista</li>
                                            <li>Haz clic en <strong>"Restaurar"</strong></li>
                                            <li>Confirma la operación (requiere confirmación adicional)</li>
                                            <li>El sistema restaurará los datos desde ese punto</li>
                                        </ol>

                                        <h6 class="text-primary mt-3">Otras Funciones:</h6>
                                        <ul>
                                            <li><strong>Descargar:</strong> Descarga el archivo de backup al equipo local</li>
                                            <li><strong>Eliminar:</strong> Elimina backups antiguos para liberar espacio</li>
                                        </ul>

                                        <div class="alert alert-danger">
                                            <strong>Advertencia:</strong> Restaurar un backup sobrescribirá todos los datos actuales. Asegúrate de crear un backup actual antes de restaurar uno anterior.
                                        </div>

                                        <div class="alert alert-info">
                                            <strong>Recomendación:</strong> Realiza backups periódicos, especialmente antes de operaciones masivas o cambios importantes.
                                        </div>

                                        <div class="alert alert-warning">
                                            <strong>Permisos Requeridos:</strong> Necesitas permisos en el módulo Backup.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Módulos de Configuración -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConfig">
                                        16. Módulos de Configuración
                                    </button>
                                </h2>
                                <div id="collapseConfig" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú Administrar → Configurar
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Estos módulos permiten configurar los catálogos y parámetros base del sistema. Son fundamentales para el funcionamiento correcto de otros módulos.</p>

                                        <h6 class="text-primary mt-3">Módulos Disponibles:</h6>
                                        
                                        <div class="mb-3">
                                            <strong>Gestionar Año:</strong>
                                            <p>Administra años académicos (Ej: 2024 Regular, 2024 Intensivo). Necesario para crear secciones.</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Coordinación:</strong>
                                            <p>Define coordinaciones académicas o administrativas que pueden ser asignadas a docentes.</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Área:</strong>
                                            <p>Crea áreas de conocimiento para clasificar las unidades curriculares (Ej: Matemáticas, Ciencias Sociales).</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Categoría:</strong>
                                            <p>Define categorías docentes (Instructor, Asistente, Agregado, Asociado, Titular).</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Eje Integrador:</strong>
                                            <p>Administra ejes temáticos transversales del programa académico.</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Título:</strong>
                                            <p>Registra títulos académicos que pueden ser asignados a docentes (Licenciado, Magíster, Doctor, etc.).</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Turno:</strong>
                                            <p>Define turnos académicos (Mañana, Tarde, Noche).</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Gestionar Prosecusión:</strong>
                                            <p>Administra reglas de prosecución académica para estudiantes.</p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Cargar Notas Definitivas:</strong>
                                            <p>Permite a docentes subir archivos con notas finales de estudiantes.</p>
                                        </div>

                                        <div class="alert alert-warning mt-3">
                                            <strong>Importante:</strong> Configura estos módulos ANTES de registrar docentes, UCs y secciones, ya que muchos dependen de estos catálogos.
                                        </div>

                                        <h6 class="text-primary mt-4">Operaciones Comunes:</h6>
                                        <p>Todos estos módulos comparten operaciones similares:</p>
                                        <ul>
                                            <li><strong>Registrar:</strong> Agregar nuevos elementos al catálogo</li>
                                            <li><strong>Modificar:</strong> Editar elementos existentes (ícono de lápiz)</li>
                                            <li><strong>Desactivar:</strong> Desactivar elementos no utilizados (no se eliminan permanentemente)</li>
                                            <li><strong>Eliminar:</strong> Eliminar elementos del catálogo (ícono de papelera)</li>
                                            <li><strong>Consultar:</strong> Visualizar la lista completa</li>
                                        </ul>

                                        <div class="alert alert-warning mt-3">
                                            <strong>Nota sobre Eliminación:</strong> Los módulos de configuración (Año, Título, Turno, etc.) usan eliminación permanente con ícono de papelera. Solo elimina elementos que no estén siendo utilizados por otros registros.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 17. Perfil de Usuario -->
                            <div class="accordion-item" id="modulo-perfil">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePerfil">
                                        17. Perfil de Usuario
                                    </button>
                                </h2>
                                <div id="collapsePerfil" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Menú superior (foto de perfil) → Perfil
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Permite a cada usuario gestionar su información personal y configuración de cuenta.</p>

                                        <h6 class="text-primary mt-3">Funciones Disponibles:</h6>
                                        <ul>
                                            <li><strong>Actualizar Datos Personales:</strong> Modifica nombre, correo y otros datos</li>
                                            <li><strong>Cambiar Contraseña:</strong> Actualiza tu contraseña de acceso de forma segura</li>
                                            <li><strong>Subir Foto de Perfil:</strong> Personaliza tu foto que aparece en la barra superior</li>
                                            <li><strong>Ver Rol Asignado:</strong> Consulta tu rol y permisos actuales</li>
                                            <li><strong>Ver Información del Sistema:</strong> Datos de tu cuenta y última actividad</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Cómo Cambiar la Contraseña:</h6>
                                        <ol>
                                            <li>Accede a tu perfil</li>
                                            <li>Busca la sección "Cambiar Contraseña"</li>
                                            <li>Ingresa tu contraseña actual</li>
                                            <li>Ingresa la nueva contraseña</li>
                                            <li>Confirma la nueva contraseña</li>
                                            <li>Haz clic en <strong>"Actualizar Contraseña"</strong></li>
                                        </ol>

                                        <div class="alert alert-info">
                                            <strong>Seguridad:</strong> Se recomienda usar contraseñas seguras con combinación de letras, números y símbolos.
                                        </div>

                                        <div class="alert alert-success">
                                            <strong>Acceso:</strong> Todos los usuarios tienen acceso a su propio perfil independientemente de su rol.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 18. Sistema de Notificaciones -->
                            <div class="accordion-item" id="modulo-notificaciones">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotificaciones">
                                        18. Sistema de Notificaciones
                                    </button>
                                </h2>
                                <div id="collapseNotificaciones" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <div class="alert alert-primary">
                                            <strong>Ruta:</strong> Icono de campana en la barra superior
                                        </div>

                                        <h6 class="text-primary">Descripción</h6>
                                        <p>Mantiene informados a los usuarios sobre eventos importantes del sistema.</p>

                                        <h6 class="text-primary mt-3">Funciones:</h6>
                                        <ul>
                                            <li><strong>Notificaciones en Tiempo Real:</strong> Alertas instantáneas sobre cambios relevantes</li>
                                            <li><strong>Contador de Pendientes:</strong> Badge numérico indica notificaciones no leídas</li>
                                            <li><strong>Historial:</strong> Consulta notificaciones anteriores</li>
                                            <li><strong>Marcar como Leída:</strong> Gestiona el estado de las notificaciones</li>
                                        </ul>

                                        <h6 class="text-primary mt-3">Tipos de Notificaciones:</h6>
                                        <ul>
                                            <li>Cambios en horarios o secciones</li>
                                            <li>Asignaciones nuevas</li>
                                            <li>Actualizaciones del sistema</li>
                                            <li>Alertas administrativas</li>
                                        </ul>

                                        <div class="alert alert-info">
                                            <strong>Acceso:</strong> Disponible para todos los usuarios autenticados.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nota Final -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fs-5 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNota">
                                        Información Adicional
                                    </button>
                                </h2>
                                <div id="collapseNota" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                    <div class="accordion-body">
                                        <h6 class="text-primary">Consejos y Buenas Prácticas</h6>
                                        <ul>
                                            <li>Realiza backups periódicos antes de operaciones masivas</li>
                                            <li>Configura primero los catálogos (Año, Área, Eje, etc.) antes de crear UCs y Secciones</li>
                                            <li>Verifica los permisos de tu rol si no puedes acceder a algún módulo</li>
                                            <li>Usa la función de búsqueda en las tablas para encontrar registros rápidamente</li>
                                            <li>Revisa la bitácora regularmente para auditar cambios importantes</li>
                                            <li>Cambia tu contraseña periódicamente por seguridad</li>
                                        </ul>

                                        <h6 class="text-primary mt-4">Flujo de Trabajo Recomendado</h6>
                                        <ol>
                                            <li><strong>Configuración Inicial:</strong> Configura Año, Área, Eje, Categoría, Título, Coordinación, Turno</li>
                                            <li><strong>Gestión de Recursos:</strong> Registra Espacios (aulas)</li>
                                            <li><strong>Personal:</strong> Registra Docentes con sus datos completos</li>
                                            <li><strong>Académico:</strong> Crea Unidades Curriculares y arma la Malla Curricular</li>
                                            <li><strong>Organización:</strong> Crea Secciones y asigna horarios</li>
                                            <li><strong>Administración:</strong> Crea Usuarios y asigna Roles según necesidad</li>
                                            <li><strong>Reportes:</strong> Genera reportes para análisis y toma de decisiones</li>
                                        </ol>

                                        <h6 class="text-primary mt-4">Soporte y Ayuda</h6>
                                        <div class="alert alert-success">
                                            <p><strong>Si tienes dudas o problemas con el sistema:</strong></p>
                                            <ul class="mb-0">
                                                <li>Consulta este manual desde <strong>Ayuda → Manual de Usuario</strong></li>
                                                <li>Contacta al administrador del sistema</li>
                                                <li>Verifica que tengas los permisos necesarios para la operación</li>
                                                <li>Revisa la bitácora para ver el historial de cambios</li>
                                            </ul>
                                        </div>

                                        <div class="text-center mt-4 p-4 bg-light rounded">
                                            <h5 class="text-primary">Sistema de Gestión Docente</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

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