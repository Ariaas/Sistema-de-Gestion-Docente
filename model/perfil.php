<?php
require_once('model/db_bitacora.php');
require_once('config/config.php');

class Perfil extends Connection_bitacora
{
    private $usuarioId;
    private $nombreUsuario;
    private $correoUsuario;
    private $fotoPerfil;
    private $orgConnection;

    public function __construct($usuarioId = null, $nombreUsuario = null, $correoUsuario = null, $fotoPerfil = null)
    {
        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->fotoPerfil = $fotoPerfil;
        $this->orgConnection = null;
    }

    public function get_usuarioId()
    {
        return $this->usuarioId;
    }
    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }
    public function get_correoUsuario()
    {
        return $this->correoUsuario;
    }
    public function get_fotoPerfil()
    {
        return $this->fotoPerfil;
    }

    public function set_usuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }
    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }
    public function set_correoUsuario($correoUsuario)
    {
        $this->correoUsuario = $correoUsuario;
    }
    public function set_fotoPerfil($fotoPerfil)
    {
        $this->fotoPerfil = $fotoPerfil;
    }

    private function getOrgConnection()
    {
        if ($this->orgConnection === null) {
            $dsn = "mysql:host=" . _DB_HOST_ . ";dbname=" . _DB_NAME_ . ";charset=utf8";
            $this->orgConnection = new PDO($dsn, _DB_USER_, _DB_PASS_);
            $this->orgConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->orgConnection;
    }

    private function obtenerCatalogosDocente()
    {
        $catalogos = ['titulos' => [], 'coordinaciones' => []];
        try {
            $co = $this->getOrgConnection();

            $stmtTitulos = $co->query("SELECT tit_prefijo, tit_nombre FROM tbl_titulo WHERE tit_estado = 1 ORDER BY tit_prefijo, tit_nombre");
            while ($fila = $stmtTitulos->fetch(PDO::FETCH_ASSOC)) {
                $catalogos['titulos'][] = [
                    'valor' => $fila['tit_prefijo'] . '::' . $fila['tit_nombre'],
                    'texto' => $fila['tit_prefijo'] . ' ' . $fila['tit_nombre']
                ];
            }

            $stmtCoordinaciones = $co->query("SELECT cor_nombre FROM tbl_coordinacion WHERE cor_estado = 1 ORDER BY cor_nombre");
            while ($fila = $stmtCoordinaciones->fetch(PDO::FETCH_ASSOC)) {
                $catalogos['coordinaciones'][] = [
                    'valor' => $fila['cor_nombre'],
                    'texto' => $fila['cor_nombre']
                ];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $catalogos;
    }

    private function obtenerTitulosDocente($cedula)
    {
        $titulos = [];
        $co = $this->getOrgConnection();
        $stmt = $co->prepare("SELECT td.tit_prefijo, td.tit_nombre FROM titulo_docente td INNER JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre WHERE td.doc_cedula = :cedula AND t.tit_estado = 1 ORDER BY td.tit_prefijo, td.tit_nombre");
        $stmt->execute([':cedula' => $cedula]);
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $titulos[] = [
                'valor' => $fila['tit_prefijo'] . '::' . $fila['tit_nombre'],
                'texto' => $fila['tit_prefijo'] . ' ' . $fila['tit_nombre']
            ];
        }
        return $titulos;
    }

    private function obtenerCoordinacionesDocente($cedula)
    {
        $coordinaciones = [];
        $co = $this->getOrgConnection();
        $stmt = $co->prepare("SELECT cd.cor_nombre FROM coordinacion_docente cd INNER JOIN tbl_coordinacion c ON cd.cor_nombre = c.cor_nombre WHERE cd.doc_cedula = :cedula AND c.cor_estado = 1 ORDER BY cd.cor_nombre");
        $stmt->execute([':cedula' => $cedula]);
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $coordinaciones[] = [
                'valor' => $fila['cor_nombre'],
                'texto' => $fila['cor_nombre']
            ];
        }
        return $coordinaciones;
    }

    private function obtenerDocentePerfil($cedula)
    {
        try {
            $co = $this->getOrgConnection();
            $stmt = $co->prepare("SELECT doc_cedula, doc_prefijo, doc_nombre, doc_apellido, doc_correo, cat_nombre, doc_dedicacion, doc_condicion, doc_ingreso, doc_anio_concurso, doc_tipo_concurso, doc_observacion FROM tbl_docente WHERE doc_cedula = :cedula AND doc_estado = 1");
            $stmt->execute([':cedula' => $cedula]);
            $docente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$docente) {
                return null;
            }

            if (!empty($docente['doc_anio_concurso'])) {
                $docente['doc_anio_concurso'] = date('Y-m', strtotime($docente['doc_anio_concurso']));
            }

            $titulos = $this->obtenerTitulosDocente($cedula);
            $coordinaciones = $this->obtenerCoordinacionesDocente($cedula);

            return [
                'datos' => $docente,
                'titulos' => [
                    'detalle' => $titulos,
                    'seleccionados' => array_column($titulos, 'valor')
                ],
                'coordinaciones' => [
                    'detalle' => $coordinaciones,
                    'seleccionadas' => array_column($coordinaciones, 'valor')
                ]
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function Listar($usuarioId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $tieneDocente = false;
        try {
            $stmt = $co->prepare("SELECT usu_id, usu_nombre, usu_correo, usu_foto, usu_cedula FROM tbl_usuario WHERE usu_id = :usuarioId AND usu_estado = 1");
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;

            if ($data && !empty($data['usu_cedula'])) {
                $docenteInfo = $this->obtenerDocentePerfil($data['usu_cedula']);
                if (is_array($docenteInfo) && !isset($docenteInfo['error'])) {
                    $r['docente'] = $docenteInfo;
                    $tieneDocente = true;
                }
                $catalogos = $this->obtenerCatalogosDocente();
                if (!isset($catalogos['resultado'])) {
                    $r['catalogos'] = $catalogos;
                }
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $r['tiene_docente'] = $tieneDocente;
        $co = null;
        return $r;
    }

    public function Modificar($contraseniaUsuario = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $query = "UPDATE tbl_usuario SET usu_correo = :correoUsuario";
            if (!empty($this->fotoPerfil)) {
                $query .= ", usu_foto = :fotoPerfil";
            }
            if (!empty($contraseniaUsuario)) {
                $hashedPassword = password_hash($contraseniaUsuario, PASSWORD_DEFAULT);
                $query .= ", usu_contrasenia = :contraseniaUsuario";
            }
            $query .= " WHERE usu_id = :usuarioId";

            $stmt = $co->prepare($query);

            $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
            if (!empty($this->fotoPerfil)) {
                $stmt->bindParam(':fotoPerfil', $this->fotoPerfil, PDO::PARAM_STR);
            }
            if (!empty($contraseniaUsuario)) {
                $stmt->bindParam(':contraseniaUsuario', $hashedPassword, PDO::PARAM_STR);
            }
            $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);

            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Perfil actualizado correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ActualizarDocentePerfil($cedula, array $datos)
    {
        try {
            $co = $this->getOrgConnection();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmtVerificar = $co->prepare("SELECT doc_cedula FROM tbl_docente WHERE doc_cedula = :cedula");
            $stmtVerificar->execute([':cedula' => $cedula]);
            if (!$stmtVerificar->fetch(PDO::FETCH_ASSOC)) {
                return ['resultado' => 'error', 'mensaje' => 'No se encontró registro de docente asociado.'];
            }

            $titulos = isset($datos['titulos']) && is_array($datos['titulos']) ? $datos['titulos'] : [];
            $coordinaciones = isset($datos['coordinaciones']) && is_array($datos['coordinaciones']) ? $datos['coordinaciones'] : [];
            $correoDocente = isset($datos['doc_correo']) ? trim($datos['doc_correo']) : null;

            $co->beginTransaction();

            $stmtActualizar = $co->prepare("UPDATE tbl_docente SET doc_correo = :doc_correo WHERE doc_cedula = :doc_cedula");
            $stmtActualizar->execute([
                ':doc_correo' => $correoDocente,
                ':doc_cedula' => $cedula
            ]);

            $stmtEliminarTitulos = $co->prepare("DELETE FROM titulo_docente WHERE doc_cedula = :doc_cedula");
            $stmtEliminarTitulos->execute([':doc_cedula' => $cedula]);

            if (!empty($titulos)) {
                $stmtInsertTitulo = $co->prepare("INSERT INTO titulo_docente (doc_cedula, tit_prefijo, tit_nombre) VALUES (:doc_cedula, :tit_prefijo, :tit_nombre)");
                foreach ($titulos as $titulo) {
                    $partes = explode('::', $titulo, 2);
                    if (count($partes) === 2) {
                        $stmtInsertTitulo->execute([
                            ':doc_cedula' => $cedula,
                            ':tit_prefijo' => $partes[0],
                            ':tit_nombre' => $partes[1]
                        ]);
                    }
                }
            }

            $stmtEliminarCoordinaciones = $co->prepare("DELETE FROM coordinacion_docente WHERE doc_cedula = :doc_cedula");
            $stmtEliminarCoordinaciones->execute([':doc_cedula' => $cedula]);

            if (!empty($coordinaciones)) {
                $stmtInsertCoordinacion = $co->prepare("INSERT INTO coordinacion_docente (doc_cedula, cor_nombre, cor_doc_estado) VALUES (:doc_cedula, :cor_nombre, 1)");
                foreach ($coordinaciones as $coordinacion) {
                    $stmtInsertCoordinacion->execute([
                        ':doc_cedula' => $cedula,
                        ':cor_nombre' => $coordinacion
                    ]);
                }
            }

            $co->commit();

            $datosActualizados = $this->obtenerDocentePerfil($cedula);
            if (is_array($datosActualizados) && isset($datosActualizados['error'])) {
                $datosActualizados = null;
            }

            $catalogos = $this->obtenerCatalogosDocente();
            if (isset($catalogos['resultado']) && $catalogos['resultado'] === 'error') {
                $catalogos = null;
            }

            return [
                'resultado' => 'actualizar_docente',
                'mensaje' => 'Datos del docente actualizados correctamente.',
                'docente' => $datosActualizados,
                'catalogos' => $catalogos,
                'tiene_docente' => (bool) $datosActualizados
            ];
        } catch (Exception $e) {
            if (isset($co) && $co instanceof PDO && $co->inTransaction()) {
                $co->rollBack();
            }
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public function existeCorreo($correo, $usuarioId = null)
    {
        $co = $this->Con();
        try {
            $sql_usuario = "SELECT usu_id FROM tbl_usuario WHERE usu_correo = :correo AND usu_estado = 1";
            $stmt_usuario = $co->prepare($sql_usuario);
            $stmt_usuario->execute([':correo' => $correo]);
            $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if ($usuarioId && $usuario['usu_id'] == $usuarioId) {
                    return ['resultado' => 'no_existe'];
                } else {
                    return [
                        'resultado' => 'existe_usuario',
                        'mensaje' => 'Este correo ya está en uso por otro usuario.'
                    ];
                }
            }

            $sql_docente = "SELECT doc_nombre, doc_apellido FROM tbl_docente WHERE doc_correo = :correo AND doc_estado = 1";
            $stmt_docente = $co->prepare($sql_docente);
            $stmt_docente->execute([':correo' => $correo]);
            $docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);

            if ($docente) {
                return [
                    'resultado' => 'existe_docente',
                    'mensaje' => 'El correo ya está asignado al docente (' . $docente['doc_nombre'] . ' ' . $docente['doc_apellido'] . ').'
                ];
            }

            return ['resultado' => 'no_existe'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
}
