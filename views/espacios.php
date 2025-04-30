<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once("components/head.php"); ?>
    <title>Espacios</title>
</head>
<body>
    <?php require_once("components/sidebar.php"); ?>
    <main class="main-content">
        <h1>Gestionar Espacios</h1>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#espaciosModal">
                <h8>Registrar Espacios</h8>
            </button>

            <div class="modal fade" id="espaciosModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="espaciosModalLabelLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="espaciosModalLabel">Registrar Espacios</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cod_espacio" class="form-label">Código de Espacio</label>
                                <input type="text" class="form-control" id="cod_espacio" placeholder="Ingrese el Código" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_espacio" class="form-label">Tipo de Espacio</label>
                                <input type="text" class="form-control" id="tipo_espacio" placeholder="Ingrese el Tipo de Espacio" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#espaciosModal">Registrar</button>
                </div>
                </div>
            </div>
            </div>
    </main>
    <script src="public/js/espacios.js"></script>
    <?php require_once("components/footer.php"); ?>
</body>
</html>