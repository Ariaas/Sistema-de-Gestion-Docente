<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $p = new Perfil();
        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            $resultado = $p->Listar($usu_id);
            if ($resultado['resultado'] === 'consultar' && isset($_SESSION['name'])) {
                $resultado['mensaje']['usu_nombre'] = $_SESSION['name'];
            }
            echo json_encode($resultado);
        } elseif ($accion == 'modificar') {
            $p->set_usuarioId($usu_id);
            $p->set_correoUsuario($_POST['correoUsuario']);

            $fotoPerfil = '';
            if (!empty($_POST['fotoPerfil'])) {
                $fotoData = $_POST['fotoPerfil'];
                if (preg_match('/^data:image\/(png|jpg|jpeg|svg\+xml);base64,/', $fotoData, $type)) {
                    $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
                    $fotoData = base64_decode($fotoData);
                    $ext = $type[1] === 'svg+xml' ? 'svg' : $type[1];
                    $pedula = $_SESSION['cedula'] ?? $usu_id;

                    $extensions = ['png', 'jpg', 'jpeg', 'svg'];
                    foreach ($extensions as $oldExt) {
                        $oldFileName = "public/assets/profile/{$pedula}.{$oldExt}";
                        if (file_exists($oldFileName)) {
                            unlink($oldFileName);
                        }
                    }
                    $fileName = "public/assets/profile/{$pedula}.{$ext}";
                    file_put_contents($fileName, $fotoData);
                    $fotoPerfil = $fileName;
                } else if (strpos($fotoData, 'public/assets/profile/') === 0 || strpos($fotoData, 'public/assets/icons/') === 0) {
                    $fotoPerfil = $fotoData;
                }
            }
            $p->set_fotoPerfil($fotoPerfil);
            echo json_encode($p->Modificar());
            $bitacora->registrarAccion($usu_id, 'modificó su perfil', 'perfil');
        } else if ($_POST['accion'] === 'existe_correo_perfil') {
            require_once('model/perfil.php');
            $perfil = new Perfil();
            $correo = $_POST['correoUsuario'];
            $usuarioId = $_SESSION['usu_id'];
            echo json_encode($perfil->existeCorreo($correo, $usuarioId));
            exit;
        }
        exit;
    }
    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
