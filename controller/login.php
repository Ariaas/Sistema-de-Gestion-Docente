<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['name']) && !empty($_SESSION['name'])) {
    header('Location: ?pagina=principal');
    exit();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta el modelo";
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST) && isset($_POST['accion'])) {
        $o = new Login();
        $h = $_POST['accion'];

        // Acción 'ingresar' - Para pruebas automatizadas (JMeter) - SIN CAPTCHA
        if ($h == 'ingresar') {
            // Solo permitir en localhost para seguridad
            $es_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' ||
                $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

            if (!$es_localhost) {
                echo json_encode(['resultado' => 'error', 'mensaje' => 'Acceso no permitido']);
                exit;
            }

            $o->set_nombreUsuario($_POST['usu_usuario']);
            $o->set_contraseniaUsuario($_POST['usu_clave']);
            $m = $o->existe();

            if ($m['resultado'] == 'existe') {
                session_destroy();
                session_start();
                $_SESSION['name'] = $m['mensaje'];
                $_SESSION['usu_id'] = $m['usu_id'];
                $fotoPerfil = $m['usu_foto'] ?? '';
                if (empty($fotoPerfil) || (!str_contains($fotoPerfil, 'public/assets/profile/') && !str_contains($fotoPerfil, 'public/assets/icons/'))) {
                    $fotoPerfil = 'public/assets/profile/sinPerfil.jpg';
                }
                $_SESSION['usu_foto'] = $fotoPerfil;
                $_SESSION['usu_docente'] = $m['usu_docente'];
                $_SESSION['usu_cedula'] = $m['usu_cedula'];
                $_SESSION['cedula'] = $m['mensaje'];

                $permisos = $o->get_permisos($m['usu_id']);
                $_SESSION['permisos'] = $permisos;

                $_SESSION['session_start'] = time();
                $_SESSION['last_activity'] = time();

                echo json_encode(['resultado' => 'ok', 'mensaje' => 'Login exitoso']);
                exit;
            } else {
                echo json_encode(['resultado' => 'error', 'mensaje' => $m['mensaje']]);
                exit;
            }
        }

        // Acción 'acceder' - Para usuarios normales - CON CAPTCHA
        if ($h == 'acceder') {
            $captcha = $_POST['g-recaptcha-response'] ?? '';
            if (!$o->validarCaptcha($captcha)) {
                $mensaje = "Captcha inválido. Intente de nuevo.";
            } else {
                $o->set_nombreUsuario($_POST['nombreUsuario']);
                $o->set_contraseniaUsuario($_POST['contraseniaUsuario']);
                $m = $o->existe();
                if ($m['resultado'] == 'existe') {
                    session_destroy();
                    session_start();
                    $_SESSION['name'] = $m['mensaje'];
                    $_SESSION['usu_id'] = $m['usu_id'];
                    $fotoPerfil = $m['usu_foto'] ?? '';
                    if (empty($fotoPerfil) || (!str_contains($fotoPerfil, 'public/assets/profile/') && !str_contains($fotoPerfil, 'public/assets/icons/'))) {
                        $fotoPerfil = 'public/assets/profile/sinPerfil.jpg';
                    }
                    $_SESSION['usu_foto'] = $fotoPerfil;
                    $_SESSION['usu_docente'] = $m['usu_docente'];
                    $_SESSION['usu_cedula'] = $m['usu_cedula'];
                    $_SESSION['cedula'] = $m['mensaje'];

                    $permisos = $o->get_permisos($m['usu_id']);
                    $_SESSION['permisos'] = $permisos;

                    // Establecer timestamps para control de sesión
                    $_SESSION['session_start'] = time();
                    $_SESSION['last_activity'] = time();

                    header('Location: ?pagina=principal');
                    die();
                } else {
                    $mensaje = $m['mensaje'];
                }
            }
        }
    }

    if (!empty($_POST) && isset($_GET['accion'])) {
        $o = new Login();

        if ($_GET['accion'] == 'recuperar') {
            $captcha = $_POST['g-recaptcha-response'] ?? '';
            if (!$o->validarCaptcha($captcha)) {
                echo "Captcha inválido. Intente de nuevo.";
                exit;
            }
            $usuario = $_POST['usuario'];
            echo $o->enviarCodigoRecuperacionPorUsuario($usuario);
            exit;
        }
        if ($_GET['accion'] == 'validarCodigo') {
            $usuario = $_POST['usuario'];
            $codigo = $_POST['codigo'];
            echo $o->validarCodigoRecuperacion($usuario, $codigo);
            exit;
        }
        if ($_GET['accion'] == 'cambiarClave') {
            $usuario = $_POST['usuario'];
            $codigo = $_POST['codigo'];
            $nuevaClave = $_POST['nuevaClave'];
            echo $o->cambiarClaveConToken($usuario, $codigo, $nuevaClave);
            exit;
        }
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Falta la vista";
}
