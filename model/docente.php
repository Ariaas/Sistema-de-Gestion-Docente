<?php
require_once('model/dbconnection.php');

class Docente extends Connection
{
    private $doc_id;
    private $cat_id;
    private $doc_prefijo;
    private $doc_cedula;
    private $doc_nombre;
    private $doc_apellido;
    private $doc_correo;
    private $doc_dedicacion;
    private $doc_condicion;
    private $titulos = array();
    private $coordinaciones = array(); 

    public function __construct()
    {
        parent::__construct();
    }

    //////////////////////////GETTERS//////////////////////////
    public function getDocId()
    {
        return $this->doc_id;
    }
    public function getCedula()
    {
        return $this->doc_cedula;
    }
    public function getNombre()
    {
        return $this->doc_nombre;
    }
    public function getApellido()
    {
        return $this->doc_apellido;
    }
    public function getCorreo()
    {
        return $this->doc_correo;
    }
    public function getPrefijo()
    {
        return $this->doc_prefijo;
    }
    public function getCategoriaId()
    {
        return $this->cat_id;
    }
    public function getDedicacion()
    {
        return $this->doc_dedicacion;
    }
    public function getCondicion()
    {
        return $this->doc_condicion;
    }
    public function getTitulos()
    {
        return $this->titulos;
    }
    public function getCoordinaciones()
    {
        return $this->coordinaciones;
    }

    //////////////////////////SETTERS//////////////////////////
    public function setDocId($doc_id)
    {
        $this->doc_id = $doc_id;
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
    public function setCategoriaId($cat_id)
    {
        $this->cat_id = $cat_id;
    }
    public function setDedicacion($doc_dedicacion)
    {
        $this->doc_dedicacion = $doc_dedicacion;
    }
    public function setCondicion($doc_condicion)
    {
        $this->doc_condicion = $doc_condicion;
    }
    public function setTitulos($titulos)
    {
        $this->titulos = $titulos;
    }
    public function setCoordinaciones($coordinaciones)
    {
        $this->coordinaciones = $coordinaciones;
    } 

    //////////////////////////METODOS//////////////////////////

    public function Registrar()
    {
        $r = array();

        if (!$this->existe($this->doc_cedula)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $co->beginTransaction();

                $stmt = $co->prepare("INSERT INTO tbl_docente(cat_id, doc_prefijo, doc_cedula, doc_nombre, doc_apellido, doc_correo, doc_dedicacion, doc_condicion, doc_estado) VALUES (:cat_id, :doc_prefijo, :doc_cedula, :doc_nombre, :doc_apellido, :doc_correo, :doc_dedicacion, :doc_condicion, 1)");

                $stmt->execute([':cat_id' => $this->cat_id, ':doc_prefijo' => $this->doc_prefijo, ':doc_cedula' => $this->doc_cedula, ':doc_nombre' => $this->doc_nombre, ':doc_apellido' => $this->doc_apellido, ':doc_correo' => $this->doc_correo, ':doc_dedicacion' => $this->doc_dedicacion, ':doc_condicion' => $this->doc_condicion]);

                $doc_id = $co->lastInsertId();

                if (!empty($this->titulos)) {
                    $stmt_titulos = $co->prepare("INSERT INTO titulo_docente (doc_id, tit_id) VALUES (:doc_id, :tit_id)");
                    foreach ($this->titulos as $tit_id) {
                        $stmt_titulos->execute([':doc_id' => $doc_id, ':tit_id' => $tit_id]);
                    }
                }

                if (!empty($this->coordinaciones)) {
                    $stmt_coordinaciones = $co->prepare("INSERT INTO coordinacion_docente (doc_id, cor_id, cor_doc_estado) VALUES (:doc_id, :cor_id, 1)");
                    foreach ($this->coordinaciones as $cor_id) {
                        $stmt_coordinaciones->execute([':doc_id' => $doc_id, ':cor_id' => $cor_id]);
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
        } else {
            $r['resultado'] = 'error'; 
            $r['mensaje'] = '¡Error!<br/>La cédula ingresada ya se encuentra registrada.';
        }
        return $r;
    }

    public function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        if ($this->existe($this->doc_cedula)) {
            try {
                $co->beginTransaction();

                $stmt = $co->prepare("UPDATE tbl_docente SET doc_nombre = :doc_nombre, doc_apellido = :doc_apellido, doc_correo = :doc_correo, cat_id = :cat_id, doc_prefijo = :doc_prefijo, doc_dedicacion = :doc_dedicacion, doc_condicion = :doc_condicion WHERE doc_cedula = :doc_cedula");
                $stmt->execute([':doc_nombre' => $this->doc_nombre, ':doc_apellido' => $this->doc_apellido, ':doc_correo' => $this->doc_correo, ':cat_id' => $this->cat_id, ':doc_prefijo' => $this->doc_prefijo, ':doc_dedicacion' => $this->doc_dedicacion, ':doc_condicion' => $this->doc_condicion, ':doc_cedula' => $this->doc_cedula]);

                $doc_id = $this->obtenerIdPorCedula($this->doc_cedula);

                $stmt_eliminar_titulos = $co->prepare("DELETE FROM titulo_docente WHERE doc_id = :doc_id");
                $stmt_eliminar_titulos->execute([':doc_id' => $doc_id]);

                if (!empty($this->titulos)) {
                    $stmt_titulos = $co->prepare("INSERT INTO titulo_docente (doc_id, tit_id) VALUES (:doc_id, :tit_id)");
                    foreach ($this->titulos as $tit_id) {
                        $stmt_titulos->execute([':doc_id' => $doc_id, ':tit_id' => $tit_id]);
                    }
                }

                $stmt_eliminar_coordinaciones = $co->prepare("DELETE FROM coordinacion_docente WHERE doc_id = :doc_id");
                $stmt_eliminar_coordinaciones->execute([':doc_id' => $doc_id]);

                if (!empty($this->coordinaciones)) {
                    $stmt_coordinaciones = $co->prepare("INSERT INTO coordinacion_docente (doc_id, cor_id, cor_doc_estado) VALUES (:doc_id, :cor_id, 1)");
                    foreach ($this->coordinaciones as $cor_id) {
                        $stmt_coordinaciones->execute([':doc_id' => $doc_id, ':cor_id' => $cor_id]);
                    }
                }

                $co->commit();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = '¡Registro Modificado!<br/> Se modificó el docente correctamente';
            } catch (Exception $e) {
                $co->rollBack();
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = '¡ERROR!<br/> El DOCENTE con esta cédula NO existe!';
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
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = '¡ERROR!<br/> El DOCENTE con esta cédula NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $stmt = $co->prepare("SELECT d.doc_id, d.doc_prefijo, d.doc_cedula, d.doc_nombre, d.doc_apellido, d.doc_correo, d.doc_dedicacion, d.doc_condicion, c.cat_nombre FROM tbl_docente d JOIN tbl_categoria c ON d.cat_id = c.cat_id WHERE c.cat_estado = 1 AND d.doc_estado = 1");
            $stmt->execute();
            $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($docentes as &$docente) {
                // Obtener Títulos
                $stmtTitulos = $co->prepare("SELECT GROUP_CONCAT(t.tit_nombre SEPARATOR ', ') AS titulos, GROUP_CONCAT(t.tit_id SEPARATOR ',') AS titulos_ids FROM titulo_docente td JOIN tbl_titulo t ON td.tit_id = t.tit_id WHERE td.doc_id = :doc_id AND t.tit_estado = 1");
                $stmtTitulos->execute([':doc_id' => $docente['doc_id']]);
                $titulosData = $stmtTitulos->fetch(PDO::FETCH_ASSOC);

                $docente['titulos'] = $titulosData['titulos'] ?? 'Sin títulos';
                $docente['titulos_ids'] = $titulosData['titulos_ids'] ?? '';

                // Obtener Coordinaciones
                $stmtCoordinaciones = $co->prepare("SELECT GROUP_CONCAT(c.cor_nombre SEPARATOR ', ') AS coordinaciones, GROUP_CONCAT(c.cor_id SEPARATOR ',') AS coordinaciones_ids FROM coordinacion_docente cd JOIN tbl_coordinacion c ON cd.cor_id = c.cor_id WHERE cd.doc_id = :doc_id AND c.cor_estado = 1");
                $stmtCoordinaciones->execute([':doc_id' => $docente['doc_id']]);
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

    public function obtenerTitulosDocente($doc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT tit_id FROM titulo_docente WHERE doc_id = :doc_id");
            $stmt->execute([':doc_id' => $doc_id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            return array();
        }
    }

    public function obtenerCoordinacionesDocente($doc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT cor_id FROM coordinacion_docente WHERE doc_id = :doc_id");
            $stmt->execute([':doc_id' => $doc_id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            return array();
        }
    }

    public function obtenerIdPorCedula($doc_cedula)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT doc_id FROM tbl_docente WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['doc_id'] ?? null;
        } catch (Exception $e) {
            return null;
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

       public function ObtenerHorasActividad($doc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras FROM tbl_actividad WHERE doc_id = :doc_id");
            $stmt->execute([':doc_id' => $doc_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                // Se cambia 'success' por un nombre de acción específico
                $r['resultado'] = 'consultar_horas';
                $r['mensaje'] = $resultado;
            } else {
                // Se cambia 'no_encontrado' por un nombre de acción específico
                $r['resultado'] = 'horas_no_encontradas';
                $r['mensaje'] = [
                    'act_creacion_intelectual' => 'N/A',
                    'act_integracion_comunidad' => 'N/A',
                    'act_gestion_academica' => 'N/A',
                    'act_otras' => 'N/A'
                ];
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

}