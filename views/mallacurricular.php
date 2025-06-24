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
    <title>Malla Curricular</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Malla Curricular
            </h2>

            <div class="w-100 d-flex justify-content-end mb-3" style="max-width: 1100px;">
                <button class="btn btn-success px-4" id="registrar">Registrar Malla Curricular</button>
            </div>

            <div class="w-100 text-center botones-cambio-tabla" style="max-width: 1100px;">
                <button class="btn btn-primary px-4" id="btnVerTablaPrincipal">Ver Mallas</button>
                <button class="btn btn-primary px-4" id="btnVerAsignacionUC">Ver Asignación UC</button>
                <button class="btn btn-primary px-4 " id="btnVerAsignacionCert">Ver Asignación Certificados</button>
            </div>


            <div class="datatable-ui w-100" id="tablaMallaPrincipalContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover w-100" id="tablamalla">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Año</th>
                                <th>Cohorte</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>


            <div class="datatable-ui w-100" id="tablaMallaUCContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem; display: none;">

                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover w-100" id="tablaMallaUC">
                        <thead>
                            <tr>
                                <th style="display: none;">ID Malla</th>
                                <th>Código Malla</th>
                                <th>Nombre Malla</th>
                                <th>UCs Asignadas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoAsignacionesUC"></tbody>
                    </table>
                </div>
            </div>


            <div class="datatable-ui w-100" id="tablaMallaCertContainer" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem; display: none;">

                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover w-100" id="tablaMallaCert">
                        <thead>
                            <tr>
                                <th style="display: none;">ID Malla</th>
                                <th>Código Malla</th>
                                <th>Nombre Malla</th>
                                <th>Certificados Asignados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoAsignacionesCert"></tbody>
                    </table>
                </div>
            </div>

        </section>


        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modal1Titulo">Formulario de Malla</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate><input type="hidden" name="accion" id="accion"><input type="hidden" id="mal_id" name="mal_id">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-6"><label for="mal_codigo" class="form-label">Código</label><input class="form-control" type="text" id="mal_codigo" name="mal_codigo" placeholder="Ejemplo: 1123" required><span id="smalcodigo" class="form-text"></span></div>
                                    <div class="col-md-6"><label for="mal_nombre" class="form-label">Nombre</label><input class="form-control" type="text" id="mal_nombre" name="mal_nombre" placeholder="Ejemplo: Malla 2022" required><span id="smalnombre" class="form-text"></span></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4"><label for="mal_Anio" class="form-label">Año</label>
                                    <select class="form-select" name="mal_Anio" id="mal_Anio" required>
                                           <option value="" disabled selected>Seleccione una año</option><?php
                                            if (!empty($anios)): foreach ($anios as $selectanio): ?>
                                            <option value="<?= htmlspecialchars($selectanio['ani_id']) ?>"><?= htmlspecialchars($selectanio['ani_anio']) ?></option>
                                            <?php endforeach;
                                    else: ?><option value="" disabled>No hay año disponibles</option><?php endif; ?>   
                                        </select><span id="smalanio" class="form-text"></span></div>
                                   <div class="col-md-4"> <label for="mal_cohorte" class="form-label">Cohorte</label>
                                   <select class="form-select" name="mal_cohorte" id="mal_cohorte" required>
                                           <option value="" disabled selected>Seleccione una cohorte</option><?php
                                          if (!empty($cohortes)): foreach ($cohortes as $selectcohortes): ?>
                                                 <option value="<?= htmlspecialchars($selectcohortes['coh_id']) ?>">
                                                <?= htmlspecialchars($selectcohortes['coh_numero']) ?></option>
                                            <?php endforeach;
                                    else: ?><option value="" disabled>No hay cohorte disponibles</option><?php endif; ?> 
                                    </select> <span id="smalcohorte" class="form-text"></span></div>
                                     <div class="col-md-4"><label for="mal_descripcion" class="form-label">Descripción</label><input class="form-control" type="text" id="mal_descripcion" name="mal_descripcion" placeholder="Descripción breve" required><span id="smaldescripcion" class="form-text"></span></div>
                                    </div>
                            </div>
                            <div class="modal-footer justify-content-center"><button type="button" class="btn btn-primary me-2" id="proceso">GUARDAR</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalAsignarUC">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Asignar Unidades Curriculares a Malla</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="mallaIdParaUC">
                        <div class="row g-3 mb-3">
                            <div class="col-md-10"><label for="selectUCParaMalla" class="form-label">Unidad Curricular</label>
                            <select class="form-select" id="selectUCParaMalla">
                                 <option value="" disabled selected>Seleccione una UC</option><?php if (!empty($unidades_curriculares_disponibles)): foreach ($unidades_curriculares_disponibles as $uc): ?><option value="<?= htmlspecialchars($uc['uc_id']) ?>"><?= htmlspecialchars($uc['uc_codigo'] . ' - ' . $uc['uc_nombre']) ?></option><?php endforeach;
                                    else: ?><option value="" disabled>No hay UCs disponibles</option><?php endif; ?>   
                                </select></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-primary w-100" id="btnAgregarUCMalla">Agregar</button></div>
                        </div>
                        <h6>UCs Seleccionadas para Asignar:</h6>
                        <ul id="listaUCsSeleccionadasMalla" class="list-group mb-3"></ul>
                        <div class="modal-footer justify-content-center"><button type="button" class="btn btn-success" id="btnGuardarAsignacionUCMalla">Guardar Asignación</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalAsignarCertificado">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Asignar Certificados a Malla</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="mallaIdParaCertificado">
                        <div class="row g-3 mb-3">
                            <div class="col-md-10"><label for="selectCertificadoParaMalla" class="form-label">Certificado</label><select class="form-select" id="selectCertificadoParaMalla">
                                    <option value="" disabled selected>Seleccione un Certificado</option><?php if (!empty($certificados_disponibles)): foreach ($certificados_disponibles as $certificado): ?><option value="<?= htmlspecialchars($certificado['cert_id']) ?>"><?= htmlspecialchars($certificado['cert_nombre']) ?></option><?php endforeach;
                                    else: ?><option value="" disabled>No hay Certificados disponibles</option><?php endif; ?>
                                </select></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-primary w-100" id="btnAgregarCertificadoMalla">Agregar</button></div>
                        </div>
                        <h6>Certificados Seleccionados para Asignar:</h6>
                        <ul id="listaCertificadosSeleccionadosMalla" class="list-group mb-3"></ul>
                        <div class="modal-footer justify-content-center"><button type="button" class="btn btn-success" id="btnGuardarAsignacionCertificadoMalla">Guardar Asignación</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalDesafiliarUC">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Gestionar Unidades Curriculares Asignadas</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>A continuación se listan las UCs asignadas a esta malla. Haga clic en "Quitar" para desafiliar la que desee.</p>
                        <ul id="listaUCsAsignadas" class="list-group mb-3"></ul>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalDesafiliarCertificado">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Gestionar Certificados Asignados</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>A continuación se listan los certificados asignados a esta malla. Haga clic en "Quitar" para desafiliar el que desee.</p>
                        <ul id="listaCertificadosAsignados" class="list-group mb-3"></ul>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" tabindex="-1" role="dialog" id="modalVerDetalles">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="modalDetallesTitulo">Detalles de Asignación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul id="listaDetalles" class="list-group">
                            </ul>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        </main>
    <?php require_once("public/components/footer.php"); ?>
    <script type="text/javascript" src="public/js/mallacurricular.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>