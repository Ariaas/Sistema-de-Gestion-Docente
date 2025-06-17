<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$permisos = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
?>

<div class="sidebar">
    <nav class="nav flex-column">
        <a href="?pagina=principal" class="nav-link">
            <span class="icon">
                <img src="public/assets/icons/house-solid.svg"></img>
            </span>
            <span class="description">
                Inicio
            </span>
        </a>
        
        <?php if (!empty($permisos['Docentes'])): ?>
            <a href="?pagina=docente" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/user-graduate-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Docente
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Espacios'])): ?>
            <a href="?pagina=espacios" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/building-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Espacio
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Seccion'])): ?>
            <a href="?pagina=seccion" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/book-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Sección
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Trayecto'])): ?>
            <a href="?pagina=trayecto" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/map-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Trayecto
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Unidad Curricular'])): ?>
            <a href="?pagina=uc" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/book-open-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Unidad Curricular
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Horario'])): ?>
            <a href="?pagina=horario" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/calendar-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Horario
                </span>
            </a>
            <a href="?pagina=horariodocente" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/calendar-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Horario Docente
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Malla Curricular'])): ?>
            <a href="?pagina=mallacurricular" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/screwdriver-wrench-solid.svg"></img>
                </span>
                <span class="description">
                    Gestionar Malla Curricular
                </span>
            </a>
        <?php endif; ?>
        <a href="?pagina=config" class="nav-link">
            <span class="icon">
                <img src="public/assets/icons/gear-solid.svg"></img>
            </span>
            <span class="description">
                Administrar Configuración
            </span>
        </a>
        <?php if (
            !empty($permisos['Bitacora']) ||
            !empty($permisos['Usuarios']) ||
            !empty($permisos['Respaldo'])
        ): ?>
            <a href="?pagina=mantenimiento" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/gear-solid.svg"></img>
                </span>
                <span class="description">
                    Administrar Mantenimiento
                </span>
            </a>
        <?php endif; ?>
        <?php if (!empty($permisos['Reportes'])): ?>
            <a href="?pagina=reportes" class="nav-link">
                <span class="icon">
                    <img src="public/assets/icons/chart-bar-solid.svg"></img>
                </span>
                <span class="description">
                    Generar Reportes
                </span>
            </a>
        <?php endif; ?>
        <a href="?pagina=fin" class="nav-link">
            <span class="icon">
                <img src="public/assets/icons/exit-solid.svg"></img>
            </span>
            <span class="description">
                Cerrar Sesión
            </span>
        </a>
    </nav>
</div>