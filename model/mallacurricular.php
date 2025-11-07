<?php

namespace App\Model;

use PDO;
use Exception;

class Malla extends Connection
{
    private $mal_cohorte;
    private $mal_codigo;
    private $mal_nombre;
    private $mal_descripcion;
    private $mal_activa;
    private $mal_codigo_original;

    public function __construct($mal_codigo = null, $mal_nombre = null, $mal_cohorte = null, $mal_descripcion = null, $mal_activa = null)
    {
        parent::__construct();
        $this->mal_codigo = $mal_codigo;
        $this->mal_nombre = $mal_nombre;
        $this->mal_cohorte = $mal_cohorte;
        $this->mal_descripcion = $mal_descripcion;
        $this->mal_activa = $mal_activa;
    }


    public function getMalCodigo(){ return $this->mal_codigo; }
    public function setMalCodigo($mal_codigo){ $this->mal_codigo = $mal_codigo; }
    public function getMalNombre(){ return $this->mal_nombre; }
    public function setMalNombre($mal_nombre){ $this->mal_nombre = $mal_nombre; }
    public function getMalCohorte(){ return $this->mal_cohorte; }
    public function setMalCohorte($mal_cohorte){ $this->mal_cohorte = $mal_cohorte; }
    public function getMalDescripcion(){ return $this->mal_descripcion; }
    public function setMalDescripcion($mal_descripcion){ $this->mal_descripcion = $mal_descripcion; }
    public function getMalActiva(){ return $this->mal_activa; }
    public function setMalActiva($mal_activa){ $this->mal_activa = $mal_activa; }
    public function getMalCodigoOriginal(){ return $this->mal_codigo_original; }
    public function setMalCodigoOriginal($mal_codigo_original){ $this->mal_codigo_original = $mal_codigo_original; }

    /**
     * Valida que todas las unidades curriculares tengan horas válidas (mayores a 0 y no nulas)
     * @param array $unidades Array de unidades curriculares con sus horas
     * @return array Resultado de la validación con 'resultado' y 'mensaje'
     */
    private function validarHorasUnidades($unidades)
    {
        foreach ($unidades as $index => $uc) {
            $uc_codigo = isset($uc['uc_codigo']) ? $uc['uc_codigo'] : 'Desconocida';
            
            if (!isset($uc['hora_independiente']) || $uc['hora_independiente'] === null || $uc['hora_independiente'] === '' ||
                !isset($uc['hora_asistida']) || $uc['hora_asistida'] === null || $uc['hora_asistida'] === '' ||
                !isset($uc['hora_academica']) || $uc['hora_academica'] === null || $uc['hora_academica'] === '') {
                return [
                    'resultado' => 'error',
                    'mensaje' => "Error: La unidad curricular '{$uc_codigo}' tiene campos de horas nulos o faltantes."
                ];
            }
            
            if ($uc['hora_independiente'] <= 0 || $uc['hora_asistida'] <= 0 || $uc['hora_academica'] <= 0) {
                return [
                    'resultado' => 'error',
                    'mensaje' => "Error: La unidad curricular '{$uc_codigo}' tiene horas inválidas (deben ser mayores a 0)."
                ];
            }
        }
        
        return ['resultado' => 'ok'];
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

    public function Registrar($unidades){
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (empty($unidades)) {
            return ['resultado' => 'error', 'mensaje' => 'No se han proporcionado unidades curriculares para registrar.'];
        }

        $validacion_horas = $this->validarHorasUnidades($unidades);
        if ($validacion_horas['resultado'] === 'error') {
            return $validacion_horas;
        }
        
        try {
            $codigos_uc = array_column($unidades, 'uc_codigo');
            $placeholders = implode(',', array_fill(0, count($codigos_uc), '?'));
            $stmt_trayectos = $co->prepare("SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN ($placeholders) AND uc_estado = 1");
            $stmt_trayectos->execute($codigos_uc);
            $trayectos_enviados = $stmt_trayectos->fetchAll(PDO::FETCH_COLUMN, 0);
            $trayectosRequeridos = ['0', '1', '2', '3', '4'];
            $trayectosFaltantes = array_diff($trayectosRequeridos, $trayectos_enviados);
            if (!empty($trayectosFaltantes)) {
                $nombresFaltantes = array_map(fn($t) => $t == '0' ? 'Inicial' : 'Trayecto ' . $t, $trayectosFaltantes);
                return ['resultado' => 'error', 'mensaje' => 'Error de validación: Debe incluir al menos una UC de los trayectos: ' . implode(', ', $nombresFaltantes)];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error al validar los trayectos: ' . $e->getMessage()];
        }
        
        
        $check_codigo = $this->Existecodigo();
        if (isset($check_codigo['resultado']) && $check_codigo['resultado'] === 'existe') {
            return $check_codigo;
        }
        $check_cohorte = $this->ExisteCohorte(false);
        if (isset($check_cohorte['resultado']) && $check_cohorte['resultado'] === 'existe') {
            return $check_cohorte;
        }

        try {
            $co->beginTransaction();
            $stmt = $co->prepare("INSERT INTO tbl_malla(mal_codigo, mal_nombre, mal_descripcion, mal_cohorte, mal_activa) VALUES (:mal_codigo, :mal_nombre, :mal_descripcion, :mal_cohorte, 1)");
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->execute();

            $stmt_pensum = $co->prepare("INSERT INTO uc_malla (mal_codigo, uc_codigo, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) VALUES (:mal_codigo, :uc_codigo, :hora_ind, :hora_asis, :hora_acad)");
            foreach ($unidades as $uc) {
                if ($uc['hora_independiente'] > 0 && $uc['hora_asistida'] > 0 && $uc['hora_academica'] > 0) {
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
            $r['mensaje'] = 'Registro Incluido!<br/> Se registró la malla curricular correctamente!';
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
        
        if (empty($unidades)) {
            return ['resultado' => 'error', 'mensaje' => 'No se han proporcionado unidades curriculares para modificar.'];
        }

        $validacion_horas = $this->validarHorasUnidades($unidades);
        if ($validacion_horas['resultado'] === 'error') {
            return $validacion_horas;
        }
        
        $check_cohorte = $this->ExisteCohorte(true);
        if (isset($check_cohorte['resultado']) && $check_cohorte['resultado'] === 'existe') {
            return $check_cohorte;
        }
        try {
            $co->beginTransaction();
            $codigo_actual = $this->mal_codigo;
            $codigo_original = $this->mal_codigo_original ?? $this->mal_codigo;
            if ($codigo_actual !== $codigo_original) {
                $stmt_check = $co->prepare("SELECT mal_codigo FROM tbl_malla WHERE mal_codigo = :mal_codigo");
                $stmt_check->bindParam(':mal_codigo', $codigo_actual, PDO::PARAM_STR);
                $stmt_check->execute();
                if ($stmt_check->fetch()) {
                    $co->rollBack();
                    return ['resultado' => 'error', 'mensaje' => 'El nuevo código ya está en uso por otra malla.'];
                }
            }
            $stmt = $co->prepare("UPDATE tbl_malla SET mal_codigo = :nuevo_codigo, mal_cohorte = :mal_cohorte, mal_nombre = :mal_nombre, mal_descripcion = :mal_descripcion WHERE mal_codigo = :mal_codigo_original");
            $stmt->bindParam(':nuevo_codigo', $codigo_actual, PDO::PARAM_STR);
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo_original', $codigo_original, PDO::PARAM_STR);
            $stmt->execute();
            
            $stmt_delete = $co->prepare("DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo_original");
            $stmt_delete->bindParam(':mal_codigo_original', $codigo_original, PDO::PARAM_STR);
            $stmt_delete->execute();

            $stmt_pensum = $co->prepare("INSERT INTO uc_malla (mal_codigo, uc_codigo, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) VALUES (:mal_codigo, :uc_codigo, :hora_ind, :hora_asis, :hora_acad)");
            foreach ($unidades as $uc) {
                if ($uc['hora_independiente'] > 0 && $uc['hora_asistida'] > 0 && $uc['hora_academica'] > 0) {
                    $stmt_pensum->bindParam(':mal_codigo', $codigo_actual, PDO::PARAM_STR);
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
            $stmt = $co->query("SELECT * FROM tbl_malla ORDER BY mal_activa DESC, mal_nombre ASC");
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

    public function Existecodigo()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            if (!empty($this->mal_codigo_original) && $this->mal_codigo === $this->mal_codigo_original) {
                $r['resultado'] = 'ok';
            } else {
                $sql = "SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo";
                $stmt = $co->prepare($sql);
                $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->fetch()) {
                    $r['resultado'] = 'existe';
                    $r['mensaje'] = '¡Atención! Este codigo ya existe.';
                } else {
                    $r['resultado'] = 'ok';
                }
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ExisteCohorte($is_modificar)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte";
            if ($is_modificar) {
                $codigo_a_excluir = !empty($this->mal_codigo_original) ? $this->mal_codigo_original : $this->mal_codigo;
                $sql .= " AND mal_codigo != :mal_codigo_excluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_INT);
            if ($is_modificar) {
                $stmt->bindParam(':mal_codigo_excluir', $codigo_a_excluir, PDO::PARAM_STR);
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

    public function obtenerUnidadesPorMalla(){
        
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