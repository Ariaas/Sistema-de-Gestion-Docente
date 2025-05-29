<?php
// El manejo de sesión y permisos debería estar aquí o en un archivo incluido
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// if (!isset($_SESSION['name'])) {
    // header('Location: .'); // Redirigir si no hay sesión
    // exit();
// }
// $permisos = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : [];
// if (!in_array('reporte_prosecucion', $permisos)) {
//    echo "No tiene permiso para acceder a este módulo.";
//    exit;
// }

// Si $datosProsecucion está seteado, significa que estamos generando el HTML para el PDF
if (isset($datosProsecucion) && !isset($datosProsecucion['error'])) {
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Reporte de Prosecución Académica</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }

            .report-container {
                width: 100%;
            }

            h1,
            h2 {
                text-align: center;
                color: #333;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                font-size: 10px;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: 6px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .trayecto-inicial {
                background-color: #e6f7ff;
            }

            .trayecto-I {
                background-color: #e6ffe6;
            }

            .trayecto-II {
                background-color: #fff0e6;
            }

            .trayecto-III {
                background-color: #f9e6ff;
            }

            .trayecto-IV {
                background-color: #ffffe6;
            }

            .text-center {
                text-align: center;
            }

            .seccion-union {
                background-color: #ffffcc;
            }

           
            .seccion-promovida {
                
            }

            .col-trayecto {
                width: 10%;
            }

            .col-secciones {
                width: 20%;
            }

            .col-cantidad {
                width: 8%;
            }

            .col-carga {
                width: 12%;
            }
        </style>
    </head>

    <body>
        <div class="report-container">
            <h1>UPTAEB - PNF INFORMÁTICA</h1>
            <h2>PROSECUCIÓN ACADÉMICA <?php echo htmlspecialchars($selectedAnio); ?></h2>

            <table>
                <thead>
                    <tr>
                        <th class="col-trayecto">Trayecto Actual</th>
                        <th class="col-secciones">Secciones Actuales</th>
                        <th class="col-cantidad">Cantidad</th>
                        <th class="col-trayecto">Trayecto Siguiente</th>
                        <th class="col-secciones">Secciones Promovidas / Unidas</th>
                        <th class="col-cantidad">Cantidad</th>
                        <th class="col-carga">Carga Académica (Siguiente Trayecto)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $trayectosOrden = ['INICIAL', 'I', 'II', 'III', 'IV']; 

                    foreach ($trayectosOrden as $index => $nombreTrayectoActual) {
                        if (!isset($datosProsecucion[$nombreTrayectoActual])) continue;

                        $dataTrayectoActual = $datosProsecucion[$nombreTrayectoActual];
                        $seccionesActuales = $dataTrayectoActual['secciones_actuales'];

                        $nombreTrayectoSiguiente = isset($trayectosOrden[$index + 1]) ? $trayectosOrden[$index + 1] : 'FINALIZADO';
                        $seccionesSiguientes = []; 
                        $cargaAcademicaSiguiente = 0; 

                

                        if (empty($seccionesActuales)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($nombreTrayectoActual) . "</td>";
                            echo "<td colspan='2'>No hay secciones</td>";
                            echo "<td>" . htmlspecialchars($nombreTrayectoSiguiente) . "</td>";
                            echo "<td colspan='3'>N/A</td>";
                            echo "</tr>";
                        } else {
                            foreach ($seccionesActuales as $idx => $secActual) {
                                echo "<tr>";
                                if ($idx === 0) { 
                                    echo "<td rowspan='" . count($seccionesActuales) . "'>" . htmlspecialchars($nombreTrayectoActual) . "</td>";
                                }
                                echo "<td class='" . ($secActual['es_union'] ? 'seccion-union' : '') . "'>" . htmlspecialchars(implode(', ', $secActual['codigos'])) . "</td>";
                                echo "<td class='text-center'>" . htmlspecialchars($secActual['cantidad_total']) . "</td>";

                                if ($idx === 0) { 
                                    echo "<td rowspan='" . count($seccionesActuales) . "'>" . htmlspecialchars($nombreTrayectoSiguiente) . "</td>";
                                    echo "<td rowspan='" . count($seccionesActuales) . "'>[Datos de secciones promovidas/unidas]</td>";
                                    echo "<td rowspan='" . count($seccionesActuales) . "' class='text-center'>[Cant]</td>";
                                    echo "<td rowspan='" . count($seccionesActuales) . "' class='text-center'>[Carga]</td>";
                                }
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>

    </html>

<?php
    // Fin del bloque de generación de PDF
} else {
    // Mostrar el formulario para seleccionar filtros
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <?php require_once("public/components/head.php"); ?>
        <title>Reporte de Prosecución Académica</title>
    </head>

    <body class="d-flex flex-column min-vh-100">
        <?php require_once("public/components/sidebar.php"); ?>
        <main class="main-content flex-shrink-0">
            <section class="d-flex flex-column align-items-center justify-content-center py-4">
                <h2 class="text-primary text-center mb-4">Reporte de Prosecución Académica</h2>

                <div class="card p-4 shadow" style="max-width: 600px; width: 100%;">
                    <?php if (isset($datosProsecucion) && isset($datosProsecucion['error'])): ?>
                        <div class="alert alert-danger">
                            Error al generar datos: <?php echo htmlspecialchars($datosProsecucion['error']); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="" target="_blank">
                        <div class="mb-3">
                            <label for="anio_academico" class="form-label">Año Académico:</label>
                            <select class="form-select" id="anio_academico" name="anio_academico" required>
                                <option value="">Seleccione un año</option>
                                <?php if (!empty($aniosAcademicos)): ?>
                                    <?php foreach ($aniosAcademicos as $anio): ?>
                                        <option value="<?php echo htmlspecialchars($anio['tra_anio']); ?>">
                                            <?php echo htmlspecialchars($anio['tra_anio']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay años disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="generar_reporte_prosecucion" class="btn btn-primary">
                                Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
        <?php require_once("public/components/footer.php"); ?>
    
    </body>

    </html>
<?php
} // Fin del else (mostrar formulario)
?>