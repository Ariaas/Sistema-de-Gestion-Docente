<?php

require_once('model/db_bitacora.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-Gestion-Docente/public/lib/PHPMailer/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-Gestion-Docente/public/lib/PHPMailer/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-Gestion-Docente/public/lib/PHPMailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Login extends Connection_bitacora
{
    private $nombreUsuario;
    private $contraseniaUsuario;

    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function set_contraseniaUsuario($contraseniaUsuario)
    {
        $this->contraseniaUsuario = $contraseniaUsuario;
    }

    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function get_contraseniaUsuario()
    {
        return $this->contraseniaUsuario;
    }


    function existe()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $p = $co->prepare("SELECT usu_id, usu_nombre, usu_contrasenia, usu_estado FROM tbl_usuario 
            WHERE usu_nombre = :username AND usu_estado = 1");
            $p->bindParam(':username', $this->nombreUsuario);

            $p->execute();

            $fila = $p->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                $inputPassword = trim($this->contraseniaUsuario);
                $dbPassword = trim($fila['usu_contrasenia']);

                if (password_verify($inputPassword, $dbPassword)) {
                    $r['resultado'] = 'existe';
                    $r['mensaje'] = $fila['usu_nombre'];
                    $r['usu_id'] = $fila['usu_id'];
                } else {
                    $r['resultado'] = 'noexiste';
                    $r['mensaje'] = "Error en el usuario o contraseña!!!";
                }
            } else {
                $r['resultado'] = 'noexiste';
                $r['mensaje'] = "Error en el usuario o contraseña!!!";
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function enviarCodigoRecuperacionPorUsuario($usuario)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT usu_id, usu_correo FROM tbl_usuario WHERE usu_nombre = :usuario AND usu_estado = 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && !empty($user['usu_correo'])) {
                $token = bin2hex(random_bytes(4)); 
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $co->prepare("UPDATE tbl_usuario SET reset_token = :token, reset_token_expira = :expira WHERE usu_id = :id");
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expira', $expira);
                $stmt->bindParam(':id', $user['usu_id']);
                $stmt->execute();

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'chicuelomorrilloballena@gmail.com'; 
                    $mail->Password = 'rwzg ndcd uoam ybox'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('chicuelomorrilloballena@gmail.com', 'Sistema Docente');
                    $mail->addAddress($user['usu_correo']);
                   
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperación de contraseña';
                    $mail->Body    = "Su código de recuperación es: <b>$token</b><br>Este código es válido por 1 hora.";

                    $mail->send();
                    return "Código enviado al correo registrado.";
                } catch (Exception $e) {
                    return "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
                }
            } else {
                return "Usuario no encontrado o sin correo registrado.";
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function validarCodigoRecuperacion($usuario, $codigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT reset_token, reset_token_expira FROM tbl_usuario WHERE usu_nombre = :usuario AND usu_estado = 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['reset_token'] === $codigo) {
                if (strtotime($user['reset_token_expira']) >= time()) {
                    return "ok";
                } else {
                    return "El código ha expirado. Solicite uno nuevo.";
                }
            } else {
                return "Código incorrecto.";
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function cambiarClaveConToken($usuario, $codigo, $nuevaClave)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT usu_id, reset_token_expira FROM tbl_usuario WHERE usu_nombre = :usuario AND reset_token = :token AND usu_estado = 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':token', $codigo);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (strtotime($user['reset_token_expira']) < time()) {
                    return "El código ha expirado. Solicite uno nuevo.";
                }
                $hash = password_hash($nuevaClave, PASSWORD_DEFAULT);
                $stmt = $co->prepare("UPDATE tbl_usuario SET usu_contrasenia = :clave, reset_token = NULL, reset_token_expira = NULL WHERE usu_id = :id");
                $stmt->bindParam(':clave', $hash);
                $stmt->bindParam(':id', $user['usu_id']);
                $stmt->execute();
                return "¡Contraseña actualizada correctamente!";
            } else {
                return "Código o usuario incorrecto.";
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
