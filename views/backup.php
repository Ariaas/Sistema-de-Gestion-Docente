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
    <title>Gestionar Mantenimiento del Sistema</title>
   
    <style>
        .mantenimiento-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .mantenimiento-section {
            margin-bottom: 25px;
        }

        .mantenimiento-section h3 {
            color: #007bff;
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .text-danger {
            color: #dc3545;
            font-size: 0.9em;
        }

        hr {
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Gestionar Mantenimiento del Sistema</h2>

            <div class="mantenimiento-container">
                <div class="mantenimiento-section">
                    <h3>Respaldo de Bases de Datos</h3>
                    <p>Se generará un respaldo completo de las bases de datos del sistema (principal y bitácora).</p>
                    <button class="btn btn-success" id="guardarRespaldo">Generar Nuevo Respaldo</button>
                </div>

                <hr>

                <div class="mantenimiento-section">
                    <h3>Restaurar Sistema desde Respaldo</h3>
                    <p>Seleccione un archivo ZIP de respaldo para restaurar ambas bases de datos.</p> <label for="selectArchivoRespaldo" class="form-label">Seleccione un archivo de respaldo (.zip):</label>
                    <select class="form-select" id="selectArchivoRespaldo">
                        <option value="">Cargando respaldos...</option>
                    </select>
                    <p class="text-danger" id="mensajeRestauracion"></p>
                    <button class="btn btn-info" id="restaurarSistemaBtn">Restaurar Sistema</button>
                </div>
            </div>

        </section>
    </main>
    <?php
    require_once("public/components/footer.php");
    ?>

    <script type="text/javascript" src="public/js/backup.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>