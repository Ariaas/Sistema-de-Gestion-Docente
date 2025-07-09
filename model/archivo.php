<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $sec_codigo;
    private $uc_codigo;
    private $ani_anio;
    private $ani_tipo;
    private $apro_cantidad;
    private $per_cantidad;
    private $per_aprobados;
    private $uc_nombre;

    private $archivosDir = __DIR__ . '/../archivos_subidos/';
    private $archivosPerDir = __DIR__ . '/../archivos_per/';

    public function __construct(){
        parent::__construct();
        if (!file_exists($this->archivosDir)) { mkdir($this->archivosDir, 0777, true); }
        if (!file_exists($this->archivosPerDir)) { mkdir($this->archivosPerDir, 0777, true); }
    }

    public function setSecCodigo($sec_codigo) { $this->sec_codigo = $sec_codigo; }
    public function getSecCodigo() { return $this->sec_codigo; }
    public function setUcCodigo($uc_codigo) { $this->uc_codigo = $uc_codigo; }
    public function getUcCodigo() { return $this->uc_codigo; }
    public function setAnioAnio($ani_anio) { $this->ani_anio = $ani_anio; }
    public function getAnioAnio() { return $this->ani_anio; }
    public function setAnioTipo($ani_tipo) { $this->ani_tipo = $ani_tipo; }
    public function getAnioTipo() { return $this->ani_tipo; }
    public function setAproCantidad($apro_cantidad) { $this->apro_cantidad = $apro_cantidad; }
    public function getAproCantidad() { return $this->apro_cantidad; }
    public function setPerCantidad($per_cantidad) { $this->per_cantidad = $per_cantidad; }
    public function getPerCantidad() { return $this->per_cantidad; }
    public function setPerAprobados($per_aprobados) { $this->per_aprobados = $per_aprobados; }
    public function getPerAprobados() { return $this->per_aprobados; }
    public function setUcNombre($uc_nombre) { $this->uc_nombre = $uc_nombre; }
    public function getUcNombre() { return $this->uc_nombre; }

    private function generarIdentificadorUnico() {
        $ucNombre = $this->getUcNombre();
        $secCodigo = $this->getSecCodigo();
        $anioAnio = $this->getAnioAnio();
        $anioTipo = $this->getAnioTipo();
        return preg_replace('/[^a-zA-Z0-9_]/', '', $ucNombre) . "_" . $secCodigo . "_" . $anioAnio . "_" . $anioTipo;
    }
    
    public function guardarRegistroInicial($archivo) {
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) { return ['success' => false, 'mensaje' => 'El archivo de acta es obligatorio.']; }
        $this->Con()->beginTransaction();
        try {
            $uc_codigo_val = $this->getUcCodigo();
            $sec_codigo_val = $this->getSecCodigo();
            $anio_anio_val = $this->getAnioAnio();
            $ani_tipo_val = $this->getAnioTipo();

            $chk = $this->Con()->prepare("SELECT COUNT(*) FROM per_aprobados WHERE uc_codigo = :uc AND sec_codigo = :sec AND ani_anio = :anio AND ani_tipo = :tipo AND pa_estado = 1");
            $chk->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $chk->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $chk->bindParam(':anio', $anio_anio_val, PDO::PARAM_INT);
            $chk->bindParam(':tipo', $ani_tipo_val, PDO::PARAM_STR);
            $chk->execute();
            if($chk->fetchColumn() > 0) { throw new Exception('Ya existe un registro activo para esta materia y sección. No se puede duplicar.'); }
            
            $identificador = $this->generarIdentificadorUnico();
            $nombreArchivo = "DEF_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($archivo['tmp_name'], $this->archivosDir . $nombreArchivo)) { throw new Exception('Error al guardar el archivo definitivo.'); }

            $p_periodo = $this->Con()->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_apertura, per_fase) VALUES (:anio, :tipo, CURDATE(), 1) ON DUPLICATE KEY UPDATE ani_anio = VALUES(ani_anio)");
            $p_periodo->bindParam(':anio', $anio_anio_val, PDO::PARAM_INT);
            $p_periodo->bindParam(':tipo', $ani_tipo_val, PDO::PARAM_STR);
            $p_periodo->execute();

            $p_apro = $this->Con()->prepare("INSERT INTO tbl_aprobados (uc_codigo, sec_codigo, apro_cantidad, apro_estado) VALUES (:uc, :sec, :cant, 1) ON DUPLICATE KEY UPDATE apro_cantidad = :cant, apro_estado = 1");
            $apro_cantidad_val = $this->getAproCantidad();
            $p_apro->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $p_apro->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $p_apro->bindParam(':cant', $apro_cantidad_val, PDO::PARAM_INT);
            $p_apro->execute();
            
            $p_per = $this->Con()->prepare("INSERT INTO per_aprobados (ani_anio, ani_tipo, uc_codigo, sec_codigo, per_cantidad, pa_estado) VALUES (:anio, :tipo, :uc, :sec, :cant, 1) ON DUPLICATE KEY UPDATE per_cantidad = :cant, pa_estado = 1");
            $per_cantidad_val = $this->getPerCantidad();
            $p_per->bindParam(':anio', $anio_anio_val, PDO::PARAM_INT);
            $p_per->bindParam(':tipo', $ani_tipo_val, PDO::PARAM_STR);
            $p_per->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $p_per->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $p_per->bindParam(':cant', $per_cantidad_val, PDO::PARAM_INT);
            $p_per->execute();
            
            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro inicial guardado con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    public function registrarAprobadosPer($archivo) {
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) { return ['success' => false, 'mensaje' => 'El archivo del PER es obligatorio.']; }
        $this->Con()->beginTransaction();
        try {
            $identificador = $this->generarIdentificadorUnico();
            $nombreArchivo = "PER_{$identificador}_" . time() . "." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($archivo['tmp_name'], $this->archivosPerDir . $nombreArchivo)) { throw new Exception('Error al guardar el archivo del PER.'); }
            
            $p_update = $this->Con()->prepare("UPDATE per_aprobados SET per_aprobados = :aprob WHERE uc_codigo = :uc AND sec_codigo = :sec");
            $per_aprobados_val = $this->getPerAprobados();
            $uc_codigo_val = $this->getUcCodigo();
            $sec_codigo_val = $this->getSecCodigo();
            $p_update->bindParam(':aprob', $per_aprobados_val, PDO::PARAM_INT);
            $p_update->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $p_update->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $p_update->execute();

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Aprobados del PER registrados con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al registrar aprobados: ' . $e->getMessage()];
        }
    }
    
    public function listarRegistros() {
        $sql = "SELECT 
                    s.sec_codigo, s.sec_cantidad,
                    a.ani_anio, a.ani_tipo,
                    uc.uc_codigo, uc.uc_nombre,
                    ap.apro_cantidad,
                    p.per_cantidad, p.per_aprobados
                FROM (
                    SELECT uc_codigo, sec_codigo FROM tbl_aprobados WHERE apro_estado = 1
                    UNION
                    SELECT uc_codigo, sec_codigo FROM per_aprobados WHERE pa_estado = 1
                ) AS distinct_records
                JOIN tbl_seccion s ON distinct_records.sec_codigo = s.sec_codigo
                JOIN tbl_uc uc ON distinct_records.uc_codigo = uc.uc_codigo
                JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                LEFT JOIN tbl_aprobados ap ON distinct_records.uc_codigo = ap.uc_codigo AND distinct_records.sec_codigo = ap.sec_codigo AND ap.apro_estado = 1
                LEFT JOIN per_aprobados p ON distinct_records.uc_codigo = p.uc_codigo AND distinct_records.sec_codigo = p.sec_codigo AND p.pa_estado = 1
                ORDER BY a.ani_anio DESC, s.sec_codigo ASC, uc.uc_nombre ASC";
        
        $p = $this->Con()->prepare($sql);
        $p->execute();
        $registros = $p->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $key => $registro) {
            $identificador = preg_replace('/[^a-zA-Z0-9_]/', '', $registro['uc_nombre']) . "_" . $registro['sec_codigo'] . "_" . $registro['ani_anio'] . "_" . $registro['ani_tipo'];
            $foundFilesDef = glob($this->archivosDir . "DEF_{$identificador}.*");
            $registros[$key]['archivo_definitivo'] = !empty($foundFilesDef) ? basename($foundFilesDef[0]) : null;
            
            $foundFilesPer = glob($this->archivosPerDir . "PER_{$identificador}_*.*");
            $registros[$key]['archivo_per'] = !empty($foundFilesPer) ? basename($foundFilesPer[0]) : null;
        }
        return $registros;
    }
    
    public function eliminarRegistroCompleto() {
        $this->Con()->beginTransaction();
        try {
            $uc_codigo_val = $this->getUcCodigo();
            $sec_codigo_val = $this->getSecCodigo();

            $p_del_per = $this->Con()->prepare("UPDATE per_aprobados SET pa_estado = 0 WHERE uc_codigo = :uc AND sec_codigo = :sec");
            $p_del_per->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $p_del_per->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $p_del_per->execute();

            $p_del_apro = $this->Con()->prepare("UPDATE tbl_aprobados SET apro_estado = 0 WHERE uc_codigo = :uc AND sec_codigo = :sec");
            $p_del_apro->bindParam(':uc', $uc_codigo_val, PDO::PARAM_STR);
            $p_del_apro->bindParam(':sec', $sec_codigo_val, PDO::PARAM_INT);
            $p_del_apro->execute();
            
            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro eliminado exitosamente.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al desactivar el registro: ' . $e->getMessage()];
        }
    }
    
  /*  public function verificarAcceso(){
        $p_malla = $this->Con()->prepare("SELECT COUNT(*) FROM tbl_malla WHERE mal_activa = 1");
        $p_malla->execute();
        if($p_malla->fetchColumn() == 0) return "Acceso Denegado: No hay ninguna malla curricular activa. Contacte al administrador.";

        return null;
    }*/
    
    public function obtenerAnios() {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
        $p->execute(); return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerFaseActual() {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo, fase_numero FROM tbl_fase WHERE NOW() BETWEEN fase_apertura AND fase_cierre LIMIT 1");
        $p->execute(); return $p->fetch(PDO::FETCH_ASSOC);
    }

    public function determinarFaseParaRemedial($fase_actual) {
        if (!$fase_actual) return null;
        if ($fase_actual['fase_numero'] == 2) return ['anio' => $fase_actual['ani_anio'], 'tipo' => $fase_actual['ani_tipo'], 'fase' => 1];
        if ($fase_actual['fase_numero'] == 1) {
            $anio_anterior = $fase_actual['ani_anio'] - 1;
            $p = $this->Con()->prepare("SELECT ani_tipo FROM tbl_anio WHERE ani_anio = :anio ORDER BY ani_tipo DESC LIMIT 1");
            $p->bindParam(':anio', $anio_anterior, PDO::PARAM_INT);
            $p->execute();
            $tipo_anterior = $p->fetchColumn();
            if ($tipo_anterior) return ['anio' => $anio_anterior, 'tipo' => $tipo_anterior, 'fase' => 2];
        }
        return null;
    }

    public function obtenerUnidadesParaRemedial($doc_cedula, $fase_uc) {
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre FROM tbl_uc uc INNER JOIN uc_docente ud ON uc.uc_codigo = ud.uc_codigo INNER JOIN uc_malla um ON uc.uc_codigo = um.uc_codigo INNER JOIN tbl_malla mal ON um.mal_codigo = mal.mal_codigo WHERE ud.doc_cedula = :doc_cedula AND uc.uc_estado = 1 AND ud.uc_doc_estado = 1 AND mal.mal_activa = 1 AND (uc.uc_periodo = :fase_uc OR uc.uc_periodo = 'Anual') ORDER BY uc.uc_nombre ASC";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
        $p->bindParam(':fase_uc', $fase_uc, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesPorAnio($anio_anio, $ani_tipo, $doc_cedula) {
        $sql = "SELECT DISTINCT s.sec_codigo, s.sec_cantidad FROM tbl_seccion s JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo WHERE s.ani_anio = :anio_anio AND s.ani_tipo = :ani_tipo AND ud.doc_cedula = :doc_cedula AND s.sec_estado = 1 ORDER BY s.sec_codigo ASC";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio_anio', $anio_anio, PDO::PARAM_INT);
        $p->bindParam(':ani_tipo', $ani_tipo, PDO::PARAM_STR);
        $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

