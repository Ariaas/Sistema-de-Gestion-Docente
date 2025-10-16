<?php
require_once('model/dbconnection.php');

 class Ruc extends Connection 
 { 
    private $anio_id;  
    private $trayecto; 
    private $nombreUnidad; 
    private $fase; 

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
    }

    public function set_trayecto($valor)
    {
        $this->trayecto = $valor;
    }

    public function set_nombreUnidad($valor)
    {
        $this->nombreUnidad = $valor;
    }

    public function set_fase($valor) 
    { 
        $this->fase = $valor; 
    } 

   public function obtenerUnidadesCurriculares()
    {
        $co = $this->con();
        try {
        
            $sqlBase = "SELECT DISTINCT
                u.uc_trayecto AS `Número de Trayecto`,
                CASE 
                    WHEN LEFT(s.sec_codigo, 1) IN ('0', '1', '2') THEN CONCAT('IN', s.sec_codigo)
                    WHEN LEFT(s.sec_codigo, 1) IN ('3', '4') THEN CONCAT('IIN', s.sec_codigo)
                    ELSE s.sec_codigo
                END AS `Código de Sección`,
                u.uc_nombre AS `Nombre de la Unidad Curricular`,
                CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`
            FROM
                uc_horario uh
                uc_horario uh
            INNER JOIN
                tbl_uc u ON uh.uc_codigo = u.uc_codigo
            INNER JOIN
                tbl_docente d ON uh.doc_cedula = d.doc_cedula
            INNER JOIN
                tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            ";

            $conditions = [];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            } else {
                $conditions[] = "s.ani_anio IN (SELECT ani_anio FROM tbl_anio WHERE ani_activo = 1)";
            }
            
            $conditions[] = "s.sec_estado = 1";

            if (isset($this->trayecto) && $this->trayecto !== '') {
                $conditions[] = "u.uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }

            if (!empty($this->nombreUnidad)) {
                $conditions[] = "u.uc_codigo = :uc_id_filter";
                $params[':uc_id_filter'] = $this->nombreUnidad;
            }

            if (!empty($this->fase) && $this->fase !== 'Anual') {
                $conditions[] = "(u.uc_periodo = :fase OR u.uc_periodo = 'Anual')";
                $params[':fase'] = $this->fase;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

            $sqlBase .= " ORDER BY u.uc_trayecto, s.sec_codigo, u.uc_nombre";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUnidadesCurriculares: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerAnios() 
    { 
        $co = $this->con(); 
        try { 
            $p = $co->prepare("SELECT DISTINCT ani_anio FROM tbl_anio WHERE ani_activo = 1 ORDER BY ani_anio DESC"); 
            $p->execute(); 
            return $p->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) { 
            error_log("Error en Ruc::obtenerAnios: " . $e->getMessage()); 
            return false; 
        } 
    }

    public function obtenerFaseActual($anio) 
    { 
        $co = $this->con(); 
        try { 
            $sql = "SELECT fase_numero, fase_apertura, fase_cierre 
                    FROM tbl_fase 
                    WHERE ani_anio = :anio 
                    AND CURDATE() BETWEEN fase_apertura AND fase_cierre 
                    LIMIT 1";
            $p = $co->prepare($sql); 
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->execute(); 
            return $p->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) { 
            error_log("Error en Ruc::obtenerFaseActual: " . $e->getMessage()); 
            return false; 
        } 
    } 

    public function obtenerTrayectos()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT DISTINCT  
                                    uc_trayecto AS tra_id,  
                                    CASE  
                                        WHEN uc_trayecto = 0 THEN 'Trayecto Inicial'  
                                        ELSE CONCAT('Trayecto ', uc_trayecto)  
                                    END AS tra_numero  
                                FROM tbl_uc ORDER BY uc_trayecto");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUc() 
    { 
        $co = $this->con(); 
        try { 
            $p = $co->prepare("SELECT uc_codigo AS uc_id, uc_nombre FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_nombre"); 
            $p->execute(); 
            return $p->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) { 
            error_log("Error en Ruc::obtenerUc: " . $e->getMessage()); 
            return false; 
        } 
    } 

    public function obtenerUcPorTrayecto($trayectoId) 
    { 
        $co = $this->con(); 
        try { 
            $p = $co->prepare("SELECT uc_codigo AS uc_id, uc_nombre FROM tbl_uc WHERE uc_estado = 1 AND uc_trayecto = :trayecto ORDER BY uc_nombre"); 
            $p->bindParam(':trayecto', $trayectoId, PDO::PARAM_INT);
            $p->execute(); 
            return $p->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) { 
            error_log("Error en Ruc::obtenerUcPorTrayecto: " . $e->getMessage()); 
            return false; 
        } 
    } 

    public function obtenerUcPorFiltros($trayectoId = null, $fase = null) 
    { 
        $co = $this->con(); 
        try { 
            $sql = "SELECT uc_codigo AS uc_id, uc_nombre, uc_periodo FROM tbl_uc WHERE uc_estado = 1";
            $params = [];
            
            if ($trayectoId !== null) {
                $sql .= " AND uc_trayecto = :trayecto";
                $params[':trayecto'] = $trayectoId;
            }
            
            if ($fase !== null && $fase !== 'Anual') {
                $sql .= " AND (uc_periodo = :fase OR uc_periodo = 'Anual')";
                $params[':fase'] = $fase;
            }
            
            $sql .= " ORDER BY uc_nombre";
            
            $p = $co->prepare($sql);
            foreach ($params as $key => $value) {
                $p->bindValue($key, $value);
            }
            $p->execute(); 
            return $p->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) { 
            error_log("Error en Ruc::obtenerUcPorFiltros: " . $e->getMessage()); 
            return false; 
        } 
    } 
 }