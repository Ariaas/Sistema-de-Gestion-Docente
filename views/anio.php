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

$puede_registrar = tiene_permiso_accion('año', 'registrar', $permisos);
$puede_modificar = tiene_permiso_accion('año', 'modificar', $permisos);
$puede_eliminar = tiene_permiso_accion('año', 'eliminar', $permisos);
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Gestión de Años</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestión de Años</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <div class="d-flex flex-column align-items-end">
                    <button class="btn btn-success px-4" id="registrar" <?php if (!$puede_registrar) echo 'disabled'; ?>>Registrar Año</button>
                    <span id="registrar-warning" class="text-danger mt-1" style="font-size: 0.9rem;"></span>
                </div>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablaanio">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Año</th>
                                <th>Tipo</th>
                                <th>Apertura Fase 1</th>
                                <th>Cierre Fase 1</th>
                                <th>Apertura Fase 2</th>
                                <th>Cierre Fase 2</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Años</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">
                            <input type="hidden" id="aniId" name="aniId">
                            <input type="hidden" id="anioOriginal" name="anioOriginal">
                            <input type="hidden" id="tipoOriginal" name="tipoOriginal">

                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="aniAnio" class="form-label">Año</label>
                                        <select class="form-select" name="aniAnio" id="aniAnio" required>
                                            <option value="" disabled>Seleccione un Año</option>
                                            <?php
                                            $anoActual = date('Y');
                                            for ($year = 1999; $year <= 2070; $year++):
                                                $selected = ($year == $anoActual) ? ' selected' : '';
                                            ?>
                                                <option value="<?= $year ?>" <?= $selected ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <span id="saniAnio"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipoAnio" class="form-label">Tipo de Año</label>
                                        <select class="form-select" name="tipoAnio" id="tipoAnio" required>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="regular">Regular</option>
                                            <option value="intensivo">Intensivo</option>
                                        </select>
                                        <span id="stipoAnio"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="aniAperturaFase1" class="form-label">Apertura Fase 1</label>
                                        <input type="date" class="form-control" id="aniAperturaFase1" name="aniAperturaFase1" required>
                                        <span id="saniAperturaFase1"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="aniCierraFase1" class="form-label">Cierre Fase 1</label>
                                        <input type="date" class="form-control" id="aniCierraFase1" name="aniCierraFase1" required>
                                        <span id="saniCierraFase1"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="aniAperturaFase2" class="form-label">Apertura Fase 2</label>
                                        <input type="date" class="form-control" id="aniAperturaFase2" name="aniAperturaFase2" required>
                                        <span id="saniAperturaFase2"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="aniCierraFase2" class="form-label">Cierre Fase 2</label>
                                        <input type="date" class="form-control" id="aniCierraFase2" name="aniCierraFase2" required>
                                        <span id="saniCierraFase2"></span>
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
        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerPer">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">PER</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Apertura Fase 1 (PER):</strong> <span id="perApertura1"></span></p>
                        <p><strong>Apertura Fase 2 (PER):</strong> <span id="perApertura2"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    <script type="text/javascript" src="public/js/anio.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>