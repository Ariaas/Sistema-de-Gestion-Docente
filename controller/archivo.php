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
                    echo json_encode($archivo->guardarArchivo($_FILES['archivo']));
                }
                break;

            case 'listar':
                echo json_encode([
                    'resultado' => 'listar',
                    'datos' => $archivo->listarArchivosLocales() // Usa el método local
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


    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
