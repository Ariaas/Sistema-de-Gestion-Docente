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
    <title>Sección</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0"
        data-count-docentes="<?= $countDocentes ?? 0 ?>"
        data-count-espacios="<?= $countEspacios ?? 0 ?>"
        data-count-turnos="<?= $countTurnos ?? 0 ?>"
        data-count-anios="<?= $countAnios ?? 0 ?>"
        data-count-mallas="<?= $countMallas ?? 0 ?>">

        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4">Gestionar Sección</h2>
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
                                    <div class="col-md-6">
                                        <label for="codigoSeccion" class="form-label">Código <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="codigoSeccion" name="codigoSeccion" required minlength="4" maxlength="4" pattern="\d{4}" title="El código debe contener exactamente 4 números." oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                                        <div id="alerta-cohorte" class="form-text text-muted p-1 mt-2 text-center"  style="display:none; font-size: 0.85em;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cantidadSeccion" class="form-label">Cantidad de Estudiantes <span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" id="cantidadSeccion" name="cantidadSeccion" required min="0" max="99" value="0">
                                        <div id="cantidad-seccion-error" class="form-text text-muted" style="display: none;">La cantidad debe ser un número entre 0 y 99.</div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label for="anioId" class="form-label">Año <span class="text-danger">*</span></label>
                                        <select class="form-select" name="anioId" id="anioId" required>
                                            <option value="" disabled selected>Seleccione un año</option>
                                            <?php
                                            if (!empty($anios)) {
                                                foreach ($anios as $anio) {
                                                   
                                                    $value = htmlspecialchars($anio['ani_anio'] . '|' . $anio['ani_tipo'], ENT_QUOTES);

                                               
                                                    $text = htmlspecialchars($anio['ani_anio'], ENT_QUOTES);

                                                    echo "<option value='{$value}'>{$text}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary me-2" id="btnGuardarSeccion">Guardar y Continuar</button>
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
                            <div class="row align-items-end">
                                <div class="col-md-5 mb-3">
                                    <label for="seccion_principal_id" class="form-label">Sección</label>
                                    <select class="form-select" id="seccion_principal_id" name="seccion_id_display" disabled>
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="cantidadSeccionModificar" class="form-label">Estudiantes <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cantidadSeccionModificar" name="cantidadSeccion" required min="0" max="99">
                                    <div id="cantidad-seccion-modificar-error" class="form-text text-muted" style="display: none;">La cantidad debe ser un número entre 0 y 99.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filtro_turno" class="form-label">Turno de la Sección</label>
                                    <select class="form-select" id="filtro_turno" disabled>
                                        <option value="mañana">Turno Mañana</option>
                                        <option value="tarde">Turno Tarde</option>
                                        <option value="noche">Turno Noche</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive mt-3" id="contenedorTablaHorario">
                                <table class="table table-bordered text-center" id="tablaHorario">
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
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn me-2" id="proceso"></button>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnProcederEliminacion">Eliminar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEntradaHorario" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Añadir/Editar Clase</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formularioEntradaHorario" autocomplete="off">
                            <div class="mb-3"><label class="form-label">Franja Horaria:</label><input type="text" class="form-control" id="modalFranjaHoraria" readonly></div>
                            <div class="mb-3"><label class="form-label">Día:</label><input type="text" class="form-control" id="modalDia" readonly></div>

                            <div class="mb-3">
                                <label for="modalSeleccionarDocente" class="form-label">Docente <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalSeleccionarDocente" required>
                                    <option value="">Seleccionar Docente</option>
                                </select>
                                <div id="conflicto-docente-warning" class="alert alert-warning p-2 mt-2" role="alert" style="display:none; font-size: 0.85em;"></div>
                            </div>

                            <div class="mb-3">
                                <label for="modalSeleccionarUc" class="form-label">Unidad Curricular <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalSeleccionarUc" required>
                                    <option value="">Primero seleccione un docente</option>
                                </select>
                                <div id="conflicto-uc-warning" class="alert alert-danger p-2 mt-2" role="alert" style="display:none; font-size: 0.85em;"></div>
                            </div>

                            <div class="mb-3">
                                <label for="modalSeleccionarEspacio" class="form-label">Espacio (Aula/Lab) <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalSeleccionarEspacio" required>
                                    <option value="">Seleccionar Espacio</option>
                                </select>
                                <div id="conflicto-espacio-warning" class="alert alert-warning p-2 mt-2" role="alert" style="display:none; font-size: 0.85em;"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="modalBloquesClase" class="form-label">Duración de la Clase:</label>
                                <select class="form-select" id="modalBloquesClase">
                                    <option value="1">1 Bloque (40 min)</option>
                                    <option value="2">2 Bloques (80 min)</option>
                                    <option value="3">3 Bloques (120 min)</option>
                                    <option value="4">4 Bloques (160 min)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnGuardarClase">Guardar Cambios</button>
                            <button type="button" class="btn btn-danger" id="btnEliminarEntrada" style="display:none;">Eliminar Clase</button>
                        </form>
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
                                <button type="submit" class="btn btn-primary" id="btnConfirmarUnion">Confirmar y Unir Horarios</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <?php require_once("public/components/footer.php"); ?>
    <script src="public/js/seccion.js"></script>
</body>

</html>