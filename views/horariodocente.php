<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Horario Docente</title>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Horario Docente</h2>
            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Horario Docente</button>
            </div>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablahorario">
                        <thead>
                            <tr>
                                <th style="display: none;">id_hdo</th>
                                <th style="display: none;">id_doc_hidden</th> <th>Docente</th> <th>Lapso</th>
                                <th>Actividad</th>
                                <th>Descripción</th>
                                <th>Dependencia</th>
                                <th>Observación</th>
                                <th>Horas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta">
                            </tbody>
                    </table>
                </div>
            </div>
        </section>
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Horario Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-2" style="display: none;">
                                        <label for="hdoId" class="form-label">ID</label>
                                        <input class="form-control" type="text" id="hdoId" name="hdoId" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docente" class="form-label">Docente <span class="text-danger">*</span></label>
                                        <select class="form-select" id="docente" name="docente" required>
                                            <option value="">-- Seleccione un Docente --</option>
                                        </select>
                                        <div class="invalid-feedback">Por favor, seleccione un docente.</div>
                                        <span id="sdocente"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="lapso" class="form-label">Lapso <span class="text-danger">*</span></label>
                                        <select class="form-select" name="lapso" id="lapso" required>
                                            <option value="" disabled selected>Seleccione</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                        </select>
                                        <div class="invalid-feedback">Por favor, seleccione un lapso.</div>
                                        <span id="slapso"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="actividad" class="form-label">Actividad <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="actividad" name="actividad" placeholder="Ej: Acreditables" required>
                                        <div class="invalid-feedback">Por favor, ingrese la actividad.</div>
                                        <span id="sactividad"></span>
                                    </div>
                                </div>
                                <div class="row mt-3 g-3">
                                    <div class="col-md-6">
                                        <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="descripcion" name="descripcion" required>
                                        <div class="invalid-feedback">Por favor, ingrese la descripción.</div>
                                        <span id="sdescripcion"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dependencia" class="form-label">Dependencia <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="dependencia" name="dependencia" required>
                                        <div class="invalid-feedback">Por favor, ingrese la dependencia.</div>
                                        <span id="sdependencia"></span>
                                    </div>
                                </div>
                                <div class="row mt-3 g-3">
                                    <div class="col-md-6">
                                        <label for="observacion" class="form-label">Observación</label>
                                        <input class="form-control" type="text" id="observacion" name="observacion">
                                        <span id="sobservacion"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="horas" class="form-label">Horas <span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" id="horas" name="horas" min="1" required>
                                        <div class="invalid-feedback">Por favor, ingrese las horas (mínimo 1).</div>
                                        <span id="shoras"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary me-2" id="proceso"></button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerHorarioDocente">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modalVerHorarioDocenteTitle">Horario del Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 id="nombreDocenteHorario" class="text-center mb-3"></h4> 
                        <div class="table-responsive" id="contenedorTablaVerHorarioDocente">
                            <table class="table table-bordered" id="tablaVerHorarioDocente">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th data-day="Lunes">Lunes</th>
                                        <th data-day="Martes">Martes</th>
                                        <th data-day="Miércoles">Miércoles</th>
                                        <th data-day="Jueves">Jueves</th>
                                        <th data-day="Viernes">Viernes</th>
                                        <th data-day="Sábado">Sábado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/horarioDocente.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script> </body>
</html>
