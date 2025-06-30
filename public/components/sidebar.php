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
?>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a href="?pagina=principal" class="navbar-brand d-flex align-items-center">
            <img src="public/assets/img/logo.png" width="32" height="32" class="me-2">
            <span style="font-weight: 600;">Sistema Docente</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNavbar" aria-controls="sidebarNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

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
                            <?php if (!empty($permisos['Reportes'])) : ?>
                                <li><a class="dropdown-item <?php echo is_active('reportes', $pagina_actual); ?>" href="?pagina=reportes">Reportes</a></li>
                            <?php endif; ?>
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
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="public/assets/icons/bell.svg" alt="Notificaciones" width="24" height="24" style="filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);">
                        <span id="notificacionesBadge" class="badge bg-danger" style="display:none;position:absolute;top:8px;right:8px;">!</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdown" style="width:350px;max-width:90vw;">
                        <li>
                            <div id="notificacionesPanel" style="padding: 1rem; min-height: 120px; text-align: center;">
                                <span class="text-muted">No hay notificaciones.</span>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="public/assets/icons/user-circle.svg" alt="" width="24" height="24" class="rounded-circle me-2" style="filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);">
                        <strong><?php echo $_SESSION['username'] ?? 'Usuario'; ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="?pagina=perfil">Perfil</a></li>
                        <li><a class="dropdown-item" href="?pagina=fin">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>