<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$permisos = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
$pagina_actual = $_GET['pagina'] ?? 'principal';

function is_active($paginas, $pagina_actual)
{
    if (in_array($pagina_actual, (array)$paginas)) {
        return 'active';
    }
    return '';
}

// Lógica para el nuevo menú desplegable "Gestión"
$gestion_items = [
    'Docentes' => 'docente',
    'Espacios' => 'espacios',
    'Seccion' => 'seccion',
    'Año' => 'anio',
    'Unidad Curricular' => 'uc',
    'Malla Curricular' => 'mallacurricular'
];

// Comprobar si el usuario tiene permiso para ver algún elemento de "Gestión"
$paginas_gestion = array_values($gestion_items);
$tiene_permiso_gestion = false;
foreach (array_keys($gestion_items) as $permiso) {
    if (!empty($permisos[$permiso])) {
        $tiene_permiso_gestion = true;
        break;
    }
}

// Lógica para el nuevo menú desplegable "Reportes"
$reportes_items = [
    'Reporte Estadístico' => 'reporte',
    'Reporte de Malla' => 'rmalla',
    'Reporte de Transcripción' => 'rtranscripcion',
    'Reporte de U.C.' => 'ruc',
    'Reporte Carga Académica' => 'rcargaAcademica',
    'Reporte Definitivo' => 'rdefinitivo',
    'Reporte Prosecución' => 'rprosecucion',
    'Reporte Horario Docente' => 'rhordocente',
    'Reporte Asignación Aulas' => 'raulaAsignada',
    'Reporte Aulario' => 'raulario'
];

$paginas_reportes = array_values($reportes_items);
$tiene_permiso_reportes = !empty($permisos['Reportes']);

// Lógica para el nuevo menú desplegable "Reportes Estadísticos"
$reportes_estadisticos_items = [
    'Aprobados Directos' => 'Daprobados',
    'Reporte Aprobados' => 'rAprobados',
    'Reporte PER' => 'rPer',
    'Reporte Reprobados' => 'rReprobados',
    'Reporte General' => 'reporteG'
];
$paginas_reportes_estadisticos = array_values($reportes_estadisticos_items);
?>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a href="?pagina=principal" class="navbar-brand d-flex align-items-center">
            <img src="public/assets/img/logo.png" width="32" height="32" class="me-2">
            <span style="font-weight: 600;">Sistema Docente</span>
        </a>

        <!-- Contenedor para móvil: notificaciones y toggler -->
        <div class="d-flex align-items-center d-lg-none ms-auto">
            <!-- Notificaciones para móvil -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificacionesDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="public/assets/icons/bell.svg" alt="Notificaciones" width="24" height="24" style="filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);">
                        <span id="notificacionesBadgeMobile" class="badge bg-danger rounded-pill notification-badge" style="display:none;"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdownMobile" style="width:350px;max-width:90vw;">
                        <li>
                            <div id="notificacionesPanelMobile" style="padding: 1rem; min-height: 120px; text-align: center;">
                                <span class="text-muted">No hay notificaciones.</span>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>

            <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNavbar" aria-controls="sidebarNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="sidebarNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="?pagina=principal" class="nav-link <?php echo is_active('principal', $pagina_actual); ?>">Inicio</a>
                </li>

                <!-- Nuevo Menú Desplegable "Gestión" -->
                <?php if ($tiene_permiso_gestion): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($paginas_gestion, $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($gestion_items as $nombre => $pagina): ?>
                                <?php if (!empty($permisos[$nombre])): ?>
                                    <li><a class="dropdown-item <?php echo is_active($pagina, $pagina_actual); ?>" href="?pagina=<?php echo $pagina; ?>"><?php echo $nombre === 'Unidad Curricular' ? 'Unidades C.' : $nombre; ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                
                <?php if ($tiene_permiso_reportes): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($paginas_reportes, $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($reportes_items as $nombre => $pagina): ?>
                                <li><a class="dropdown-item <?php echo is_active($pagina, $pagina_actual); ?>" href="?pagina=<?php echo $pagina; ?>"><?php echo $nombre; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($tiene_permiso_reportes): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($paginas_reportes_estadisticos, $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Reportes Estadísticos
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($reportes_estadisticos_items as $nombre => $pagina): ?>
                                <li><a class="dropdown-item <?php echo is_active($pagina, $pagina_actual); ?>" href="?pagina=<?php echo $pagina; ?>"><?php echo $nombre; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (!empty($permisos['Horario'])) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active(['horario', 'horariodocente'], $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Horarios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo is_active('horario', $pagina_actual); ?>" href="?pagina=horario">Gestionar Horario</a></li>
                            <li><a class="dropdown-item <?php echo is_active('horariodocente', $pagina_actual); ?>" href="?pagina=horariodocente">Horario Docente</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (!empty($permisos['Bitacora']) || !empty($permisos['Usuarios']) || !empty($permisos['Respaldo']) || !empty($permisos['Configuracion']) || !empty($permisos['Reportes'])) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active(['mantenimiento', 'config', 'reportes'], $pagina_actual); ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Administración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item <?php echo is_active('config', $pagina_actual); ?>" href="?pagina=config">Configuración</a></li>
                            <?php if (!empty($permisos['Configuracion'])): ?>
                                <li><a class="dropdown-item <?php echo is_active('config', $pagina_actual); ?>" href="?pagina=config">Configuración</a></li>
                            <?php endif; ?>
                            <?php if (!empty($permisos['Bitacora']) || !empty($permisos['Usuarios']) || !empty($permisos['Respaldo'])): ?>
                                <li><a class="dropdown-item <?php echo is_active('mantenimiento', $pagina_actual); ?>" href="?pagina=mantenimiento">Mantenimiento</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('preguntas', $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Ayuda
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?php echo is_active('preguntas', $pagina_actual); ?>" href="?pagina=preguntas">Preguntas</a></li>
                    </ul>
                </li>

                <!-- Dropdown de usuario para móvil -->
                <li class="nav-item dropdown d-lg-none">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                            $foto_perfil_mobile = $_SESSION['usu_foto'] ?? 'public/assets/icons/user-circle.svg';
                            $estilo_filtro_mobile = str_contains($foto_perfil_mobile, 'user-circle.svg') ? 'filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);' : '';
                        ?>
                        <img src="<?php echo $foto_perfil_mobile; ?>?v=<?php echo time(); ?>" alt="Foto de perfil" width="24" height="24" class="rounded-circle me-2" style="object-fit: cover; <?php echo $estilo_filtro_mobile; ?>">
                        <strong><?php echo $_SESSION['name'] ?? 'Usuario'; ?></strong>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="userDropdownMobile">
                        <li><a class="dropdown-item" href="?pagina=perfil">Perfil</a></li>
                        <li><a class="dropdown-item" href="?pagina=fin">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- Contenedor para Perfil y Notificaciones en Desktop -->
            <div class="d-none d-lg-flex align-items-center ms-auto">
                <!-- Notificaciones para Desktop -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="public/assets/icons/bell.svg" alt="Notificaciones" width="24" height="24" style="filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);">
                            <span id="notificacionesBadge" class="badge bg-danger rounded-pill notification-badge" style="display:none;"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdown" style="width:350px;max-width:90vw;">
                            <li>
                                <div id="notificacionesPanel" style="padding: 1rem; min-height: 120px; text-align: center;">
                                    <span class="text-muted">No hay notificaciones.</span>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>

                <!-- Dropdown de Usuario -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                                $foto_perfil = $_SESSION['usu_foto'] ?? 'public/assets/icons/user-circle.svg';
                                $estilo_filtro = str_contains($foto_perfil, 'user-circle.svg') ? 'filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);' : '';
                            ?>
                            <img src="<?php echo $foto_perfil; ?>?v=<?php echo time(); ?>" alt="Foto de perfil" width="24" height="24" class="rounded-circle me-2" style="object-fit: cover; <?php echo $estilo_filtro; ?>">
                            <strong><?php echo $_SESSION['name'] ?? 'Usuario'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="?pagina=perfil">Perfil</a></li>
                            <li><a class="dropdown-item" href="?pagina=fin">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>