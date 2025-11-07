<?php

namespace App\Model;

use PDO;
use Exception;

class Permisos extends Connection
{
    function obtenerPermisosPorUsuario($usu_id)
    {
        $permisos = [];
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT per_modulo, per_permisos FROM tbl_permisos WHERE usu_id = :usu_id AND per_estado = 1";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permisos[$row['per_modulo']] = $row['per_permisos'];
            }
        } catch (Exception $e) {
            $permisos['error'] = 'Error' . $e->getMessage();
        }
        return $permisos;
    }
}
