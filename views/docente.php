<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Docente</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Docente</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Docente</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tabladocente">
                        <thead>
                            <tr>
                                <th>Prefijo</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Correo</th>
                                <th>Categoría</th>
                                <th>Títulos</th>
                                <th>Coordinaciones</th>
                                <th>Dedicación</th>
                                <th>Condición</th>
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
                        <h5 class="modal-title">Formulario de Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion">

                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="prefijoCedula" class="form-label">Prefijo</label>
                                    <select class="form-select" name="prefijoCedula" id="prefijoCedula" required>
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="cedulaDocente" class="form-label">Cédula</label>
                                    <input class="form-control" type="text" id="cedulaDocente" name="cedulaDocente" placeholder="Ej: 12345678" required>
                                    <span id="scedulaDocente" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombreDocente" class="form-label">Nombre</label>
                                    <input class="form-control" type="text" id="nombreDocente" name="nombreDocente" placeholder="Ej: María José" required>
                                    <span id="snombreDocente" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidoDocente" class="form-label">Apellido</label>
                                    <input class="form-control" type="text" id="apellidoDocente" name="apellidoDocente" placeholder="Ej: González Pérez" required>
                                    <span id="sapellidoDocente" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="correoDocente" class="form-label">Correo Electrónico</label>
                                    <input class="form-control" type="email" id="correoDocente" name="correoDocente" placeholder="Ej: docente@universidad.edu.ve" required>
                                    <span id="scorreoDocente" class="text-danger"></span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria" id="categoria" required>
                                        <option value="" disabled selected>Seleccione...</option>
                                        <?php foreach ($categorias as $categoria) {
                                            echo "<option value='" . $categoria['cat_id'] . "'>" . $categoria['cat_nombre'] . "</option>";
                                        } ?>
                                    </select>
                                    <span id="scategoria" class="text-danger"></span>
                                </div>
                                <div class="col-md-4">
                                    <label for="dedicacion" class="form-label">Dedicación</label>
                                    <select class="form-select" name="dedicacion" id="dedicacion" required>
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="exclusiva">Exclusiva</option>
                                        <option value="ordinaria">Ordinaria</option>
                                        <option value="contratado">Contratado</option>
                                    </select>
                                    <span id="sdedicacion" class="text-danger"></span>
                                </div>
                                <div class="col-md-4">
                                    <label for="condicion" class="form-label">Condición</label>
                                    <select class="form-select" name="condicion" id="condicion" required>
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="medio">Medio</option>
                                        <option value="completo">Completo</option>
                                        <option value="parcial">Parcial</option>
                                    </select>
                                    <span id="scondicion" class="text-danger"></span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Títulos <span class="text-danger">*</span></label>
                                    <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($titulos as $titulo): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="titulos[]" id="titulo_<?= $titulo['tit_id'] ?>" value="<?= $titulo['tit_id'] ?>">
                                                <label class="form-check-label" for="titulo_<?= $titulo['tit_id'] ?>"><?= $titulo['tit_nombre'] ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <span id="stitulos" class="text-danger"></span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Coordinaciones <span class="text-danger">*</span></label>
                                    <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($coordinaciones as $coordinacion): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="coordinaciones[]" id="coordinacion_<?= $coordinacion['cor_id'] ?>" value="<?= $coordinacion['cor_id'] ?>">
                                                <label class="form-check-label" for="coordinacion_<?= $coordinacion['cor_id'] ?>"><?= $coordinacion['cor_nombre'] ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <span id="scoordinaciones" class="text-danger"></span>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center mt-4">
                                <button type="button" class="btn btn-primary me-2" id="proceso">Guardar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalHoras" tabindex="-1" role="dialog" aria-labelledby="modalHorasLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modalHorasLabel">Horas de Actividad Asignadas</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Docente:</strong> <span id="nombreDocenteHoras" class="fw-bold"></span></p>
                        <hr>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Creación Intelectual
                                <span class="badge bg-primary rounded-pill fs-6" id="horasCreacion">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Integración con la Comunidad
                                <span class="badge bg-success rounded-pill fs-6" id="horasIntegracion">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Gestión Académica
                                <span class="badge bg-warning text-dark rounded-pill fs-6" id="horasGestion">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Otras Actividades
                                <span class="badge bg-secondary rounded-pill fs-6" id="horasOtras">0</span>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/docente.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>