<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

$permisos_sesion = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
$permisos = array_change_key_case($permisos_sesion, CASE_LOWER);

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

$puede_registrar = tiene_permiso_accion('area', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('area', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('area', 'eliminar', $permisos);

if (!$puede_registrar && !$puede_modificar && !$puede_eliminar) {
    header('Location: ?pagina=principal');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <title>Sección</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0"
        data-count-docentes="<?= $countDocentes ?? 0 ?>"
        data-count-espacios="<?= $countEspacios ?? 0 ?>"
        data-count-turnos="<?= $countTurnos ?? 0 ?>"
        data-count-anios="<?= $countAnios ?? 0 ?>"
        data-count-mallas="<?= $countMallas ?? 0 ?>"
        data-mostrar-prompt-duplicar="<?= htmlspecialchars(json_encode($mostrar_prompt_duplicar), ENT_QUOTES, 'UTF-8') ?>">

        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Sección</h2>
            <div class="w-100 d-flex justify-content-end mb-3 gap-2" style="max-width: 900px;">
                <button class="btn btn-primary px-4" id="btnAbrirModalUnir">Unir Horarios</button> 
                <button class="btn btn-success px-4" id="btnIniciarRegistro">Registrar Sección</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 900px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="tablaListadoHorarios">
                        <thead>
                            <tr>
                                <th>Código de Sección</th>
                                <th>Cantidad de Estudiantes</th>
                                <th>Año</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalRegistroSeccion">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Paso 1: Registrar Nueva Sección</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="formRegistroSeccion" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" value="registrar_seccion">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label for="anioId" class="form-label">Año <span class="text-danger">*</span></label>
                                        <select class="form-select" name="anioId" id="anioId" required>
                                            <option value="" disabled selected>Seleccione un año</option>
                                            <?php
                                            if (!empty($anios)) {
                                                foreach ($anios as $anio) {

                                                    $value = htmlspecialchars($anio['ani_anio'] . '|' . $anio['ani_tipo'], ENT_QUOTES);

                                                    $tipoTexto = $anio['ani_tipo'] === 'regular' ? 'Regular' : 'Intensivo';
                                                    $text = htmlspecialchars($anio['ani_anio'] . ' (' . $tipoTexto . ')', ENT_QUOTES);

                                                    echo "<option value='{$value}'>{$text}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="codigoSeccion" class="form-label">Código <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="codigoSeccion" name="codigoSeccion" required title="El código debe tener un prefijo de 2-3 letras y luego números (ej: IN1101)." oninput="this.value = this.value.toUpperCase()">
                                        <div id="alerta-codigo" class="form-text text-danger p-1 mt-1 text-center" style="display:none; font-size: 0.85em;"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="cantidadSeccion" class="form-label">Estudiantes <span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" id="cantidadSeccion" name="cantidadSeccion" required min="0" max="99" value="0">
                                        <div id="cantidad-seccion-error" class="form-text text-muted" style="display: none;">La cantidad debe ser un número entre 0 y 99.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary me-2" id="btnGuardarSeccion">REGISTRAR Y CONTINUAR</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

      <div class="modal fade" tabindex="-1" role="dialog" id="modal-horario">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalHorarioGlobalTitle"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="form-horario" autocomplete="off">
                            <input type="hidden" name="accion" id="accion">
                            <input type="hidden" name="sec_codigo" id="sec_codigo_hidden">
                            <input type="hidden" id="ani_anio_hidden" name="ani_anio">
                            
                            <input type="hidden" id="filtro_turno">
                            <div class="d-flex justify-content-between align-items-center mb-4 px-4 py-3 bg-light rounded">
                                <div style="flex: 0 0 220px;">
                                    <label for="cantidadSeccionModificar" class="form-label fw-bold mb-2">Estudiantes <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cantidadSeccionModificar" name="cantidadSeccion" required min="0" max="99" placeholder="0-99">
                                    <div id="cantidad-seccion-modificar-error" class="form-text text-muted" style="display: none;">Debe ser entre 0 y 99</div>
                                </div>
                                <div class="ms-4">
                                     <button type="button" class="btn btn-warning" id="btnLimpiarHorario" title="Limpiar todo el horario" style="padding: 0.5rem 1.5rem;">
                                        <img src="public/assets/icons/escoba.svg" alt="Limpiar" style="width: 18px; height: 18px; margin-right: 8px;">
                                        <span>Limpiar Horario</span>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive mt-2" id="contenedorTablaHorario">
                                <table class="table table-bordered text-center" id="tablaHorario">
                                    <thead>
                                        <tr>
                                            <th>
                                                Hora
                                                <a href="#" id="btnAnadirFilaHorario" class="d-block small fw-normal" style="text-decoration: none;">
                                                    (Agregar más horas de clase)
                                                </a>
                                            </th>
                                            <th>Lunes</th>
                                            <th>Martes</th>
                                            <th>Miércoles</th>
                                            <th>Jueves</th>
                                            <th>Viernes</th>
                                            <th>Sábado</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalVerHorario" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modalVerHorarioTitle">Ver Horario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive" id="contenedorTablaVerHorario">
                            <table class="table table-bordered text-center" id="tablaVerHorario">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                        <th>Sábado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center fs-5">¿Está seguro de que desea eliminar la siguiente sección y su horario asociado? <strong>Esta acción no se puede deshacer.</strong></p>
                        <div class="card my-3">
                            <div class="card-header"><strong>Detalles de la Sección a Eliminar</strong></div>
                            <div class="card-body" id="detallesParaEliminar">
                            </div>
                        </div>
                        <h5 class="mt-4">Horario Asociado:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center" id="tablaEliminarHorario">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                        <th>Sábado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-danger" id="btnProcederEliminacion">ELIMINAR</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                    </div>
                </div>
            </div>
        </div>

    <div class="modal fade" id="modalEntradaHorario" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestionar Bloque Horario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="modal-body-gestion-clase">
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalUnirHorarios" tabindex="-1" aria-labelledby="modalUnirHorariosLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalUnirHorariosLabel">Unir Horarios de Secciones</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formUnirHorarios" novalidate>
                            <input type="hidden" name="accion" value="unir_horarios">
                            <div class="alert alert-info" role="alert">
                                <strong>Paso 1:</strong> Marque 2 o más secciones que desea unir. El sistema agrupará automáticamente las secciones compatibles (mismo año, tipo y trayecto).
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Secciones a Unir</label>
                                <div id="unirSeccionesContainer" class="border p-3 rounded" style="height: 200px; overflow-y: auto;">
                                </div>
                            </div>

                            <div class="alert alert-info mt-4" role="alert">
                                <strong>Paso 2:</strong> De las secciones que marcó, seleccione cuál de ellas tiene el horario que desea copiar a las demás.
                            </div>
                            <div class="mb-3">
                                <label for="unirSeccionOrigen" class="form-label">Usar el horario de la sección:</label>
                                <select class="form-select" id="unirSeccionOrigen" name="id_seccion_origen" required>
                                    <option value="" disabled selected>Marque primero las secciones a unir...</option>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar una sección de origen.</div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary" id="btnConfirmarUnion">UNIR HORARIOS</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     
     
<div class="modal fade" id="modalReporteHorario" tabindex="-1" aria-labelledby="modalReporteHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReporteHorarioLabel">Generar Reporte de Horario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center mb-4">Descargar horario para la sección <strong id="reporteSeccionCodigo"></strong> en formato:</p>
                
                <input type="hidden" id="reporte_sec_codigo_hidden">
                <input type="hidden" id="reporte_ani_anio_hidden">

                <div class="d-grid gap-2 col-8 mx-auto">
                    <button type="button" class="btn btn-danger btn-generar-reporte-tipo" data-tipo="pdf">
                        <img src="public/assets/icons/filetype-pdf.svg"  alt="PDF" style="height: 1.2em; margin-right: 8px; filter: brightness(0) invert(1);"> Generar PDF
             
                    </button>
                    <button type="button" class="btn btn-success btn-generar-reporte-tipo" data-tipo="excel">
                        <img src="public/assets/icons/file-earmark-spreadsheet.svg" alt="Excel" style="height: 1.2em; margin-right: 8px; filter: brightness(0) invert(1)"> Generar Excel
                    </button>
                    <button type="button" class="btn btn-primary btn-generar-reporte-tipo" data-tipo="word">
                        <img src="public/assets/icons/file-earmark-word.svg" alt="Word" style="height: 1.2em; margin-right: 8px; filter: brightness(0) invert(1)"> Generar Word
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="formGenerarReporte" action="?pagina=seccion" method="POST" target="_blank" style="display: none;">
    <input type="hidden" name="accion" value="generar_reporte">
    <input type="hidden" id="form_reporte_sec_codigo" name="sec_codigo">
    <input type="hidden" id="form_reporte_ani_anio" name="ani_anio">
    <input type="hidden" id="form_reporte_formato" name="formato">
</form>
    </main>
    <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <?php require_once("public/components/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="public/js/seccion.js?v=FINAL"></script>
</body>

</html>