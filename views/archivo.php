<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}

if (!empty($acceso_denegado)) {
    echo "<h2 class='text-danger text-center mt-5'>{$acceso_denegado}</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Notas y Remediales</title>
    <style>
        .table-compact {
            font-size: 0.9rem;
        }
        .actions-column .btn {
            width: 100%;
            margin-bottom: 0.25rem;
            text-align: left;
        }
        .btn.download-link {
            color: #6c757d; /* Color de ícono secundario */
            box-shadow: none;
        }
        .btn.download-link:hover {
            background-color: transparent !important;
            color: #212529; /* Color de ícono más oscuro al pasar el ratón */
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Notas y Remediales</h2>

            <?php if (!empty($alerta_datos)) { echo $alerta_datos; } ?>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1200px;">
                <button class="btn btn-success px-4" id="btnNuevoRegistro">
                    <i class="fas fa-plus me-2"></i> Crear Nuevo Registro
                </button>
            </div>

            <div class="w-100 datatable-ui" style="max-width: 1200px; margin: 0 auto 2rem auto; border: 1px solid #dee2e6; border-radius: .5rem; padding: 1.5rem 2rem;">
                <h3 class="text-secondary mt-3 mb-4">Historial de Registros de Notas</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100 table-compact" id="tablaRegistros">
                        <thead>
                            <tr>
                                <th>Año Acad.</th>
                                <th>Sección</th>
                                <th>Unidad Curricular</th>
                                <th>Total Est.</th>
                                <th>Aprob. Dir.</th>
                                <th>Para PER</th>
                                <th>Aprob. PER</th>
                                <th class="text-success">Aprob. Totales</th>
                                <th class="text-danger">Reprobados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosRegistros"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalRegistroNotas">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Registro Inicial de Notas</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formRegistro" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="accion" value="registrar_notas">
                            <input type="hidden" id="uc_nombre" name="uc_nombre">
                            
                             <div class="alert alert-info" role="alert">
                                <?php if ($fase_actual) {
                                    echo "Registrando notas para las materias de la <strong>Fase {$fase_remedial['fase']} del Período {$fase_remedial['anio']}-{$fase_remedial['tipo']}</strong>.";
                                } else {
                                    echo "No hay un período de registro de notas activo en este momento.";
                                } ?>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="anio" class="form-label">Año Académico</label>
                                    <select class="form-select" name="anio" id="anio" required>
                                        <option value="" disabled selected>Seleccione un año</option>
                                        <?php foreach ($anios as $anio) {
                                            $valor_compuesto = $anio['ani_anio'] . ':' . $anio['ani_tipo'];
                                            $texto_opcion = $anio['ani_anio'] . ' (' . $anio['ani_tipo'] . ')';
                                            $selected = ($fase_actual && $fase_actual['ani_anio'] == $anio['ani_anio'] && $fase_actual['ani_tipo'] == $anio['ani_tipo']) ? 'selected' : '';
                                            echo "<option value='{$valor_compuesto}' {$selected}>{$texto_opcion}</option>";
                                        } ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione un año académico.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="seccion" class="form-label">Sección</label>
                                    <select class="form-select" name="seccion" id="seccion" required disabled>
                                        <option value="" disabled selected>Seleccione un año primero</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione una sección.</div>
                                    <span id="scantidad" class="form-text fw-bold text-primary"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 mb-3">
                                    <label for="ucurricular" class="form-label">Unidad Curricular</label>
                                    <select class="form-select" name="ucurricular" id="ucurricular" required>
                                        <option value="" disabled selected>Seleccione una U.C.</option>
                                        <?php if (!empty($unidadesCurriculares)) {
                                            foreach ($unidadesCurriculares as $unidad) {
                                                echo "<option value='{$unidad['uc_codigo']}'>{$unidad['uc_nombre']}</option>";
                                            }
                                        } else {
                                             echo "<option value='' disabled>No hay U.C. disponibles para remedial</option>";
                                        }?>
                                    </select>
                                    <div class="invalid-feedback">Por favor, seleccione una unidad curricular.</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3 bg-light py-3 px-2 rounded">
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad_aprobados" class="form-label">Aprobados Directos</label>
                                    <input type="number" class="form-control" name="cantidad_aprobados" id="cantidad_aprobados" required min="0" max="99" maxlength="2" oninput="validarInputNumerico(this)">
                                    <div class="invalid-feedback" id="feedback-aprobados">El campo es obligatorio.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad_per" class="form-label">Estudiantes para PER</label>
                                    <input type="number" class="form-control" name="cantidad_per" id="cantidad_per" required min="0" max="99" maxlength="2" oninput="validarInputNumerico(this)">
                                    <div class="invalid-feedback">El campo es obligatorio.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="archivo_notas" class="form-label">Acta de Notas Finales</label>
                                    <input type="file" class="form-control" name="archivo_notas" id="archivo_notas" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                    <div class="invalid-feedback">Por favor, adjunte el acta de notas.</div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary">GUARDAR REGISTRO</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalAprobadosPer">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Registrar Aprobados del Remedial (PER)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formAprobadosPer" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="registrar_per">
                            <input type="hidden" id="per_uc_codigo" name="uc_codigo">
                            <input type="hidden" id="per_sec_codigo" name="sec_codigo">
                            <input type="hidden" id="per_uc_nombre" name="uc_nombre">
                            <input type="hidden" id="per_anio_anio" name="anio_anio">
                            <input type="hidden" id="per_anio_tipo" name="anio_tipo">

                            <p>Sección: <strong id="per_seccion"></strong> | U. Curricular: <strong id="per_uc"></strong></p>
                            <p>Estudiantes en PER: <strong id="per_cantidad_en_remedial"></strong></p>

                            <div class="mb-3">
                                <label for="cantidad_aprobados_per" class="form-label">Cantidad de Estudiantes APROBADOS en PER</label>
                                <input type="number" class="form-control" name="cantidad_aprobados_per" id="cantidad_aprobados_per" required min="0" max="99" maxlength="2" oninput="validarInputNumerico(this)">
                                <div class="invalid-feedback">El campo es obligatorio y no puede exceder el total de estudiantes en PER.</div>
                            </div>

                            <div class="mb-3">
                                <label for="archivo_per" class="form-label">Acta de Notas del PER</label>
                                <input type="file" class="form-control" name="archivo_per" id="archivo_per" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                <div class="invalid-feedback">Por favor, adjunte el acta de notas del PER.</div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-info">REGISTRAR APROBADOS</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script src="public/js/archivo.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>
</html>