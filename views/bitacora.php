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

    <title>Bitacora</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Bitacora</h2>
            <div class="datatable-ui w-100" style="max-width: 1100px; margin: 0 auto 2rem auto; padding: 1.5rem 2rem;">
                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-striped table-hover w-100" id="tablatraBitacora">
                        <thead>
                            <tr>
                                <th>Nombre de usuario</th>
                                <th>Modulo</th>
                                <th>Acciones</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoconsulta"></tbody>
                    </table>
                </div>
            </div>
        </section>


    </main>
   
    <?php require_once("public/components/footer.php"); ?>
    
    <script type="text/javascript" src="public/js/bitacora.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
    
</body>

</html>