<?php
require_once('model/dbconnection.php');

class Notificaciones extends Connection
{
    public function RegistrarNotificacion($notificacion, $fin)
    {
        $co = $this->Con();
        $stmt = $co->prepare("INSERT INTO tbl_notificacion (not_notificacion, not_fecha, not_fin) VALUES (?, NOW(), ?)");
        return $stmt->execute([$notificacion, $fin]);
    }

    public function Listar()
    {
        $this->Desactivar();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $stmt = $co->query("SELECT not_notificacion FROM tbl_notificacion 
                WHERE not_estado = 1 AND not_fin >= NOW()
                ORDER BY not_fecha DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Desactivar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("UPDATE tbl_notificacion SET not_estado = 0 WHERE not_fin < NOW() AND not_estado = 1");
            $stmt->execute();
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
            return $r;
        }
        $co = null;
    }

    public function existeNotificacion($mensaje, $fin)
    {
        $co = $this->Con();
        $stmt = $co->prepare("SELECT COUNT(*) FROM tbl_notificacion WHERE not_notificacion = ? AND not_fin = ? AND not_estado = 1");
        $stmt->execute([$mensaje, $fin]);
        return $stmt->fetchColumn() > 0;
    }
}
