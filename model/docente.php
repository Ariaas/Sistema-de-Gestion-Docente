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

    public function __construct()
    {
        parent::__construct();
    }

   
    public function setCondicion($condicion) { $this->doc_condicion = $condicion; }
    public function setTipoConcurso($concurso) { 
        $this->doc_tipo_concurso = empty($concurso) ? null : $concurso; 
    }
    public function setDedicacion($dedicacion) { $this->doc_dedicacion = $dedicacion; }
    public function setCedula($doc_cedula) { $this->doc_cedula = $doc_cedula; }
    public function setNombre($doc_nombre) { $this->doc_nombre = $doc_nombre; }
    public function setApellido($doc_apellido) { $this->doc_apellido = $doc_apellido; }
    public function setCorreo($doc_correo) { $this->doc_correo = $doc_correo; }
    public function setPrefijo($doc_prefijo) { $this->doc_prefijo = $doc_prefijo; }
    public function setCategoriaNombre($cat_nombre) { $this->cat_nombre = $cat_nombre; }
    public function setIngreso($doc_ingreso) { $this->doc_ingreso = $doc_ingreso; }
    public function setAnioConcurso($doc_anio_concurso) { 
        $this->doc_anio_concurso = empty($doc_anio_concurso) ? null : $doc_anio_concurso;
    }
    public function setObservacion($doc_observacion) { $this->doc_observacion = $doc_observacion; }
    public function setTitulos($titulos) { $this->titulos = $titulos; }
    public function setCoordinaciones($coordinaciones) { $this->coordinaciones = $coordinaciones; }

    private function buscarEstadoPorCedula($doc_cedula)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

            $stmt = $co->prepare("UPDATE tbl_docente SET doc_nombre = :doc_nombre, doc_apellido = :doc_apellido, doc_correo = :doc_correo, cat_nombre = :cat_nombre, doc_prefijo = :doc_prefijo, doc_dedicacion = :doc_dedicacion, doc_condicion = :doc_condicion, doc_ingreso = :doc_ingreso, doc_anio_concurso = :doc_anio_concurso, doc_tipo_concurso = :doc_tipo_concurso, doc_observacion = :doc_observacion, doc_estado = 1 WHERE doc_cedula = :doc_cedula");
            $stmt->execute([
                ':doc_nombre' => $this->doc_nombre, 
                ':doc_apellido' => $this->doc_apellido, 
                ':doc_correo' => $this->doc_correo, 
                ':cat_nombre' => $this->cat_nombre, 
                ':doc_prefijo' => $this->doc_prefijo, 
                ':doc_dedicacion' => $this->doc_dedicacion, 
                ':doc_condicion' => $this->doc_condicion,
                ':doc_ingreso' => $this->doc_ingreso, 
                ':doc_anio_concurso' => $this->doc_anio_concurso,
                ':doc_tipo_concurso' => $this->doc_tipo_concurso,
                ':doc_observacion' => $this->doc_observacion, 
                ':doc_cedula' => $this->doc_cedula
            ]);
            
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
                $r['mensaje'] = '¡Registro Incluido!<br/> Se registró el docente correctamente.';
            } else {
                return $resultado_actualizacion;
            }
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            
            $stmt = $co->prepare("INSERT INTO tbl_docente(cat_nombre, doc_prefijo, doc_cedula, doc_nombre, doc_apellido, doc_correo, doc_dedicacion, doc_condicion, doc_ingreso, doc_anio_concurso, doc_tipo_concurso, doc_observacion, doc_estado) VALUES (:cat_nombre, :doc_prefijo, :doc_cedula, :doc_nombre, :doc_apellido, :doc_correo, :doc_dedicacion, :doc_condicion, :doc_ingreso, :doc_anio_concurso, :doc_tipo_concurso, :doc_observacion, 1)");
            $stmt->execute([
                ':cat_nombre' => $this->cat_nombre, 
                ':doc_prefijo' => $this->doc_prefijo, 
                ':doc_cedula' => $this->doc_cedula, 
                ':doc_nombre' => $this->doc_nombre, 
                ':doc_apellido' => $this->doc_apellido, 
                ':doc_correo' => $this->doc_correo, 
                ':doc_dedicacion' => $this->doc_dedicacion, 
                ':doc_condicion' => $this->doc_condicion,
                ':doc_ingreso' => $this->doc_ingreso, 
                ':doc_anio_concurso' => $this->doc_anio_concurso,
                ':doc_tipo_concurso' => $this->doc_tipo_concurso,
                ':doc_observacion' => $this->doc_observacion
            ]);

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

            $co->commit();
            $r['resultado'] = 'incluir';
            $r['mensaje'] = '¡Registro Incluido!<br/> Se registró el docente correctamente';
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
                $r['mensaje'] = '¡Registro Modificado!<br/> Se modificó el docente correctamente';
            } else {
                return $resultado_actualizacion;
            }
        } else {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡ERROR!<br/> El DOCENTE con esta cédula NO existe o está inactivo!';
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
                $stmt = $co->prepare("UPDATE tbl_docente SET doc_estado = 0 WHERE doc_cedula = :doc_cedula");
                $stmt->execute([':doc_cedula' => $this->doc_cedula]);

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = '¡Registro Eliminado!<br/> Se eliminó el docente correctamente';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'No se puede eliminar este registro.<br/> Está asociado a otro registro existente.';
            }
        } else {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡ERROR!<br/> El DOCENTE con esta cédula NO existe o ya está eliminado.';
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
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras FROM tbl_actividad WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                $r['resultado'] = 'consultar_horas';
                $r['mensaje'] = $resultado;
            } else {
                $r['resultado'] = 'horas_no_encontradas';
                $r['mensaje'] = [ 'act_creacion_intelectual' => 'N/A', 'act_integracion_comunidad' => 'N/A', 'act_gestion_academica' => 'N/A', 'act_otras' => 'N/A' ];
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }
}
?>