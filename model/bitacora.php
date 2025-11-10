<?php

namespace App\Model;

use PDO;
use Exception;

class Bitacora extends Connection_bitacora
{
    public function registrarAccion($usu_id, $accion, $modulo)
    {
        return $this->PostRegistrarAccion($usu_id, $accion, $modulo);
    }

    private function PostRegistrarAccion($usu_id, $accion, $modulo)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("INSERT INTO tbl_bitacora (usu_id, bit_modulo, bit_accion, bit_fecha, bit_estado) VALUES (?, ?, ?, NOW(6), 1)");
            return $stmt->execute([$usu_id, $modulo, $accion]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                return true;
            }
            throw $e;
        }
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $stmt = $co->query("SELECT tbl_usuario.usu_id, tbl_usuario.usu_nombre, bit_modulo, bit_accion, bit_fecha FROM tbl_usuario INNER JOIN tbl_bitacora ON tbl_usuario.usu_id = tbl_bitacora.usu_id ORDER BY bit_fecha DESC");
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
}
