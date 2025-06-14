<?php
require_once('model/dbconnection.php');

class Categoria extends Connection
{

    private $categoriaNombre;
    private $categoriaId;


    public function __construct($categoriaNombre = null, $categoriaId = null)
    {
        parent::__construct();

        $this->categoriaNombre = $categoriaNombre;
        $this->categoriaId = $categoriaId;
    }


    public function getCategoria()
    {
        return $this->categoriaNombre;
    }
    public function getId()
    {
        return $this->categoriaId;
    }
    public function setCategoria($categoriaNombre)
    {
        $this->categoriaNombre = $categoriaNombre;
    }
    public function setId($categoriaId)
    {
        $this->categoriaId = $categoriaId;
    }


    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->categoriaNombre)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_categoria (
                    cat_nombre,
                    cat_estado
                ) VALUES (
                    :categoriaNombre,
                    1
                )");

                $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el Categoría correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El Categoría colocado ya existe!';
        }

        return $r;
    }


    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->categoriaId)) {
            if (!$this->existe($this->categoriaNombre)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_categoria
                    SET cat_nombre = :categoriaNombre 
                    WHERE cat_id = :categoriaId");

                    $stmt->bindParam(':categoriaId', $this->categoriaId, PDO::PARAM_INT);
                    $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el Categoría correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El Categoría colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El Categoría colocado NO existe!';
        }
        return $r;
    }


    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->categoriaId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_categoria
                SET cat_estado = 0
                WHERE cat_id = :categoriaId");

                $stmt->bindParam(':categoriaId', $this->categoriaId, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el Categoría correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El CÓDIGO colocado NO existe!';
        }
        return $r;
    }


    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT cat_nombre, cat_id FROM tbl_categoria WHERE cat_estado = 1");
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


    public function ExisteId($categoriaId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_categoria WHERE cat_id=:categoriaId AND cat_estado = 1");
            $stmt->bindParam(':categoriaId', $categoriaId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El Categoria ya existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existe($categoriaNombre)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_categoria WHERE cat_nombre=:categoriaNombre AND cat_estado = 1");
            $stmt->bindParam(':categoriaNombre', $categoriaNombre, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El Categoria ya existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}