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

$puede_registrar = tiene_permiso_accion('unidad curricular', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('unidad curricular', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('unidad curricular', 'eliminar', $permisos);

if (!$puede_registrar && !$puede_modificar && !$puede_eliminar) {
    header('Location: ?pagina=principal');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Unidades Curriculares</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0" data-total-ejes="<?php echo count($ejes); ?>" data-total-areas="<?php echo count($areas); ?>">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Unidades Curriculares</h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px; gap: 10px;">
                <div class="d-flex flex-column align-items-end">
                    <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Unidad Curricular</button>
                    <span id="registrar-warning" class="text-danger mt-1" style="font-size: 0.9rem;"></span>
                </div>
            </div>

            <div class="datatable-ui w-100" id="tablaucContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">

                <table class="table table-striped table-hover w-100" id="tablauc">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Trayecto</th>
                            <th>Área</th>
                            <th>Fase</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="resultadoconsulta1"></tbody>
                </table>

            </div>
        </section>

        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Unidad Curricular</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion" value="registrar">
                            <div class="mb-4">
                                <div class="row g-3 mb-2">
                                    <div class="col-md-4">
                                        <label for="codigoUC" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoUC" name="codigoUC" required placeholder="Ej: MAT101">
                                        <span id="scodigoUC" class="form-text"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nombreUC" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="nombreUC" name="nombreUC" required placeholder="Ej: Matemáticas Básicas">
                                        <span id="snombreUC" class="form-text"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="creditosUC" class="form-label">Unidades de Crédito</label>
                                        <input class="form-control" type="number" id="creditosUC" name="creditosUC" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="Ej: 4">
                                        <span id="screditosUC" class="form-text"></span>
                                    </div>
                                </div>
                                <div class="row g-3 mb-2">
                                    <div class="col-md-4">
                                        <label for="ejeUC" class="form-label">Eje</label>
                                        <select class="form-select" name="ejeUC" id="ejeUC" required>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <?php
                                            $ejeActual = isset($_GET['ejeActual']) ? $_GET['ejeActual'] : '';
                                            $ejesActivos = array_column($ejes, 'eje_nombre');
                                            if ($ejeActual && !in_array($ejeActual, $ejesActivos)) {
                                                echo "<option value='" . htmlspecialchars($ejeActual) . "' disabled selected>" . htmlspecialchars($ejeActual) . " (eliminado)</option>";
                                            }
                                            if (!empty($ejes)) {
                                                foreach ($ejes as $eje) {
                                                    echo "<option value='" . htmlspecialchars($eje['eje_nombre']) . "'>" . htmlspecialchars($eje['eje_nombre']) . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No hay ejes disponibles</option>";
                                            }
                                            ?>
                                        </select>
                                        <span id="seje" class="form-text"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="areaUC" class="form-label">Área</label>
                                        <select class="form-select" name="areaUC" id="areaUC" required>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <?php
                                            $areasActivas = array_column($areas, 'area_nombre');
                                            if (!empty($areas)) {
                                                foreach ($areas as $area) {
                                                    echo "<option value='" . htmlspecialchars($area['area_nombre']) . "'>" . htmlspecialchars($area['area_nombre']) . "</option>";
                                                }
                                            }
                                            if (!empty($areasEliminadas)) {
                                                foreach ($areasEliminadas as $areaEliminada) {
                                                    if (!in_array($areaEliminada, $areasActivas)) {
                                                        echo "<option value='" . htmlspecialchars($areaEliminada) . "'>" . htmlspecialchars($areaEliminada) . " (eliminado)</option>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                        <span id="sarea" class="form-text"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="trayectoUC" class="form-label">Trayecto</label>
                                        <select class="form-select" name="trayectoUC" id="trayectoUC" required>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="0">Trayecto inicial</option>
                                            <option value="1">Trayecto 1</option>
                                            <option value="2">Trayecto 2</option>
                                            <option value="3">Trayecto 3</option>
                                            <option value="4">Trayecto 4</option>
                                        </select>
                                        <span id="strayectoUC" class="form-text"></span>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6">
                                        <label for="periodoUC" class="form-label">Periodo</label>
                                        <select class="form-select form-select-sm" name="periodoUC" id="periodoUC">
                                            <option value="" disabled selected>Seleccione un Periodo</option>
                                            <option value="Anual">Anual</option>
                                            <option value="Fase I">Fase 1</option>
                                            <option value="Fase II">Fase 2</option>
                                        </select>
                                        <span id="speriodoUC" class="form-text"></span>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center" style="margin-top: 2em;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="electivaUC" name="electivaUC" value="1">
                                            <label class="form-check-label" for="electivaUC">Electiva</label>
                                        </div>
                                        <span id="selectivaUC" class="form-text ms-2"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal2">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Asignar Docentes</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f-asignar" autocomplete="off" class="needs-validation" novalidate>
                            <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover w-100" id="tablaDocentesDisponibles">
                                    <thead>
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Nombre</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cuerpoTablaDocentesDisp"></tbody>
                                </table>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-12">
                                    <label class="form-label">Docentes a asignar:</label>
                                    <ul id="carritoDocentes" class="list-group" style="max-height: 150px; overflow-y: auto;"></ul>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-success" id="asignarDocentes">Procesar Asignación</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    require_once("public/components/footer.php");
    ?>

    <script>
        const PERMISOS = {
            modificar: <?php echo json_encode($puede_modificar); ?>,
            eliminar: <?php echo json_encode($puede_eliminar); ?>
        };
    </script>
    <script type="text/javascript" src="public/js/uc.js"></script>
    <div class="modal fade" id="modalVerMasUC" tabindex="-1" aria-labelledby="modalVerMasUCLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalVerMasUCLabel">Detalles de Unidad Curricular</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Código:</strong> <span id="ucVerMasCodigo"></span></li>
                        <li class="list-group-item"><strong>Nombre:</strong> <span id="ucVerMasNombre"></span></li>
                        <li class="list-group-item"><strong>Trayecto:</strong> <span id="ucVerMasTrayecto"></span></li>
                        <li class="list-group-item"><strong>Área:</strong> <span id="ucVerMasArea"></span></li>
                        <li class="list-group-item"><strong>Eje:</strong> <span id="ucVerMasEje"></span></li>
                        <li class="list-group-item"><strong>Créditos:</strong> <span id="ucVerMasCreditos"></span></li>
                        <li class="list-group-item"><strong>Periodo:</strong> <span id="ucVerMasPeriodo"></span></li>
                        <li class="list-group-item"><strong>Electiva:</strong> <span id="ucVerMasElectiva"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalDetallesUC" tabindex="-1" aria-labelledby="modalDetallesUCLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetallesUCLabel">Detalles de Unidad Curricular</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6>Detalles Generales</h6>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Código:</strong> <span id="detallesUcCodigo"></span></li>
                            <li class="list-group-item"><strong>Nombre:</strong> <span id="detallesUcNombre"></span></li>
                            <li class="list-group-item"><strong>Trayecto:</strong> <span id="detallesUcTrayecto"></span></li>
                            <li class="list-group-item"><strong>Área:</strong> <span id="detallesUcArea"></span></li>
                            <li class="list-group-item"><strong>Eje:</strong> <span id="detallesUcEje"></span></li>
                            <li class="list-group-item"><strong>Créditos:</strong> <span id="detallesUcCreditos"></span></li>
                            <li class="list-group-item"><strong>Periodo:</strong> <span id="detallesUcPeriodo"></span></li>
                            <li class="list-group-item"><strong>Electiva:</strong> <span id="detallesUcElectiva"></span></li>
                        </ul>
                    </div>

                    <div>
                        <h6 class="mb-3">Docentes Asignados a: <span id="ucDetallesNombreModal" class="fw-bold"></span></h6>
                        <ul class="list-group" id="listaDocentesDetalles">
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="public/js/validacion.js"></script>

</body>

</html>