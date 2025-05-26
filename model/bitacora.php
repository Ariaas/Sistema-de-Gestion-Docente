<?php
require_once('model/db_bitacora.php');

class Bitacora extends Connection_bitacora
{
    public function registrarAccion($usu_id, $accion, $modulo)
    {
        $co = $this->Con();
        $stmt = $co->prepare("INSERT INTO tbl_bitacora (usu_id, bit_modulo, bit_accion, bit_fecha, bit_estado) VALUES (?, ?, ?, NOW(), 1)");
        return $stmt->execute([$usu_id, $modulo, $accion]);
    }
}
