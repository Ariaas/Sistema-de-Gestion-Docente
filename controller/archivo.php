<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $archivo = new Archivo();
        $accion = $_POST['accion'] ?? '';

        switch ($accion) {
            case 'subir':
                if (isset($_FILES['archivo'])) {
                 
                    $docente = $_POST['docente'] ?? '';       
                    $ucurricular = $_POST['ucurricular'] ?? ''; 
                    $fecha = $_POST['fecha'] ?? date('Y-m-d');

                    echo json_encode($archivo->guardarArchivo(
                        $_FILES['archivo'],
                        $docente,
                        $ucurricular,
                        $fecha
                    ));
                }
                break;

            case 'listar':
                echo json_encode([
                    'resultado' => 'listar',
                    'datos' => $archivo->listarArchivosLocales()
                ]);
                break;

            case 'eliminar':
                if (isset($_POST['nombre_archivo'])) {
                    echo json_encode($archivo->eliminarArchivo($_POST['nombre_archivo']));
                }
                break;
        }
        exit;
    }
    $obj2 = new Archivo();

    $docentes = $obj2->obtenerdocente();
    $unidadcurriculares = $obj2->obtenerunidadcurricular();


    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
