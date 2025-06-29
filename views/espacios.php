<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("public/components/head.php"); ?>

    <title>Espacios</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content">
        <section class="container-fluid p-4">
            <!-- Contenedor Centrado -->
            <div class="datatable-ui">
                <!-- Cabecera de la página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary mb-0" style="font-weight: 600;">Gestionar Espacios</h2>
                    <button class="btn btn-success px-4 d-flex align-items-center justify-content-center" id="registrar">
                        <span>Registrar</span>
                    </button>
                </div>

                <!-- Tarjeta de la DataTable -->
                <div class="card">
                    <div class="card-body" style="overflow-x: hidden;">
                        <table class="table table-striped table-hover w-100" id="tablaespacio">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoconsulta"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
       
        <div class="modal fade" tabindex="-1" role="dialog" id="modal1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Formulario de Espacios</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="f" autocomplete="off" class="needs-validation" novalidate>
                            <input type="hidden" name="accion" id="accion" value="modificar">

                            <div class="container">
                                <div class="row mb-3">
                                    
                                    <div class="col-md-6">
                                        <label for="codigoEspacio" class="form-label">Código</label>
                                        <input class="form-control" type="text" id="codigoEspacio" name="codigoEspacio" placeholder="Ejemplo: H-12" required>
                                        <span id="scodigoEspacio" class="form-text"></span>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label for="tipoEspacio" class="form-label">Tipo</label>
                                        <select class="form-select" name="tipoEspacio" id="tipoEspacio" required>
                                            <option value="" disabled>Seleccione un tipo</option>
                                            <option value="Aula" selected>Aula</option>
                                            <option value="Laboratorio">Laboratorio</option>
                                        </select>
                                        <span id="stipoEspacio" class="form-text"></span>
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
    
    </main>
    
    <?php
    require_once("public/components/footer.php");
    ?>
    
    <script type="text/javascript" src="public/js/espacios.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    
</body>

</html>