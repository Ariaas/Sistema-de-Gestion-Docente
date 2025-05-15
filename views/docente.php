<!DOCTYPE html>
<html lang="ES">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docente</title>
    <?php require_once("public/components/head.php"); ?>
</head>

<body>
    <?php require_once("public/components/sidebar.php"); ?>
    
    <main class="main-content">
        <section class="d-flex flex-column align-items-md-center" style="margin-top: 110px;">
            <center>
                <h2 class="text-primary text-md-center">Gestionar Docente</h2>
            </center>
            <br>
            <div class="container">
                <div class="text-md-left">
                    <button class="btn btn-success" id="incluir">Registrar Docente</button>
                </div>
            </div>
            <br>
            <div class="container card shadow mb-4">
                <br>
                <div class="container text-md-center">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabladocente">
                            <thead>
                                <tr>
                                    <th>Prefijo</th>
                                    <th>Cédula</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Correo</th>
                                    <th>Categoría</th>
                                    <th>Dedicación</th>
                                    <th>Condición</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoconsulta"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="text" class="form-control" name="accion" id="accion" style="display: none;">
                            
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label for="prefijoCedula" class="form-label">Prefijo</label>
                                        <select class="form-select" name="prefijoCedula" id="prefijoCedula" required>
                                            <option value="" disabled selected>Seleccione</option>
                                            <option value="V">V</option>
                                            <option value="E">E</option>
                                        </select>
                                        <span id="sprefijoCedula"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cedulaDocente" class="form-label">Cédula</label>
                                        <input class="form-control" type="text" id="cedulaDocente" name="cedulaDocente" required>
                                        <span id="scedulaDocente"></span>
                                    </div>
                                </div>
                                
                                <div class="row mt-3 g-3">
                                    <div class="col-md-6">
                                        <label for="nombreDocente" class="form-label">Nombre</label>
                                        <input class="form-control" type="text" id="nombreDocente" name="nombreDocente" required>
                                       <span id="snombreDocente"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellidoDocente" class="form-label">Apellido</label>
                                        <input class="form-control" type="text" id="apellidoDocente" name="apellidoDocente" required>
                                         <span id="sapellidoDocente"></span>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-8">
                                        <label for="correoDocente" class="form-label">Correo Electrónico</label>
                                        <input class="form-control" type="email" id="correoDocente" name="correoDocente" required>
                                         <span id="scorreoDocente"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="categoria" class="form-label">Categoría</label>
                                        <select class="form-select" name="categoria" id="categoria" required>
                                            <option value="" disabled selected>Seleccione una categoría</option>
                                            <?php
                                            foreach ($categorias as $categoria) {
                                                echo "<option value='" . $categoria['cat_id'] . "'>" . $categoria['cat_nombre'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <span id="scategoria" class="error"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="dedicacion" class="form-label">Dedicación</label>
                                        <select class="form-select" name="dedicacion" id="dedicacion" required>
                                            <option value="" disabled selected>Seleccione una dedicación</option>
                                            <option value="Dedicacion exclusiva">Dedicación exclusiva</option>
                                            <option value="Dedicacion">Dedicación</option>
                                        </select>
                                              <span id="sdedicacion"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="condicion" class="form-label">Condición</label>
                                        <select class="form-select" name="condicion" id="condicion" required>
                                            <option value="" disabled selected>Seleccione una condición</option>
                                            <option value="Ordinario">Ordinario</option>
                                            <option value="Desordinario">Desordinario</option>
                                        </select>
                                         <span id="scondicion"></span>>
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
        <!-- Fin del Modal -->
    </main>

    <!-- Footer -->
    <?php require_once("public/components/footer.php"); ?>
    
    <!-- Scripts -->
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="public/js/docente.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>
</html>