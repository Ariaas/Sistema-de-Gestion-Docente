<?php
require_once('model/dbconnection.php');

class Malla extends Connection
{
    private $mal_cohorte;
    private $mal_codigo;
    private $mal_nombre;
    private $mal_descripcion;
    private $mal_estado;
    private $mal_activa;

    public function __construct($mal_codigo = null, $mal_nombre = null, $mal_cohorte = null, $mal_descripcion = null, $mal_estado = null, $mal_activa = null)
    {
        parent::__construct();

        $this->mal_codigo = $mal_codigo;
        $this->mal_nombre = $mal_nombre;
        $this->mal_cohorte = $mal_cohorte;
        $this->mal_descripcion = $mal_descripcion;
        $this->mal_estado = $mal_estado;
        $this->mal_activa = $mal_activa;
    }

    public function getMalCodigo()
    {
        return $this->mal_codigo;
    }
    public function setMalCodigo($mal_codigo)
    {
        $this->mal_codigo = $mal_codigo;
    }

    public function getMalNombre()
    {
        return $this->mal_nombre;
    }
    public function setMalNombre($mal_nombre)
    {
        $this->mal_nombre = $mal_nombre;
    }

    public function getMalCohorte()
    {
        return $this->mal_cohorte;
    }
    public function setMalCohorte($mal_cohorte)
    {
        $this->mal_cohorte = $mal_cohorte;
    }

    public function getMalDescripcion()
    {
        return $this->mal_descripcion;
    }
    public function setMalDescripcion($mal_descripcion)
    {
        $this->mal_descripcion = $mal_descripcion;
    }

    public function getMalEstado()
    {
        return $this->mal_estado;
    }
    public function setMalEstado($mal_estado)
    {
        $this->mal_estado = $mal_estado;
    }

    public function getMalActiva()
    {
        return $this->mal_activa;
    }
    public function setMalActiva($mal_activa)
    {
        $this->mal_activa = $mal_activa;
    }


    public function verificarCondicionesParaRegistrar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['puede_registrar' => false, 'mensaje' => ''];

        try {
            $stmt_uc_total = $co->query("SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1");
            if ($stmt_uc_total->fetchColumn() == 0) {
                $r['mensaje'] = 'No hay unidades curriculares registradas. Debe registrar unidades antes de crear una malla.';
                return $r;
            }

            $stmt_trayectos = $co->query("SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_estado = 1");
            $trayectos_existentes = $stmt_trayectos->fetchAll(PDO::FETCH_COLUMN, 0);
            $trayectos_requeridos = ['0', '1', '2', '3', '4'];

            $faltantes = array_diff($trayectos_requeridos, $trayectos_existentes);

            if (!empty($faltantes)) {
                $nombres_faltantes = [];
                foreach ($faltantes as $t) {
                    $nombres_faltantes[] = ($t == '0') ? "Inicial" : $t;
                }
                $r['mensaje'] = 'Faltan unidades curriculares en los trayectos: ' . implode(', ', $nombres_faltantes) . '.';
                return $r;
            }

            $r['puede_registrar'] = true;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Registrar($unidades)
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $check_codigo = $this->Existecodigo();
        if (isset($check_codigo['resultado']) && $check_codigo['resultado'] === 'existe') {
            return $check_codigo;
        }

        $check_cohorte = $this->ExisteCohorte();
        if (isset($check_cohorte['resultado']) && $check_cohorte['resultado'] === 'existe') {
            return $check_cohorte;
        }

        $stmt_inactiva = $co->prepare("SELECT mal_codigo FROM tbl_malla WHERE mal_codigo = :mal_codigo AND mal_estado = 0");
        $stmt_inactiva->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
        $stmt_inactiva->execute();
        if ($stmt_inactiva->fetch()) {
            try {
                $co->beginTransaction();
                $stmt_reactivar = $co->prepare("UPDATE tbl_malla SET mal_nombre = :mal_nombre, mal_descripcion = :mal_descripcion, mal_cohorte = :mal_cohorte, mal_estado = 1, mal_activa = 0 WHERE mal_codigo = :mal_codigo");
                $stmt_reactivar->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                $stmt_reactivar->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
                $stmt_reactivar->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
                $stmt_reactivar->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
                $stmt_reactivar->execute();

                $stmt_delete = $co->prepare("DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo");
                $stmt_delete->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                $stmt_delete->execute();

                $stmt_pensum = $co->prepare("INSERT INTO uc_malla (mal_codigo, uc_codigo, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) VALUES (:mal_codigo, :uc_codigo, :hora_ind, :hora_asis, :hora_acad)");
                foreach ($unidades as $uc) {
                    if ($uc['hora_independiente'] > 0 || $uc['hora_asistida'] > 0 || $uc['hora_academica'] > 0) {
                        $stmt_pensum->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                        $stmt_pensum->bindParam(':uc_codigo', $uc['uc_codigo'], PDO::PARAM_STR);
                        $stmt_pensum->bindParam(':hora_ind', $uc['hora_independiente'], PDO::PARAM_INT);
                        $stmt_pensum->bindParam(':hora_asis', $uc['hora_asistida'], PDO::PARAM_INT);
                        $stmt_pensum->bindParam(':hora_acad', $uc['hora_academica'], PDO::PARAM_INT);
                        $stmt_pensum->execute();
                    }
                }
                $co->commit();
                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se reactivó y actualizó la malla curricular correctamente!';
            } catch (Exception $e) {
                $co->rollBack();
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            $co = null;
            return $r;
        }

        try {
            $co->beginTransaction();



            $stmt = $co->prepare("INSERT INTO tbl_malla( mal_codigo, mal_nombre, mal_descripcion, mal_cohorte, mal_estado, mal_activa) VALUES (:mal_codigo, :mal_nombre, :mal_descripcion, :mal_cohorte, 1, 0)");
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->execute();

            $stmt_pensum = $co->prepare("INSERT INTO uc_malla (mal_codigo, uc_codigo, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) VALUES (:mal_codigo, :uc_codigo, :hora_ind, :hora_asis, :hora_acad)");
            foreach ($unidades as $uc) {
                if ($uc['hora_independiente'] > 0 || $uc['hora_asistida'] > 0 || $uc['hora_academica'] > 0) {
                    $stmt_pensum->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                    $stmt_pensum->bindParam(':uc_codigo', $uc['uc_codigo'], PDO::PARAM_STR);
                    $stmt_pensum->bindParam(':hora_ind', $uc['hora_independiente'], PDO::PARAM_INT);
                    $stmt_pensum->bindParam(':hora_asis', $uc['hora_asistida'], PDO::PARAM_INT);
                    $stmt_pensum->bindParam(':hora_acad', $uc['hora_academica'], PDO::PARAM_INT);
                    $stmt_pensum->execute();
                }
            }
            $co->commit();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/> Se registró y activó la malla curricular correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Modificar($unidades)
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $check_cohorte = $this->ExisteCohorte(true);
        if (isset($check_cohorte['resultado']) && $check_cohorte['resultado'] === 'existe') {
            return $check_cohorte;
        }

        try {
            $co->beginTransaction();
            $stmt = $co->prepare("UPDATE tbl_malla SET mal_cohorte = :mal_cohorte, mal_nombre = :mal_nombre, mal_descripcion = :mal_descripcion WHERE mal_codigo = :mal_codigo AND mal_estado = 1");
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->execute();

            $stmt_delete = $co->prepare("DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo");
            $stmt_delete->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt_delete->execute();

            $stmt_pensum = $co->prepare("INSERT INTO uc_malla (mal_codigo, uc_codigo, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) VALUES (:mal_codigo, :uc_codigo, :hora_ind, :hora_asis, :hora_acad)");
            foreach ($unidades as $uc) {
                if ($uc['hora_independiente'] > 0 || $uc['hora_asistida'] > 0 || $uc['hora_academica'] > 0) {
                    $stmt_pensum->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                    $stmt_pensum->bindParam(':uc_codigo', $uc['uc_codigo'], PDO::PARAM_STR);
                    $stmt_pensum->bindParam(':hora_ind', $uc['hora_independiente'], PDO::PARAM_INT);
                    $stmt_pensum->bindParam(':hora_asis', $uc['hora_asistida'], PDO::PARAM_INT);
                    $stmt_pensum->bindParam(':hora_acad', $uc['hora_academica'], PDO::PARAM_INT);
                    $stmt_pensum->execute();
                }
            }
            $co->commit();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la malla curricular correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_malla WHERE mal_estado = 1 ORDER BY mal_activa DESC, mal_nombre ASC");
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

    public function cambiarEstadoActivo()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $co->beginTransaction();


            $stmt_current = $co->prepare("SELECT mal_activa FROM tbl_malla WHERE mal_codigo = :mal_codigo");
            $stmt_current->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt_current->execute();
            $estado_actual = $stmt_current->fetchColumn();

            if ($estado_actual == 1) {

                $nuevo_estado = 0;
                $accion_bitacora = 'desactivar';
                $mensaje_exito = '¡Malla desactivada correctamente!';
            } else {

                $nuevo_estado = 1;
                $accion_bitacora = 'activar';
                $mensaje_exito = '¡Malla activada correctamente!';
            }


            $stmt_cambiar = $co->prepare("UPDATE tbl_malla SET mal_activa = :nuevo_estado WHERE mal_codigo = :mal_codigo");
            $stmt_cambiar->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_INT);
            $stmt_cambiar->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt_cambiar->execute();

            $co->commit();
            $r['resultado'] = 'ok';
            $r['mensaje'] = $mensaje_exito;
            $r['accion_bitacora'] = $accion_bitacora;
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_malla SET mal_estado = 0, mal_activa = 0 WHERE mal_codigo = :mal_codigo");
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la malla curricular correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existecodigo()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo AND mal_estado = 1";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch()) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = '¡Atención! Este codigo ya existe.';
            } else {
                $r['resultado'] = 'ok';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ExisteCohorte($is_modificar = false)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte AND mal_estado = 1";
            if ($is_modificar) {
                $sql .= " AND mal_codigo != :mal_codigo";
            }

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_INT);
            if ($is_modificar) {
                $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = '¡Atención!, Esta cohorte ya existe.';
            } else {
                $r['resultado'] = 'ok';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function obtenerUnidadesCurriculares()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT uc_codigo, uc_nombre, uc_trayecto FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_trayecto ASC, uc_nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function obtenerUnidadesPorMalla()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT p.uc_codigo, u.uc_nombre, u.uc_trayecto, p.mal_hora_independiente, p.mal_hora_asistida, p.mal_hora_academica FROM uc_malla p INNER JOIN tbl_uc u ON p.uc_codigo = u.uc_codigo WHERE p.mal_codigo = :mal_codigo ORDER BY u.uc_trayecto ASC, u.uc_nombre ASC");
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
