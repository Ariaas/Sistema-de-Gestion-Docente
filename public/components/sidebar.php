<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$permisos_sesion = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
$permisos = array_change_key_case($permisos_sesion, CASE_LOWER);

$pagina_actual = $_GET['pagina'] ?? 'principal';

function is_active($paginas, $pagina_actual)
{
    if (in_array($pagina_actual, (array)$paginas)) {
        return 'active';
    }
    return '';
}

if (!function_exists('tiene_permiso')) {
    function tiene_permiso($modulo, $permisos_array)
    {
        return !empty($permisos_array[strtolower($modulo)]);
    }
}

if (!function_exists('tiene_permiso_accion')) {
    function tiene_permiso_accion($modulo, $accion, $permisos_array)
    {
        $modulo = strtolower($modulo);
        if (isset($permisos_array[$modulo]) && is_array($permisos_array[$modulo])) {
            return in_array($accion, $permisos_array[$modulo]);
        }
        return false;
    }
}


$gestion_items = [
    'Docentes' => 'docente',
    'Espacio' => 'espacios',
    'Seccion' => 'seccion',
    'Unidad Curricular' => 'uc',
    'Malla Curricular' => 'mallacurricular',
    'Horario Docente' => 'horariodocente'
];

$reportes_estadisticos_items = [
    'Aprobados Directos' => 'Daprobados',
    'Reporte Aprobados' => 'rAprobados',
    'Reporte PER' => 'rPer',
    'Reporte Reprobados' => 'rReprobados',
    'Reporte General' => 'reporteG'
];

$mantenimiento_permisos = ['Usuario', 'Rol', 'Bitacora', 'backup'];
$config_permisos = ['Coordinacion', 'Area', 'Categoria', 'Eje', 'Titulo', 'Notas', 'Actividad','año'];

$tiene_permiso_gestion = false;
$docente_asignado = isset($_SESSION['usu_cedula']) && !empty($_SESSION['usu_cedula']);

foreach (array_keys($gestion_items) as $permiso) {
    if (tiene_permiso($permiso, $permisos)) {
        $tiene_permiso_gestion = true;
        break;
    }
}

$tiene_permiso_reportes_estadisticos = tiene_permiso_accion('reportes', 'registrar', $permisos);

$tiene_permiso_config_subitem = false;
foreach ($config_permisos as $permiso) {
    if (tiene_permiso($permiso, $permisos)) {
        $tiene_permiso_config_subitem = true;
        break;
    }
}
if (!$tiene_permiso_config_subitem) {
    $tiene_permiso_config_subitem = tiene_permiso_accion('seccion', 'registrar', $permisos) && tiene_permiso_accion('seccion', 'modificar', $permisos);
}

$tiene_permiso_mantenimiento_subitem = false;
foreach ($mantenimiento_permisos as $permiso) {
    if (tiene_permiso($permiso, $permisos)) {
        $tiene_permiso_mantenimiento_subitem = true;
        break;
    }
}

$tiene_permiso_reportes_subitem = tiene_permiso_accion('reportes', 'registrar', $permisos);

$tiene_permiso_admin = $tiene_permiso_config_subitem || $tiene_permiso_reportes_subitem || $tiene_permiso_mantenimiento_subitem || $docente_asignado;


$paginas_gestion = array_values($gestion_items);
$paginas_reportes_estadisticos = array_values($reportes_estadisticos_items);

?>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a href="?pagina=principal" class="navbar-brand d-flex align-items-center">
            <img src="public/assets/img/logo.png" width="32" height="32" class="me-2">
            <span style="font-weight: 600;">Sistema Docente</span>
        </a>

        <div class="d-flex align-items-center d-lg-none ms-auto">
            <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNavbar" aria-controls="sidebarNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="sidebarNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="?pagina=principal" class="nav-link <?php echo is_active('principal', $pagina_actual); ?>">Inicio</a>
                </li>

                <?php if ($tiene_permiso_gestion): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($paginas_gestion, $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Gestión</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($gestion_items as $nombre => $pagina): ?>
                                <?php if (tiene_permiso($nombre, $permisos)): ?>
                                    <li><a class="dropdown-item <?php echo is_active($pagina, $pagina_actual); ?>" href="?pagina=<?php echo $pagina; ?>"><?php echo $nombre === 'Unidad Curricular' ? 'Unidades C.' : $nombre; ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($tiene_permiso_reportes_estadisticos): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($paginas_reportes_estadisticos, $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes Estadísticos</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($reportes_estadisticos_items as $nombre => $pagina): ?>
                                <li><a class="dropdown-item <?php echo is_active($pagina, $pagina_actual); ?>" href="?pagina=<?php echo $pagina; ?>"><?php echo $nombre; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (tiene_permiso('Horario', $permisos)) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active(['horario', 'horariodocente'], $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Horario</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo is_active('horariodocente', $pagina_actual); ?>" href="?pagina=horariodocente">Horario Docente</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($tiene_permiso_admin) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active(['mantenimiento', 'config', 'reportes', 'archivo'], $pagina_actual); ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Administración</a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <?php if ($docente_asignado): ?>
                                <!-- <li><a class="dropdown-item <?php echo is_active('archivo', $pagina_actual); ?>" href="?pagina=archivo">Resguardar Notas</a></li> -->
                            <?php endif; ?>
                            <?php if ($tiene_permiso_config_subitem): ?>
                                <li><a class="dropdown-item <?php echo is_active('config', $pagina_actual); ?>" href="?pagina=config">Configuración</a></li>
                            <?php endif; ?>
                            <?php if ($tiene_permiso_mantenimiento_subitem): ?>
                                <li><a class="dropdown-item <?php echo is_active('mantenimiento', $pagina_actual); ?>" href="?pagina=mantenimiento">Mantenimiento</a></li>
                            <?php endif; ?>
                            <?php if ($tiene_permiso_reportes_subitem): ?>
                                <li><a class="dropdown-item <?php echo is_active('reportes', $pagina_actual); ?>" href="?pagina=reportes">Reportes</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active(['preguntas', 'manual_usuario'], $pagina_actual); ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Ayuda</a>
                    <ul class="dropdown-menu">
                        
                        <li><a class="dropdown-item <?php echo is_active('manual_usuario', $pagina_actual); ?>" href="?pagina=manual_usuario">Manual de Usuario</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-none d-lg-flex align-items-center ms-auto">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link <?php echo is_active('notificaciones', $pagina_actual); ?>" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="public/assets/icons/bell.svg" alt="Notificaciones" width="24" height="24" style="filter: invert(35%) sepia(30%) saturate(2000%) hue-rotate(200deg);">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificacionesBadge" style="display: none;"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdown" id="notificacionesPanel" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <li><a class="dropdown-item text-center" href="#">Cargando...</a></li>
                        </ul>
                    </li>
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

<script type="text/javascript" src="public/js/notificaciones.js"></script>