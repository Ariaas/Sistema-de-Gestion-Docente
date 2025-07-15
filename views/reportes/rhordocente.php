<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php require_once("public/components/head.php"); ?>
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
    <title>Reporte - Horario Docente</title>
</head>
<body>
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0" style="padding-top: 25px; padding-bottom: 40px;">
        <div class="container" style="width: 85%; max-width: 700px;">
            <div class="text-center mb-4">
                <h2 class="text-primary">Reporte - Horario del Personal Docente</h2>
                <p class="lead">Seleccione un docente para generar su horario detallado en formato PDF.</p>
            </div>
            <div class="card p-4 shadow-sm bg-light rounded">
                <form method="post" action="" target="_blank">
                    <div class="row g-3 justify-content-center mb-4">
                        <div class="col-md-10">
                            <label for="cedula_docente" class="form-label">Seleccione el Docente: <span style="color:red;">*</span></label>
                            <select class="form-select" name="cedula_docente" id="cedula_docente" required>
                                <option value="">-- Busque y seleccione un docente --</option>
                                <?php if (!empty($listaDocentes)):
                                    foreach ($listaDocentes as $docente): ?>
                                        <option value="<?= htmlspecialchars($docente['doc_cedula']) ?>">
                                            <?= htmlspecialchars($docente['nombreCompleto']) . ' (C.I: ' . htmlspecialchars($docente['doc_cedula']) . ')' ?>
                                        </option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-danger btn-lg px-5" name="generar_rhd_report">
                                <i class="fas fa-file-pdf me-2"></i>Generar PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script src="public/js/rhordocente.js"></script>
</body>
</html>