<?php
require_once('model/dbconnection.php');

class Docente extends Connection
{
    private $cat_nombre;
    private $doc_prefijo;
    private $doc_cedula;
    private $doc_nombre;
    private $doc_apellido;
    private $doc_correo;
    private $doc_dedicacion;
    private $doc_condicion;
    private $doc_tipo_concurso;
    private $doc_ingreso;
    private $doc_anio_concurso;
    private $doc_observacion;
    private $titulos = array();
    private $coordinaciones = array();
    private $horasAcademicas;
    private $creacionIntelectual;
    private $integracionComunidad;
    private $gestionAcademica;
    private $otras;
    private $preferencias = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function setCondicion($condicion)
    {
        $this->doc_condicion = $condicion;
    }
    public function setTipoConcurso($concurso)
    {
        $this->doc_tipo_concurso = empty($concurso) ? null : $concurso;
    }
    public function setDedicacion($dedicacion)
    {
        $this->doc_dedicacion = $dedicacion;
    }
    public function setCedula($doc_cedula)
    {
        $this->doc_cedula = $doc_cedula;
    }
    public function setNombre($doc_nombre)
    {
        $this->doc_nombre = $doc_nombre;
    }
    public function setApellido($doc_apellido)
    {
        $this->doc_apellido = $doc_apellido;
    }
    public function setCorreo($doc_correo)
    {
        $this->doc_correo = $doc_correo;
    }
    public function setPrefijo($doc_prefijo)
    {
        $this->doc_prefijo = $doc_prefijo;
    }
    public function setCategoriaNombre($cat_nombre)
    {
        $this->cat_nombre = $cat_nombre;
    }
    public function setIngreso($doc_ingreso)
    {
        $this->doc_ingreso = $doc_ingreso;
    }
    public function setAnioConcurso($doc_anio_concurso)
    {
        $this->doc_anio_concurso = empty($doc_anio_concurso) ? null : $doc_anio_concurso;
    }
    public function setObservacion($doc_observacion)
    {
        $this->doc_observacion = $doc_observacion;
    }
    public function setTitulos($titulos)
    {
        $this->titulos = $titulos;
    }
    public function setCoordinaciones($coordinaciones)
    {
        $this->coordinaciones = $coordinaciones;
    }
    public function setHorasAcademicas($horas)
    {
        $this->horasAcademicas = $horas;
    }
    public function setCreacionIntelectual($creacion)
    {
        $this->creacionIntelectual = $creacion;
    }
    public function setIntegracionComunidad($integracion)
    {
        $this->integracionComunidad = $integracion;
    }
    public function setGestionAcademica($gestion)
    {
        $this->gestionAcademica = $gestion;
    }
    public function setOtras($otras)
    {
        $this->otras = $otras;
    }
    public function setPreferencias($preferencias)
    {
        $this->preferencias = $preferencias;
    }

    private function buscarEstadoPorCedula($doc_cedula)
    {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT doc_estado FROM tbl_docente WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['doc_estado'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function _actualizarDatosDocente()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $co->beginTransaction();
            $this->_validarFechaConcurso();
            $errorValidacionCarga = $this->ValidarCargaHoraria();
            if ($errorValidacionCarga) {
                throw new Exception($errorValidacionCarga['mensaje']);
            }
            $this->_validarPreferenciasHorario();

            $stmt = $co->prepare("UPDATE tbl_docente SET doc_nombre = :doc_nombre, doc_apellido = :doc_apellido, doc_correo = :doc_correo, cat_nombre = :cat_nombre, doc_prefijo = :doc_prefijo, doc_dedicacion = :doc_dedicacion, doc_condicion = :doc_condicion, doc_ingreso = :doc_ingreso, doc_anio_concurso = :doc_anio_concurso, doc_tipo_concurso = :doc_tipo_concurso, doc_observacion = :doc_observacion, doc_estado = 1 WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_nombre' => $this->doc_nombre, ':doc_apellido' => $this->doc_apellido, ':doc_correo' => $this->doc_correo, ':cat_nombre' => $this->cat_nombre, ':doc_prefijo' => $this->doc_prefijo, ':doc_dedicacion' => $this->doc_dedicacion, ':doc_condicion' => $this->doc_condicion, ':doc_ingreso' => $this->doc_ingreso, ':doc_anio_concurso' => $this->doc_anio_concurso, ':doc_tipo_concurso' => $this->doc_tipo_concurso, ':doc_observacion' => $this->doc_observacion, ':doc_cedula' => $this->doc_cedula]);

            $stmt_eliminar_titulos = $co->prepare("DELETE FROM titulo_docente WHERE doc_cedula = :doc_cedula");
            $stmt_eliminar_titulos->execute([':doc_cedula' => $this->doc_cedula]);

            if (!empty($this->titulos)) {
                $stmt_titulos = $co->prepare("INSERT INTO titulo_docente (doc_cedula, tit_prefijo, tit_nombre) VALUES (:doc_cedula, :tit_prefijo, :tit_nombre)");
                foreach ($this->titulos as $titulo_compuesto) {
                    list($tit_prefijo, $tit_nombre) = explode('::', $titulo_compuesto);
                    $stmt_titulos->execute([':doc_cedula' => $this->doc_cedula, ':tit_prefijo' => $tit_prefijo, ':tit_nombre' => $tit_nombre]);
                }
            }

            $stmt_eliminar_coordinaciones = $co->prepare("DELETE FROM coordinacion_docente WHERE doc_cedula = :doc_cedula");
            $stmt_eliminar_coordinaciones->execute([':doc_cedula' => $this->doc_cedula]);

            if (!empty($this->coordinaciones)) {
                $stmt_coordinaciones = $co->prepare("INSERT INTO coordinacion_docente (doc_cedula, cor_nombre, cor_doc_estado) VALUES (:doc_cedula, :cor_nombre, 1)");
                foreach ($this->coordinaciones as $cor_nombre) {
                    $stmt_coordinaciones->execute([':doc_cedula' => $this->doc_cedula, ':cor_nombre' => $cor_nombre]);
                }
            }

            $this->_guardarActividad($co);
            $this->_guardarPreferenciasHorario($co);

            $co->commit();
            $r['resultado'] = 'ok';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Registrar()
    {
        $r = array();
        $estado_docente = $this->buscarEstadoPorCedula($this->doc_cedula);
        if ($estado_docente == '1') {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡Error!<br/>La cédula ingresada ya se encuentra registrada para un docente activo.';
            return $r;
        }

        if ($estado_docente == '0') {
            $resultado_actualizacion = $this->_actualizarDatosDocente();
            if ($resultado_actualizacion['resultado'] === 'ok') {
                $r['resultado'] = 'incluir';
                $r['mensaje'] = '¡Registro Incluido!<br/> El docente ha sido reactivado y actualizado correctamente.';
            } else {
                return $resultado_actualizacion;
            }
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $this->_validarFechaConcurso();
            $errorValidacionCarga = $this->ValidarCargaHoraria();
            if ($errorValidacionCarga) {
                throw new Exception($errorValidacionCarga['mensaje']);
            }
            $this->_validarPreferenciasHorario();

            $stmt = $co->prepare("INSERT INTO tbl_docente(cat_nombre, doc_prefijo, doc_cedula, doc_nombre, doc_apellido, doc_correo, doc_dedicacion, doc_condicion, doc_ingreso, doc_anio_concurso, doc_tipo_concurso, doc_observacion, doc_estado) VALUES (:cat_nombre, :doc_prefijo, :doc_cedula, :doc_nombre, :doc_apellido, :doc_correo, :doc_dedicacion, :doc_condicion, :doc_ingreso, :doc_anio_concurso, :doc_tipo_concurso, :doc_observacion, 1)");
            $stmt->execute([':cat_nombre' => $this->cat_nombre, ':doc_prefijo' => $this->doc_prefijo, ':doc_cedula' => $this->doc_cedula, ':doc_nombre' => $this->doc_nombre, ':doc_apellido' => $this->doc_apellido, ':doc_correo' => $this->doc_correo, ':doc_dedicacion' => $this->doc_dedicacion, ':doc_condicion' => $this->doc_condicion, ':doc_ingreso' => $this->doc_ingreso, ':doc_anio_concurso' => $this->doc_anio_concurso, ':doc_tipo_concurso' => $this->doc_tipo_concurso, ':doc_observacion' => $this->doc_observacion]);

            if (!empty($this->titulos)) {
                $stmt_titulos = $co->prepare("INSERT INTO titulo_docente (doc_cedula, tit_prefijo, tit_nombre) VALUES (:doc_cedula, :tit_prefijo, :tit_nombre)");
                foreach ($this->titulos as $titulo_compuesto) {
                    list($tit_prefijo, $tit_nombre) = explode('::', $titulo_compuesto);
                    $stmt_titulos->execute([':doc_cedula' => $this->doc_cedula, ':tit_prefijo' => $tit_prefijo, ':tit_nombre' => $tit_nombre]);
                }
            }
            if (!empty($this->coordinaciones)) {
                $stmt_coordinaciones = $co->prepare("INSERT INTO coordinacion_docente (doc_cedula, cor_nombre, cor_doc_estado) VALUES (:doc_cedula, :cor_nombre, 1)");
                foreach ($this->coordinaciones as $cor_nombre) {
                    $stmt_coordinaciones->execute([':doc_cedula' => $this->doc_cedula, ':cor_nombre' => $cor_nombre]);
                }
            }

            $this->_guardarActividad($co);
            $this->_guardarPreferenciasHorario($co);

            $co->commit();
            $r['resultado'] = 'incluir';
            $r['mensaje'] = '¡Registro Incluido!<br/> Se registró el docente, actividades y preferencias correctamente';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Modificar()
    {
        $r = array();
        if ($this->existe($this->doc_cedula)) {
            $resultado_actualizacion = $this->_actualizarDatosDocente();
            if ($resultado_actualizacion['resultado'] === 'ok') {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = '¡Registro Modificado!<br/> Se modificó el docente, actividades y preferencias correctamente';
            } else {
                return $resultado_actualizacion;
            }
        } else {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡ERROR!<br/> El docente no existe o está inactivo.';
        }
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->existe($this->doc_cedula)) {
            try {
                $co->beginTransaction();

                $stmt_act = $co->prepare("UPDATE tbl_actividad SET act_estado = 0 WHERE doc_cedula = :doc_cedula");
                $stmt_act->execute([':doc_cedula' => $this->doc_cedula]);

                $stmt_pref = $co->prepare("DELETE FROM tbl_docente_preferencia WHERE doc_cedula = :doc_cedula");
                $stmt_pref->execute([':doc_cedula' => $this->doc_cedula]);

                $stmt_uc = $co->prepare("DELETE FROM uc_docente WHERE doc_cedula = :doc_cedula");
                $stmt_uc->execute([':doc_cedula' => $this->doc_cedula]);

                $stmt = $co->prepare("UPDATE tbl_docente SET doc_estado = 0 WHERE doc_cedula = :doc_cedula");
                $stmt->execute([':doc_cedula' => $this->doc_cedula]);

                $co->commit();
                $r['resultado'] = 'eliminar';
                $r['mensaje'] = '¡Registro Eliminado!<br/> Se eliminó el docente y sus datos asociados correctamente';
            } catch (Exception $e) {
                $co->rollBack();
                $r['resultado'] = 'error';
                $r['mensaje'] = 'No se puede eliminar. Puede que esté asociado a otros registros.';
            }
        } else {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡ERROR!<br/> El docente no existe o ya está eliminado.';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $stmt = $co->prepare("SELECT d.doc_prefijo, d.doc_cedula, d.doc_nombre, d.doc_apellido, d.doc_correo, d.doc_dedicacion, d.doc_condicion, d.doc_ingreso, d.doc_anio_concurso, d.doc_tipo_concurso, d.doc_observacion, d.cat_nombre FROM tbl_docente d WHERE d.doc_estado = 1");
            $stmt->execute();
            $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($docentes as &$docente) {
                $doc_cedula = $docente['doc_cedula'];

                $stmtTitulos = $co->prepare("SELECT GROUP_CONCAT(t.tit_nombre SEPARATOR ', ') AS titulos, GROUP_CONCAT(CONCAT(t.tit_prefijo, '::', t.tit_nombre) SEPARATOR ',') AS titulos_ids FROM titulo_docente td JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre WHERE td.doc_cedula = :doc_cedula AND t.tit_estado = 1");
                $stmtTitulos->execute([':doc_cedula' => $doc_cedula]);
                $titulosData = $stmtTitulos->fetch(PDO::FETCH_ASSOC);

                $docente['titulos'] = $titulosData['titulos'] ?? 'Sin títulos';
                $docente['titulos_ids'] = $titulosData['titulos_ids'] ?? '';

                $stmtCoordinaciones = $co->prepare("SELECT GROUP_CONCAT(c.cor_nombre SEPARATOR ', ') AS coordinaciones, GROUP_CONCAT(c.cor_nombre SEPARATOR ',') AS coordinaciones_ids FROM coordinacion_docente cd JOIN tbl_coordinacion c ON cd.cor_nombre = c.cor_nombre WHERE cd.doc_cedula = :doc_cedula AND c.cor_estado = 1");
                $stmtCoordinaciones->execute([':doc_cedula' => $doc_cedula]);
                $coordinacionesData = $stmtCoordinaciones->fetch(PDO::FETCH_ASSOC);

                $docente['coordinaciones'] = $coordinacionesData['coordinaciones'] ?? 'Sin coordinaciones';
                $docente['coordinaciones_ids'] = $coordinacionesData['coordinaciones_ids'] ?? '';
            }

            $r['resultado'] = 'consultar';
            $r['mensaje'] = $docentes;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Existe($doc_cedula)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT * FROM tbl_docente WHERE doc_cedula = :doc_cedula AND doc_estado = 1");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function existeCorreo($correo, $cedula_actual = null)
    {
        $co = $this->Con();
        try {
            $sql_docente = "SELECT doc_cedula, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_correo = :correo AND doc_estado = 1";
            $stmt_docente = $co->prepare($sql_docente);
            $stmt_docente->execute([':correo' => $correo]);
            $docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);

            if ($docente) {
                if ($cedula_actual && $docente['doc_cedula'] == $cedula_actual) {
                    return ['resultado' => 'no_existe'];
                } else {
                    return [
                        'resultado' => 'existe_docente',
                        'mensaje' => 'El correo ya está asignado al docente (' . $docente['doc_nombre'] . ' ' . $docente['doc_apellido'] . ').'
                    ];
                }
            }

            $sql_usuario = "SELECT usu_id, usu_cedula FROM tbl_usuario WHERE usu_correo = :correo AND usu_estado = 1";
            $stmt_usuario = $co->prepare($sql_usuario);
            $stmt_usuario->execute([':correo' => $correo]);
            $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if ($cedula_actual && $usuario['usu_cedula'] == $cedula_actual) {
                } else {
                    return [
                        'resultado' => 'existe',
                        'mensaje' => 'Este correo ya está en uso por un usuario.'
                    ];
                }
            }

            return ['resultado' => 'no_existe'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public function listacategoria()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_categoria WHERE cat_estado = 1");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listatitulo()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_titulo WHERE tit_estado = 1");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listaCoordinacion()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_coordinacion WHERE cor_estado = 1");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ObtenerHorasActividad($doc_cedula)
    {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT act_academicas, act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras FROM tbl_actividad WHERE doc_cedula = :doc_cedula AND act_estado = 1");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                return ['resultado' => 'consultar_horas', 'mensaje' => $resultado];
            } else {
                return ['resultado' => 'horas_no_encontradas', 'mensaje' => ['act_academicas' => '0', 'act_creacion_intelectual' => '0', 'act_integracion_comunidad' => '0', 'act_gestion_academica' => '0', 'act_otras' => '0']];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public function ObtenerPreferenciasHorario($doc_cedula)
    {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT dia_semana, hora_inicio, hora_fin FROM tbl_docente_preferencia WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $preferencias_por_dia = [];
            foreach ($resultados as $fila) {
                $preferencias_por_dia[$fila['dia_semana']] = [
                    'inicio' => $fila['hora_inicio'],
                    'fin' => $fila['hora_fin']
                ];
            }
            return ['resultado' => 'ok', 'mensaje' => $preferencias_por_dia];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    private function ValidarCargaHoraria()
    {
        if ($this->horasAcademicas < 0 || $this->creacionIntelectual < 0 || $this->integracionComunidad < 0 || $this->gestionAcademica < 0 || $this->otras < 0) {
            return ['resultado' => 'error', 'mensaje' => 'Los valores de horas no pueden ser negativos.'];
        }

        $cargaTotal = (int)$this->horasAcademicas + (int)$this->creacionIntelectual + (int)$this->integracionComunidad + (int)$this->gestionAcademica + (int)$this->otras;
        $dedicacion = strtolower($this->doc_dedicacion);
        $maxHoras = 0;

        switch ($dedicacion) {
            case 'exclusiva':
                $maxHoras = 42;
                break;
            case 'tiempo completo':
                $maxHoras = 36;
                break;
            case 'medio tiempo':
                $maxHoras = 21;
                break;
            case 'tiempo convencional':
                $maxHoras = 7;
                break;
        }

        if ($maxHoras > 0 && $cargaTotal > $maxHoras) {
            return ['resultado' => 'error', 'mensaje' => "La carga horaria total ($cargaTotal horas) excede el límite de $maxHoras para la dedicación '{$this->doc_dedicacion}'."];
        }

        if ($dedicacion == 'tiempo convencional' && $cargaTotal > 7) {
            return ['resultado' => 'error', 'mensaje' => "La carga horaria total ($cargaTotal horas) excede el límite de 7 para la dedicación 'Tiempo Convencional'."];
        }

        return null;
    }


    private function _validarFechaConcurso()
    {
        if ($this->doc_anio_concurso !== null) {
            $fechaConcurso = new DateTime($this->doc_anio_concurso);
            $hoy = new DateTime();
            $hoy->setTime(0, 0, 0);

            if ($fechaConcurso > $hoy) {
                throw new Exception("La fecha del concurso no puede ser una fecha futura.");
            }
        }
    }


    private function _validarPreferenciasHorario()
    {
        if (empty($this->preferencias)) {
            return;
        }

        $horaMinima = '07:00:00';
        $horaMaxima = '23:00:00';

        foreach ($this->preferencias as $dia => $horas) {
            if (isset($horas['activado'])) {
                $inicio = $horas['inicio'] ?? null;
                $fin = $horas['fin'] ?? null;

                if ($inicio && $fin) {
                    if ($inicio < $horaMinima || $fin < $horaMinima || $inicio > $horaMaxima || $fin > $horaMaxima) {
                        throw new Exception("Error en el día " . ucfirst($dia) . ": Las horas deben estar entre las 07:00 AM y las 11:00 PM.");
                    }
                }
            }
        }
    }

    private function _guardarActividad($co)
    {
        $stmt_check = $co->prepare("SELECT doc_cedula FROM tbl_actividad WHERE doc_cedula = :doc_cedula");
        $stmt_check->bindParam(':doc_cedula', $this->doc_cedula, PDO::PARAM_INT);
        $stmt_check->execute();

        if ($stmt_check->fetch()) {
            $stmt = $co->prepare("UPDATE tbl_actividad SET act_academicas = :academicas, act_creacion_intelectual = :creacion, act_integracion_comunidad = :integracion, act_gestion_academica = :gestion, act_otras = :otras, act_estado = 1 WHERE doc_cedula = :doc_cedula");
        } else {
            $stmt = $co->prepare("INSERT INTO tbl_actividad (doc_cedula, act_academicas, act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras, act_estado) VALUES (:doc_cedula, :academicas, :creacion, :integracion, :gestion, :otras, 1)");
        }
        $stmt->bindParam(':doc_cedula', $this->doc_cedula, PDO::PARAM_INT);
        $stmt->bindParam(':academicas', $this->horasAcademicas, PDO::PARAM_INT);
        $stmt->bindParam(':creacion', $this->creacionIntelectual, PDO::PARAM_INT);
        $stmt->bindParam(':integracion', $this->integracionComunidad, PDO::PARAM_INT);
        $stmt->bindParam(':gestion', $this->gestionAcademica, PDO::PARAM_INT);
        $stmt->bindParam(':otras', $this->otras, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function _guardarPreferenciasHorario($co)
    {
        $stmt_delete = $co->prepare("DELETE FROM tbl_docente_preferencia WHERE doc_cedula = :doc_cedula");
        $stmt_delete->execute([':doc_cedula' => $this->doc_cedula]);

        if (!empty($this->preferencias)) {
            $stmt_insert = $co->prepare(
                "INSERT INTO tbl_docente_preferencia (doc_cedula, dia_semana, hora_inicio, hora_fin) 
                 VALUES (:doc_cedula, :dia_semana, :hora_inicio, :hora_fin)"
            );
            foreach ($this->preferencias as $dia => $horas) {
                if (isset($horas['activado'])) {
                    $stmt_insert->execute([
                        ':doc_cedula' => $this->doc_cedula,
                        ':dia_semana' => $dia,
                        ':hora_inicio' => !empty($horas['inicio']) ? $horas['inicio'] : null,
                        ':hora_fin' => !empty($horas['fin']) ? $horas['fin'] : null
                    ]);
                }
            }
        }
    }
}
