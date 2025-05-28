<?php
require_once('model/dbconnection.php');

class Malla extends Connection {
    private $mal_id;
    private $mal_codigo;
    private $mal_nombre;
    private $mal_anio;
    private $mal_cohorte;
    private $mal_descripcion;
    private $mal_estado;

    public function __construct($mal_id = null, $mal_codigo = null, $mal_nombre = null, $mal_anio = null, $mal_cohorte = null, $mal_descripcion = null, $mal_estado = null)
    {
        parent::__construct();
        $this->mal_id = $mal_id;
        $this->mal_codigo = $mal_codigo;
        $this->mal_nombre = $mal_nombre;
        $this->mal_anio = $mal_anio;
        $this->mal_cohorte = $mal_cohorte;
        $this->mal_descripcion = $mal_descripcion;
        $this->mal_estado = $mal_estado;
    }

    // Getters y Setters (sin cambios)
    public function getMalId() { return $this->mal_id; }
    public function getMalCodigo() { return $this->mal_codigo; }
    public function getMalNombre() { return $this->mal_nombre; }
    public function getMalAnio() { return $this->mal_anio; }
    public function getMalCohorte() { return $this->mal_cohorte; }
    public function getMalDescripcion() { return $this->mal_descripcion; }
    public function getMalEstado() { return $this->mal_estado; }
    public function setMalId($mal_id) { $this->mal_id = $mal_id; }
    public function setMalCodigo($mal_codigo) { $this->mal_codigo = $mal_codigo; }
    public function setMalNombre($mal_nombre) { $this->mal_nombre = $mal_nombre; }
    public function setMalAnio($mal_anio) { $this->mal_anio = $mal_anio; }
    public function setMalCohorte($mal_cohorte) { $this->mal_cohorte = $mal_cohorte; }
    public function setMalDescripcion($mal_descripcion) { $this->mal_descripcion = $mal_descripcion; }
    public function setMalEstado($mal_estado) { $this->mal_estado = $mal_estado; }

    public function Registrar(){
        $r = array();
        if (!$this->Existe($this->mal_codigo, $this->mal_nombre)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $stmt = $co->prepare("INSERT INTO tbl_malla( mal_codigo, mal_nombre, mal_anio, mal_cohorte, mal_descripcion, mal_estado)
                 VALUES (:mal_codigo, :mal_nombre, :mal_anio, :mal_cohorte, :mal_descripcion, 1)");
                $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
                $stmt->bindParam(':mal_anio', $this->mal_anio, PDO::PARAM_STR);
                $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
                $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
                $stmt->execute();
                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró la malla curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            $co = null;
        } else {
            $r['resultado'] = 'existe';
            $r['mensaje'] = 'ERROR! <br/> La malla curricular con ese código y nombre ya existe!';
        }
        return $r;
    }

    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_malla WHERE mal_estado = 1 ORDER BY mal_nombre ASC");
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

    public function Modificar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $stmt_check = $co->prepare("SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo AND mal_nombre = :mal_nombre AND mal_id != :mal_id AND mal_estado = 1");
        $stmt_check->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
        $stmt_check->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
        $stmt_check->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
        $stmt_check->execute();
        if ($stmt_check->fetch()) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> Ya existe otra malla curricular con ese código y nombre.';
            return $r;
        }
        try {
            $stmt = $co->prepare("UPDATE tbl_malla
                SET mal_codigo = :mal_codigo, mal_nombre = :mal_nombre, mal_anio = :mal_anio, mal_cohorte = :mal_cohorte, mal_descripcion = :mal_descripcion
                WHERE mal_id = :mal_id");
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_anio', $this->mal_anio, PDO::PARAM_STR);
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la malla curricular correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_malla SET mal_estado = 0 WHERE mal_id = :mal_id");
            $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
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

    public function Existe($codigo, $nombre){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo AND mal_nombre = :mal_nombre AND mal_estado = 1");
            $stmt->bindParam(':mal_codigo', $codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch()) {
                return true;
            }
        } catch (Exception $e) {
        }
        $co = null;
        return false;
    }

    public function obtenerUnidadesCurricularesActivas() {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT uc_id, uc_codigo, uc_nombre FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_nombre ASC");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    public function AsignarUCsAMalla($mallaId, $ucIdsArray) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $co->beginTransaction();
            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            $stmtCheck = $co->prepare("SELECT COUNT(*) FROM uc_pensum WHERE pens_id = :mallaId AND uc_id = :ucId");
            $stmtInsert = $co->prepare("INSERT INTO uc_pensum (pens_id, uc_id) VALUES (:mallaId, :ucId)");

            foreach ($ucIdsArray as $ucId) {
                $stmtCheck->execute([':mallaId' => $mallaId, ':ucId' => $ucId]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists == 0) {
                    $stmtInsert->execute([':mallaId' => $mallaId, ':ucId' => $ucId]);
                    $assignedCount++;
                } else {
                    $alreadyAssignedCount++;
                }
            }
            $co->commit();
            $r['resultado'] = 'ok';
            $message = "";
            if ($assignedCount > 0) {
                $message .= "$assignedCount Unidades Curriculares nuevas asignadas. ";
            }
            if ($alreadyAssignedCount > 0) {
                $message .= "$alreadyAssignedCount Unidades Curriculares ya estaban asignadas.";
            }
            if (empty($message)) {
                $message = "No se seleccionaron UCs para asignar o ya estaban todas asignadas.";
            }
            $r['mensaje'] = trim($message);

        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Error al asignar UCs: ' . $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function QuitarUCDeMalla($mallaId, $ucId) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $stmt = $co->prepare("DELETE FROM uc_pensum WHERE pens_id = :mallaId AND uc_id = :ucId");
            $stmt->bindParam(':mallaId', $mallaId, PDO::PARAM_INT);
            $stmt->bindParam(':ucId', $ucId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $r['resultado'] = 'ok';
                $r['mensaje'] = 'Unidad Curricular desvinculada de la malla correctamente.';
            } else {
                $r['resultado'] = 'info';
                $r['mensaje'] = 'La Unidad Curricular no estaba vinculada.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function obtenerCertificadosActivos() {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT cert_id, cert_nombre FROM tbl_certificado WHERE cert_estado = 1 ORDER BY cert_nombre ASC");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    public function AsignarCertificadosAMalla($mallaId, $certificadosIdsArray) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $co->beginTransaction();
            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            $stmtCheck = $co->prepare("SELECT COUNT(*) FROM pensum_certificado WHERE pens_id = :mallaId AND cert_id = :certId");
            $stmtInsert = $co->prepare("INSERT INTO pensum_certificado (pens_id, cert_id) VALUES (:mallaId, :certId)");

            foreach ($certificadosIdsArray as $certId) {
                $stmtCheck->execute([':mallaId' => $mallaId, ':certId' => $certId]);
                $exists = $stmtCheck->fetchColumn();
                
                if ($exists == 0) {
                    $stmtInsert->execute([':mallaId' => $mallaId, ':certId' => $certId]);
                    $assignedCount++;
                } else {
                    $alreadyAssignedCount++;
                }
            }
            $co->commit();
            $r['resultado'] = 'ok';
            $message = "";
            if ($assignedCount > 0) {
                $message .= "$assignedCount Certificados nuevos asignados. ";
            }
            if ($alreadyAssignedCount > 0) {
                $message .= "$alreadyAssignedCount Certificados ya estaban asignados.";
            }
             if (empty($message)) {
                $message = "No se seleccionaron Certificados para asignar o ya estaban todos asignados.";
            }
            $r['mensaje'] = trim($message);

        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Error al asignar Certificados: ' . $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function QuitarCertificadoDeMalla($mallaId, $certificadoId) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $stmt = $co->prepare("DELETE FROM pensum_certificado WHERE pens_id = :mallaId AND cert_id = :certId");
            $stmt->bindParam(':mallaId', $mallaId, PDO::PARAM_INT);
            $stmt->bindParam(':certId', $certificadoId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $r['resultado'] = 'ok';
                $r['mensaje'] = 'Certificado desvinculado de la malla correctamente.';
            } else {
                $r['resultado'] = 'info';
                $r['mensaje'] = 'El Certificado no estaba vinculado.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    // --- Nuevos Métodos para Listar Asignaciones ---
    public function ListarAsignacionesUC() {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $stmt = $co->query("
                SELECT 
                    m.mal_id, 
                    m.mal_codigo, 
                    m.mal_nombre AS malla_nombre, 
                    GROUP_CONCAT(DISTINCT uc.uc_nombre ORDER BY uc.uc_nombre SEPARATOR ', ') AS ucs_asignadas
                FROM tbl_malla m
                LEFT JOIN uc_pensum up ON m.mal_id = up.pens_id
                LEFT JOIN tbl_uc uc ON up.uc_id = uc.uc_id AND uc.uc_estado = 1
                WHERE m.mal_estado = 1
                GROUP BY m.mal_id, m.mal_codigo, m.mal_nombre
                ORDER BY m.mal_nombre ASC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok_asignaciones_uc';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al listar asignaciones UC: " . $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ListarAsignacionesCertificados() {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];
        try {
            $stmt = $co->query("
                SELECT 
                    m.mal_id, 
                    m.mal_codigo, 
                    m.mal_nombre AS malla_nombre, 
                    GROUP_CONCAT(DISTINCT c.cert_nombre ORDER BY c.cert_nombre SEPARATOR ', ') AS certificados_asignados
                FROM tbl_malla m
                LEFT JOIN pensum_certificado pc ON m.mal_id = pc.pens_id
                LEFT JOIN tbl_certificado c ON pc.cert_id = c.cert_id AND c.cert_estado = 1
                WHERE m.mal_estado = 1
                GROUP BY m.mal_id, m.mal_codigo, m.mal_nombre
                ORDER BY m.mal_nombre ASC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok_asignaciones_cert';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al listar asignaciones de certificados: " . $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
?>