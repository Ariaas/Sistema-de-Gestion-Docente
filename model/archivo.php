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
     private $doc_cedula;
private $fase_numero;
    private $archivosDir = __DIR__ . '/../archivos_subidos/';
    private $archivosPerDir = __DIR__ . '/../archivos_per/';

   public function __construct(){
    parent::__construct();
    // Se normalizan las rutas para que siempre usen la barra correcta del sistema operativo
    $this->archivosDir = realpath(__DIR__ . '/../archivos_subidos/') . DIRECTORY_SEPARATOR;
    $this->archivosPerDir = realpath(__DIR__ . '/../archivos_per/') . DIRECTORY_SEPARATOR;

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
    public function setDocCedula($doc_cedula) { 
    $this->doc_cedula = $doc_cedula; 
}
public function setFaseNumero($fase_numero) { // <-- Añade este método
    $this->fase_numero = $fase_numero;
}
public function getDocCedula() { 
    return $this->doc_cedula; 
}

   private function generarIdentificadorUnico() {
    $ucNombre = $this->getUcNombre();
    $secCodigo = $this->getSecCodigo();
    $anioAnio = $this->getAnioAnio();
    $anioTipo = $this->getAnioTipo();
    // Aseguramos que se use la propiedad de la clase para el número de fase
    $faseNumero = $this->fase_numero; 

    // Se añade "_fase" y el número al final del identificador
    return preg_replace('/[^a-zA-Z0-9_]/', '', $ucNombre) . "_" . $secCodigo . "_" . $anioAnio . "_" . $anioTipo . "_fase" . $faseNumero;
}


 public function guardarRegistroInicial($archivo) {
    $this->Con()->beginTransaction();
    try {
        // Validación de que el archivo es obligatorio
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'mensaje' => 'El archivo del acta de notas es obligatorio.'];
        }

        // --- PARÁMETROS PARA TBL_APROBADOS ---
        $params_apro = [
            ':anio' => $this->getAnioAnio(),
            ':tipo' => $this->getAnioTipo(),
            ':fase' => $this->fase_numero,
            ':uc' => $this->getUcCodigo(),
            ':sec' => $this->getSecCodigo(),
            ':doc' => $this->getDocCedula(),
            ':apro_cant' => $this->getAproCantidad()
        ];
        
        $sql_apro = "INSERT INTO tbl_aprobados (ani_anio, ani_tipo, fase_numero, uc_codigo, sec_codigo, doc_cedula, apro_cantidad, apro_estado) 
                     VALUES (:anio, :tipo, :fase, :uc, :sec, :doc, :apro_cant, 1) 
                     ON DUPLICATE KEY UPDATE apro_cantidad = VALUES(apro_cantidad), doc_cedula = VALUES(doc_cedula), apro_estado = 1";
        
        $p_apro = $this->Con()->prepare($sql_apro);
        // Se usa el array de parámetros correcto
        $p_apro->execute($params_apro);

        // --- PARÁMETROS PARA PER_APROBADOS ---
        $params_per = [
            ':anio' => $this->getAnioAnio(),
            ':tipo' => $this->getAnioTipo(),
            ':fase' => $this->fase_numero,
            ':uc' => $this->getUcCodigo(),
            ':sec' => $this->getSecCodigo(),
            ':per_cant' => $this->getPerCantidad()
        ];
        
        $sql_per = "INSERT INTO per_aprobados (ani_anio, ani_tipo, fase_numero, uc_codigo, sec_codigo, per_cantidad, per_aprobados, pa_estado) 
                    VALUES (:anio, :tipo, :fase, :uc, :sec, :per_cant, 0, 1) 
                    ON DUPLICATE KEY UPDATE per_cantidad = VALUES(per_cantidad), pa_estado = 1";

        $p_per = $this->Con()->prepare($sql_per);
        // Se usa el array de parámetros correcto
        $p_per->execute($params_per);

        // El resto del código no cambia
        $identificador = $this->generarIdentificadorUnico();
        $nombreArchivo = "DEF_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (!move_uploaded_file($archivo['tmp_name'], $this->archivosDir . $nombreArchivo)) {
            throw new Exception('Error al guardar el archivo definitivo.');
        }

        $this->Con()->commit();
        return ['success' => true, 'mensaje' => 'Registro guardado con éxito.'];

    } catch (Exception $e) {
        $this->Con()->rollBack();
        return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
    }
}
  // Reemplaza tu función actual con esta:
  public function registrarAprobadosPer($archivo) {
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'mensaje' => 'El archivo del PER es obligatorio.'];
    }
    $this->Con()->beginTransaction();
    try {
        $identificador = $this->generarIdentificadorUnico();
        $nombreArchivo = "PER_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (!move_uploaded_file($archivo['tmp_name'], $this->archivosPerDir . $nombreArchivo)) {
            throw new Exception('Error al guardar el archivo del PER.');
        }

        $params = [
            ':aprob' => $this->getPerAprobados(),
            ':anio' => $this->getAnioAnio(),
            ':tipo' => $this->getAnioTipo(),
            ':fase' => $this->fase_numero,
            ':uc' => $this->getUcCodigo(),
            ':sec' => $this->getSecCodigo()
        ];

        $sql_update = "UPDATE per_aprobados SET per_aprobados = :aprob, pa_estado = 1 WHERE ani_anio = :anio AND ani_tipo = :tipo AND fase_numero = :fase AND uc_codigo = :uc AND sec_codigo = :sec";
        $stmt = $this->Con()->prepare($sql_update);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Aprobados del PER registrados con éxito.'];
        } else {
            // -- ESTA ES LA PARTE MODIFICADA --
            $this->Con()->rollBack();
            // Creamos un mensaje de depuración
            $debug_info = "Año: " . $this->getAnioAnio() .
                          ", Tipo: " . $this->getAnioTipo() .
                          ", Fase: " . $this->fase_numero .
                          ", UC: " . $this->getUcCodigo() .
                          ", Sección: " . $this->getSecCodigo();

            return ['success' => false, 'mensaje' => 'No se encontró el registro para actualizar. Por favor, verifique los datos. [DEBUG: ' . $debug_info . ']'];
        }
    } catch (Exception $e) {
        $this->Con()->rollBack();
        return ['success' => false, 'mensaje' => 'Error al registrar aprobados: ' . $e->getMessage()];
    }
}


    
 public function listarRegistros($doc_cedula, $rol, $filtrar_propios = false) {
        $sql = "SELECT s.sec_codigo, s.sec_cantidad, p.ani_anio, p.ani_tipo, p.fase_numero, uc.uc_codigo, uc.uc_nombre, uc.uc_periodo, ap.apro_cantidad, ap.doc_cedula, p.per_cantidad, p.per_aprobados FROM per_aprobados p LEFT JOIN tbl_aprobados ap ON p.ani_anio = ap.ani_anio AND p.ani_tipo = ap.ani_tipo AND p.uc_codigo = ap.uc_codigo AND p.sec_codigo = ap.sec_codigo AND p.fase_numero = ap.fase_numero LEFT JOIN tbl_seccion s ON p.sec_codigo = s.sec_codigo LEFT JOIN tbl_uc uc ON p.uc_codigo = uc.uc_codigo WHERE p.pa_estado = 1 ";
        if ($rol == 'Docente' || ($rol == 'Administrador' && $filtrar_propios)) { $sql .= " AND ap.doc_cedula = :doc_cedula "; }
        $sql .= " ORDER BY p.ani_anio DESC, s.sec_codigo ASC, uc.uc_nombre ASC, p.fase_numero ASC";
        $p = $this->Con()->prepare($sql);
        if ($rol == 'Docente' || ($rol == 'Administrador' && $filtrar_propios)) { $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT); }
        $p->execute();
        $registros = $p->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $key => $registro) {
            $registros[$key]['per_abierto'] = $this->esPeriodoPerAbierto($registro['ani_anio'], $registro['fase_numero']);
            $identificador = preg_replace('/[^a-zA-Z0-9_]/', '', $registro['uc_nombre']) . "_" . $registro['sec_codigo'] . "_" . $registro['ani_anio'] . "_" . $registro['ani_tipo'] . "_fase" . $registro['fase_numero'];
            $foundFilesDef = glob($this->archivosDir . "DEF_{$identificador}.*");
            $registros[$key]['archivo_definitivo'] = !empty($foundFilesDef) ? basename($foundFilesDef[0]) : null;
            $foundFilesPer = glob($this->archivosPerDir . "PER_{$identificador}.*");
            $registros[$key]['archivo_per'] = !empty($foundFilesPer) ? basename($foundFilesPer[0]) : null;
        }
        return $registros;
    }
    // Reemplaza tu función actual con esta:
public function eliminarRegistroCompleto() {
    $this->Con()->beginTransaction();
    try {
        $params = [
            ':anio' => $this->getAnioAnio(),
            ':tipo' => $this->getAnioTipo(),
            ':fase' => $this->fase_numero,
            ':uc' => $this->getUcCodigo(),
            ':sec' => $this->getSecCodigo()
        ];

        $p_del_per = $this->Con()->prepare("UPDATE per_aprobados SET pa_estado = 0 WHERE ani_anio = :anio AND ani_tipo = :tipo AND fase_numero = :fase AND uc_codigo = :uc AND sec_codigo = :sec");
        $p_del_per->execute($params);

        $p_del_apro = $this->Con()->prepare("UPDATE tbl_aprobados SET apro_estado = 0 WHERE ani_anio = :anio AND ani_tipo = :tipo AND fase_numero = :fase AND uc_codigo = :uc AND sec_codigo = :sec");
        $p_del_apro->execute($params);
        
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
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo, fase_numero FROM tbl_fase WHERE  CURRENT_DATE BETWEEN fase_apertura AND fase_cierre LIMIT 1");
        $p->execute(); return $p->fetch(PDO::FETCH_ASSOC);
    }

public function determinarFaseParaRemedial($fase_actual) {
    if (!$fase_actual) {
        return null; // Si no hay fase activa, no hay remedial que determinar.
    }

    // Regla: Si estamos en Fase 2, el remedial abierto es el de Fase 1 de ESTE MISMO año.
    if ($fase_actual['fase_numero'] == 2) {
        return [
            'anio' => $fase_actual['ani_anio'], 
            'tipo' => $fase_actual['ani_tipo'], 
            'fase_uc' => 'Fase I' // Las UCs que se pueden registrar son las de 'Fase I'.
        ];
    }

    // Regla: Si estamos en Fase 1, el remedial abierto es el de Fase 2 del AÑO ANTERIOR.
    if ($fase_actual['fase_numero'] == 1) {
        $anio_anterior = $fase_actual['ani_anio'] - 1;

        // Buscamos el tipo de período del año anterior (ej. 'regular')
        $p = $this->Con()->prepare("SELECT ani_tipo FROM tbl_anio WHERE ani_anio = :anio ORDER BY ani_tipo DESC LIMIT 1");
        $p->bindParam(':anio', $anio_anterior, PDO::PARAM_INT);
        $p->execute();
        $tipo_anterior = $p->fetchColumn();

        if ($tipo_anterior) {
            return [
                'anio' => $anio_anterior, 
                'tipo' => $tipo_anterior, 
                'fase_uc' => 'Fase II' // Las UCs que se pueden registrar son las de 'Fase II'.
            ];
        }
    }

    return null; // Si no se cumple ninguna regla, no retorna nada.
} 

    public function obtenerUnidadesParaRemedial($doc_cedula, $fase_uc) {
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre FROM tbl_uc uc INNER JOIN uc_docente ud ON uc.uc_codigo = ud.uc_codigo INNER JOIN uc_malla um ON uc.uc_codigo = um.uc_codigo INNER JOIN tbl_malla mal ON um.mal_codigo = mal.mal_codigo WHERE ud.doc_cedula = :doc_cedula AND uc.uc_estado = 1 AND ud.uc_doc_estado = 1 AND mal.mal_activa = 1 AND (uc.uc_periodo = :fase_uc OR uc.uc_periodo = 'Anual') ORDER BY uc.uc_nombre ASC";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
  // CÓDIGO CORREGIDO
$p->bindParam(':fase_uc', $fase_uc, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

   public function obtenerSeccionesPorAnio($anio_anio, $ani_tipo, $doc_cedula) {
    $sql = "SELECT DISTINCT s.sec_codigo, s.sec_cantidad 
            FROM tbl_seccion s 
            JOIN docente_horario dh ON s.sec_codigo = dh.sec_codigo
            WHERE s.ani_anio = :anio_anio 
              AND s.ani_tipo = :ani_tipo 
              AND dh.doc_cedula = :doc_cedula 
              AND s.sec_estado = 1 
            ORDER BY s.sec_codigo ASC";
            
    $p = $this->Con()->prepare($sql);
    $p->bindParam(':anio_anio', $anio_anio, PDO::PARAM_INT);
    $p->bindParam(':ani_tipo', $ani_tipo, PDO::PARAM_STR);
    $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
    $p->execute();
    return $p->fetchAll(PDO::FETCH_ASSOC);
}

   public function obtenerUnidadesPorSeccion($doc_cedula, $sec_codigo) {
    $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre
            FROM tbl_uc uc
            JOIN uc_horario uh ON uc.uc_codigo = uh.uc_codigo
            JOIN uc_docente ud ON uc.uc_codigo = ud.uc_codigo
            WHERE uh.sec_codigo = :sec_codigo 
              AND ud.doc_cedula = :doc_cedula 
              AND uc.uc_estado = 1
            ORDER BY uc.uc_nombre ASC";
            
    $p = $this->Con()->prepare($sql);
    $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
    $p->bindParam(':sec_codigo', $sec_codigo, PDO::PARAM_INT);
    $p->execute();
    return $p->fetchAll(PDO::FETCH_ASSOC);
}

private function esPeriodoPerAbierto($anio_del_registro, $fase_del_registro) {
    $fase_actual = $this->obtenerFaseActual();
    if (!$fase_actual) {
        return false;
    }

    // Regla 1: El PER de un registro de Fase 1 se activa durante la Fase 2 del mismo año.
    if ($fase_del_registro == 1) {
        return ($fase_actual['fase_numero'] == 2 && $fase_actual['ani_anio'] == $anio_del_registro);
    }

    // Regla 2: El PER de un registro de Fase 2 se activa durante la Fase 1 del siguiente año.
    if ($fase_del_registro == 2) {
        $siguiente_anio = $anio_del_registro + 1;
        return ($fase_actual['fase_numero'] == 1 && $fase_actual['ani_anio'] == $siguiente_anio);
    }
    
    // Si la fase del registro no es 1 ni 2 (o es nula), no se activa.
    return false;
}
}
?>

