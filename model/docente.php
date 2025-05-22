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

    // Constructor modificado para igualar estructura
    public function __construct(
        $doc_cedula = null, 
        $doc_nombre = null, 
        $doc_apellido = null, 
        $doc_correo = null, 
        $doc_prefijo = null, 
        $cat_id = null
    ) {
        parent::__construct();
        
        $this->doc_cedula = $doc_cedula;
        $this->doc_nombre = $doc_nombre;
        $this->doc_apellido = $doc_apellido;
        $this->doc_correo = $doc_correo;
        $this->doc_prefijo = $doc_prefijo;
        $this->cat_id = $cat_id;
    }

    //////////////////////////GETTERS//////////////////////////
    public function getDocId() {
        return $this->doc_id;
    }

    public function getCedula() {
        return $this->doc_cedula;
    }

    public function getNombre() {
        return $this->doc_nombre;
    }

    public function getApellido() {
        return $this->doc_apellido;
    }

    public function getCorreo() {
        return $this->doc_correo;
    }

    public function getPrefijo() {
        return $this->doc_prefijo;
    }

    public function getCategoriaId() {
        return $this->cat_id;
    }

    public function getDedicacion() {
        return $this->doc_dedicacion;
    }

    public function getCondicion() {
        return $this->doc_condicion;
    }

    //////////////////////////SETTERS//////////////////////////
    public function setDocId($doc_id) {
        $this->doc_id = $doc_id;
    }

    public function setCedula($doc_cedula) {
        $this->doc_cedula = $doc_cedula;
    }

    public function setNombre($doc_nombre) {
        $this->doc_nombre = $doc_nombre;
    }

    public function setApellido($doc_apellido) {
        $this->doc_apellido = $doc_apellido;
    }

    public function setCorreo($doc_correo) {
        $this->doc_correo = $doc_correo;
    }

    public function setPrefijo($doc_prefijo) {
        $this->doc_prefijo = $doc_prefijo;
    }

    public function setCategoriaId($cat_id) {
        $this->cat_id = $cat_id;
    }

    public function setDedicacion($doc_dedicacion) {
        $this->doc_dedicacion = $doc_dedicacion;
    }

    public function setCondicion($doc_condicion) {
        $this->doc_condicion = $doc_condicion;
    }

    //////////////////////////METODOS//////////////////////////

    // Registrar (equivalente a incluir)
    public function Registrar()
    {
        $r = array();

        if (!$this->existe($this->doc_cedula)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            try {
            $stmt = $co->prepare("INSERT INTO tbl_docente(
                cat_id,
                doc_prefijo,
                doc_cedula,
                doc_nombre,
                doc_apellido,
                doc_correo,
                doc_dedicacion,
                doc_condicion,
                doc_estado
            ) VALUES (
                :cat_id,
                :doc_prefijo,
                :doc_cedula,
                :doc_nombre,
                :doc_apellido,
                :doc_correo,
                :doc_dedicacion,
                :doc_condicion,
                1  -- Estado activo por defecto
            )");
            
            // Solo una ejecución con todos los parámetros
            $stmt->execute([
                ':cat_id' => $this->cat_id,
                ':doc_prefijo' => $this->doc_prefijo,
                ':doc_cedula' => $this->doc_cedula,
                ':doc_nombre' => $this->doc_nombre,
                ':doc_apellido' => $this->doc_apellido,
                ':doc_correo' => $this->doc_correo,
                ':doc_dedicacion' => $this->doc_dedicacion,
                ':doc_condicion' => $this->doc_condicion
            ]);
                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el docente correctamente';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El DOCENTE con esta cédula ya existe!';
        }
        return $r;
    }

    // Modificar
   public function Modificar()
{
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $r = array();
    
    if ($this->existe($this->doc_cedula)) {
        try {
            $stmt = $co->prepare("UPDATE tbl_docente 
                SET doc_nombre = :doc_nombre,
                    doc_apellido = :doc_apellido,
                    doc_correo = :doc_correo,
                    cat_id = :cat_id,
                    doc_prefijo = :doc_prefijo,
                    doc_dedicacion = :doc_dedicacion,
                    doc_condicion = :doc_condicion
                WHERE doc_cedula = :doc_cedula");
            
            $stmt->execute([
                ':doc_nombre' => $this->doc_nombre,
                ':doc_apellido' => $this->doc_apellido,
                ':doc_correo' => $this->doc_correo,
                ':cat_id' => $this->cat_id,
                ':doc_prefijo' => $this->doc_prefijo,
                ':doc_dedicacion' => $this->doc_dedicacion,
                ':doc_condicion' => $this->doc_condicion,
                ':doc_cedula' => $this->doc_cedula
            ]);
            
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/> Se modificó el docente correctamente';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
    } else {
        $r['resultado'] = 'modificar';
        $r['mensaje'] = 'ERROR! <br/> El DOCENTE con esta cédula NO existe!';
    }
    return $r;
}
    // Eliminar
    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        if ($this->existe($this->doc_cedula)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_docente 
                    SET doc_estado = 0
                    WHERE doc_cedula = :doc_cedula");
                $stmt->execute([':doc_cedula' => $this->doc_cedula]);
                
                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado! <br/> Se eliminó el docente correctamente';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'No se puede eliminar este registro. <br/> Está asociado a otro registro existente.';
            }
            $co = null;
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El DOCENTE con esta cédula NO existe!';
        }
        return $r;
    }

    // Listar
  public function Listar()
{
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $r = array();
    
    try {
        $stmt = $co->prepare("SELECT 
            d.doc_id,
            d.doc_prefijo,
            d.doc_cedula,
            d.doc_nombre,
            d.doc_apellido,
            d.doc_correo,
            d.doc_dedicacion,
            d.doc_condicion,
            c.cat_nombre
            FROM tbl_docente d
            JOIN tbl_categoria c ON d.cat_id = c.cat_id
            WHERE c.cat_estado = 1
            AND d.doc_estado = 1");  // Filtro por docentes activos
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $r['resultado'] = 'consultar';
        $r['mensaje'] = $resultados;
    } catch (Exception $e) {
        $r['resultado'] = 'error';
        $r['mensaje'] = $e->getMessage();
    }
    $co = null;
    return $r;
}
    // Existe
 public function Existe($doc_cedula) {
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $r = array();
    
    try {
        $stmt = $co->prepare("SELECT * FROM tbl_docente WHERE doc_cedula = :doc_cedula AND doc_estado = 1");
        $stmt->execute([':doc_cedula' => $doc_cedula]);
        $fila = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $r['resultado'] = 'Existe';
            $r['mensaje'] = 'La cédula del docente ya existe!';
            
        } 
    } catch (Exception $e) {
        $r['resultado'] = 'error';
        $r['mensaje'] = $e->getMessage();
    }
    
    return $r;
}
   

     public function listacategoria()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_categoria");
        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }

      public function listatitulo()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_titulo");
        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }
    
}