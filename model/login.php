<?php

require_once('model/dbconnection.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Login extends Connection
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
            $p = $co->prepare("SELECT usu_id,usu_cedula, usu_nombre, usu_contrasenia, usu_foto, usu_estado, usu_docente FROM tbl_usuario 
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
                    $r['usu_foto'] = $fila['usu_foto'];
                    $r['usu_docente'] = $fila['usu_docente'];
                    $r['usu_cedula'] = $fila['usu_cedula'];
                } else {
                    $r['resultado'] = 'noexiste';
                    $r['mensaje'] = "Verifique su usuario o contraseña";
                }
            } else {
                $r['resultado'] = 'noexiste';
                $r['mensaje'] = "Verifique su usuario o contraseña";
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

    public function validarCaptcha($token)
    {
        $secret = '6LeahHErAAAAAE7NIWRPVeJGe6Gq6IB2M3laWOY0';
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$token}");
        $result = json_decode($response, true);
        return $result['success'] ?? false;
    }

    public function get_permisos($usu_id)
    {
        $permisos = [];
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt_rol = $co->prepare("SELECT rol_id FROM tbl_usuario WHERE usu_id = :usu_id");
            $stmt_rol->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt_rol->execute();
            $usuario = $stmt_rol->fetch(PDO::FETCH_ASSOC);

            if ($usuario && !empty($usuario['rol_id'])) {
                $rol_id = $usuario['rol_id'];

              $sql = "SELECT p.per_modulo, rp.per_accion
                    FROM rol_permisos rp
                    JOIN tbl_permisos p ON rp.per_id = p.per_id
                    WHERE rp.rol_id = :rol_id AND p.per_estado = 1";

            $stmt_permisos = $co->prepare($sql);
                $stmt_permisos->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
                $stmt_permisos->execute();

                while ($row = $stmt_permisos->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($permisos[$row['per_modulo']])) {
                        $permisos[$row['per_modulo']] = [];
                    }
                    $permisos[$row['per_modulo']][] = $row['per_accion'];
                }
            }
        } catch (Exception $e) {
            error_log("Error al obtener permisos: " . $e->getMessage());
            return [];
        }
        return $permisos;
    }
}
