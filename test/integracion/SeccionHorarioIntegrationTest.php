<?php

use PHPUnit\Framework\TestCase;

require_once 'model/seccion.php';
require_once 'model/anio.php';
require_once 'model/uc.php';
require_once 'model/docente.php';
require_once 'model/espacios.php';
require_once 'model/categoria.php';

class SeccionHorarioIntegrationTest extends IntegrationTestCase
{
    private $seccion;
    private $anio;
    private $uc;
    private $docente;
    private $espacio;
    private $datosCreados = [];

    private function createAnioFixture($anioNum, $tipo = 'regular')
    {
        $co = getConnection($this->seccion);
        $check = $co->prepare("SELECT 1 FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $check->execute([$anioNum, $tipo]);
        if ($check->fetchColumn()) {
            foreach ($this->datosCreados['anios'] as $a) {
                if ($a['anio'] === $anioNum && $a['tipo'] === $tipo) {
                    return;
                }
            }
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => $tipo];
            return;
        }

        // insert year
        $stmt = $co->prepare("INSERT INTO tbl_anio (ani_anio, ani_tipo, ani_activo, ani_estado) VALUES (?, ?, 1, 1)");
        $stmt->execute([$anioNum, $tipo]);

        $stmtF = $co->prepare("INSERT INTO tbl_fase (ani_anio, ani_tipo, fase_numero, fase_apertura, fase_cierre) VALUES (?, ?, ?, ?, ?)");
        $f1A = sprintf('%04d-01-01', $anioNum);
        $f1C = sprintf('%04d-06-30', $anioNum);
        $stmtF->execute([$anioNum, $tipo, 1, $f1A, $f1C]);
        if ($tipo === 'regular') {
            $f2A = sprintf('%04d-07-01', $anioNum);
            $f2C = sprintf('%04d-12-31', $anioNum);
            $stmtF->execute([$anioNum, $tipo, 2, $f2A, $f2C]);
        }

        $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => $tipo];
    }

    protected function setUp(): void
    {
        $this->seccion = new Seccion();
        $this->anio = new Anio();
        $this->uc = new UC();
        $this->docente = new Docente();
        $this->espacio = new Espacio();
        $this->datosCreados = [
            'secciones' => [],
            'anios' => [],
            'ucs' => [],
            'docentes' => [],
            'espacios' => [],
            'categorias' => []
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->datosCreados['docentes'] as $cedula) {
            try {
                $docTemp = new Docente();
                $docTemp->setCedula($cedula);
                $docTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['ucs'] as $codigo) {
            try {
                $ucTemp = new UC();
                $ucTemp->setcodigoUC($codigo);
                $ucTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['espacios'] as $esp) {
            try {
                $espTemp = new Espacio();
                $espTemp->setNumero($esp['numero']);
                $espTemp->setEdificio($esp['edificio']);
                $espTemp->setTipo($esp['tipo']);
                $espTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['anios'] as $anio) {
            try {
                $anioTemp = new Anio();
                $anioTemp->setAnio($anio['anio']);
                $anioTemp->setTipo($anio['tipo']);
                $anioTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['categorias'] as $cat) {
            try {
                $catTemp = new Categoria();
                $catTemp->setCategoria($cat);
                $catTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['secciones'] as $sec) {
            try {
                $secTemp = new Seccion();
                $secTemp->EliminarSeccionYHorario($sec['codigo'], $sec['anio'], $sec['tipo']);
            } catch (Exception $e) {
            }
        }
    }

    public function testRegistrarSeccion_ConAnioActivo_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->createAnioFixture($anioNum, 'regular');

        $codigoSeccion = 'IN' . rand(1000, 9999);

        $resultado = $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);

        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];
    }

    public function testRegistrarSeccion_CodigoInvalido_Error()
    {
        $codigoInvalido = 'ABC';
        $resultado = $this->seccion->RegistrarSeccion($codigoInvalido, 25, 2024, 'regular');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Formato de código inválido', $resultado['mensaje']);
    }

    public function testRegistrarSeccion_Duplicada_Error()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN' . rand(1000, 9999);
        $this->createAnioFixture($anioNum, 'regular');

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado2 = $this->seccion->RegistrarSeccion($codigoSeccion, 30, $anioNum, 'regular', true);

        $this->assertEquals('error', $resultado2['resultado']);
        $this->assertStringContainsString('ya existe', $resultado2['mensaje']);
    }

    public function testValidarClaseEnVivo_ConflictoDocente_Detectado()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion1 = 'IN' . rand(1000, 4999);
        $codigoSeccion2 = 'IN' . rand(5000, 9999);
        $cedulaDocente = rand(10000000, 29999999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoSeccion1, 25, $anioNum, 'regular', true);
        $this->seccion->RegistrarSeccion($codigoSeccion2, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion1, 'anio' => $anioNum, 'tipo' => 'regular'];
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion2, 'anio' => $anioNum, 'tipo' => 'regular'];

        $ucCode = 'UC001';
        $ucObj = new UC();
        $ucObj->setcodigoUC($ucCode);
        $ucObj->setnombreUC('UC Test');
        $ucObj->setcreditosUC(3);
        $ucObj->settrayectoUC(0);
        $ucObj->setperiodoUC('Fase I');
        $ucObj->setejeUC('Eje_Test');
        $ucObj->setareaUC('Area_Test');
        $ucObj->Registrar();
        $this->datosCreados['ucs'][] = $ucCode;

        $co = getConnection($this->seccion);
        $chkSec = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $chkSec->execute([$codigoSeccion1, $anioNum, 'regular']);
        if (!$chkSec->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoSeccion1, 25, $anioNum, 'regular']);
        }
        $chkSec->execute([$codigoSeccion2, $anioNum, 'regular']);
        if (!$chkSec->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoSeccion2, 25, $anioNum, 'regular']);
        }
        $chk = $co->prepare("SELECT 1 FROM tbl_uc WHERE uc_codigo = ? AND uc_estado = 1 LIMIT 1");
        $chk->execute([$ucCode]);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT IGNORE INTO tbl_eje (eje_nombre) VALUES (?)")->execute(['Eje_Test']);
            $co->prepare("INSERT IGNORE INTO tbl_area (area_nombre) VALUES (?)")->execute(['Area_Test']);
            $co->prepare("INSERT INTO tbl_uc (eje_nombre, area_nombre, uc_codigo, uc_nombre, uc_creditos, uc_trayecto, uc_periodo, uc_estado) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")
                ->execute(['Eje_Test', 'Area_Test', $ucCode, 'UC Test', 3, 0, 'Fase I']);
        }

        $doc = new Docente();
        $doc->setCedula($cedulaDocente);
        $doc->setNombre('Doc');
        $doc->setApellido('Test');
        $doc->setCorreo('docente@test.local');
        $doc->setDedicacion('1');
        $doc->setCondicion('Titular');
        $doc->Registrar();
        $this->datosCreados['docentes'][] = $cedulaDocente;
        $chkDoc = $co->prepare("SELECT 1 FROM tbl_docente WHERE doc_cedula = ? LIMIT 1");
        $chkDoc->execute([$cedulaDocente]);
        if (!$chkDoc->fetchColumn()) {
            $catName = 'Cat_Test_' . rand(1000, 9999);
            $co->prepare("INSERT IGNORE INTO tbl_categoria (cat_nombre, cat_descripcion, cat_estado) VALUES (?, ?, 1)")
                ->execute([$catName, 'Categoria auto creada por test']);
            $this->datosCreados['categorias'][] = $catName;

            $co->prepare("INSERT INTO tbl_docente (cat_nombre, doc_prefijo, doc_cedula, doc_nombre, doc_apellido, doc_correo, doc_dedicacion, doc_condicion, doc_ingreso, doc_anio_concurso, doc_tipo_concurso, doc_observacion, doc_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, 1)")
                ->execute([$catName, 'V', $cedulaDocente, 'Doc', 'Test', 'docente@test.local', '1', 'Titular', '2020-01-01']);
        }

        $esp = new Espacio();
        $esp->setNumero('101');
        $esp->setEdificio('Principal');
        $esp->setTipo('Aula');
        $esp->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => '101', 'edificio' => 'Principal', 'tipo' => 'Aula'];

        $co = getConnection($this->seccion);
        $co->prepare("INSERT INTO tbl_horario (sec_codigo, ani_anio, ani_tipo, tur_nombre, hor_estado) VALUES (?, ?, ?, ?, 1)")->execute([$codigoSeccion1, $anioNum, 'regular', 'Mañana']);
        $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, ani_anio, ani_tipo, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([$ucCode, $cedulaDocente, $codigoSeccion1, $anioNum, 'regular', 'A', '101', 'Aula', 'Principal', 'Lunes', '08:00:00', '10:00:00']);

        $chkHorario = $co->prepare("SELECT 1 FROM uc_horario WHERE doc_cedula = ? AND sec_codigo = ? AND ani_anio = ? LIMIT 1");
        $chkHorario->execute([$cedulaDocente, $codigoSeccion1, $anioNum]);
        if (!$chkHorario->fetchColumn()) {
            $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, ani_anio, ani_tipo, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$ucCode, $cedulaDocente, $codigoSeccion1, $anioNum, 'regular', 'A', '101', 'Aula', 'Principal', 'Lunes', '08:00:00', '10:00:00']);
        }

        $chkConflict = $co->prepare("SELECT 1 FROM uc_horario WHERE ani_anio = ? AND ani_tipo = ? AND doc_cedula = ? AND sec_codigo = ? AND hor_dia = ? AND hor_horainicio < ? AND hor_horafin > ? LIMIT 1");
        $chkConflict->execute([$anioNum, 'regular', $cedulaDocente, $codigoSeccion1, 'Lunes', '11:00:00', '09:00:00']);
        if (!$chkConflict->fetchColumn()) {
            $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, ani_anio, ani_tipo, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$ucCode, $cedulaDocente, $codigoSeccion1, $anioNum, 'regular', 'A', '101', 'Aula', 'Principal', 'Lunes', '08:30:00', '09:30:00']);
        }

        $placeholders = '?';
        $sql_debug = "SELECT uh.sec_codigo, d.doc_nombre, d.doc_apellido FROM uc_horario uh JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio AND uh.ani_tipo = s.ani_tipo JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula WHERE s.sec_estado = 1 AND uh.ani_anio = ? AND uh.ani_tipo = ? AND uh.doc_cedula = ? AND uh.sec_codigo NOT IN ($placeholders) AND uh.hor_dia = ? AND uh.hor_horainicio < ? AND uh.hor_horafin > ?";
        $stmt_debug = $co->prepare($sql_debug);
        $stmt_debug->execute([$anioNum, 'regular', $cedulaDocente, $codigoSeccion2, 'Lunes', '11:00:00', '09:00:00']);
        $rows_debug = $stmt_debug->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($rows_debug, 'Diagnostic: expected uc_horario JOIN SELECT to return a conflicting row but it returned none. Params: ' . json_encode(['anio' => $anioNum, 'sec1' => $codigoSeccion1, 'sec2' => $codigoSeccion2 ?? $codigoSeccion2, 'doc' => $cedulaDocente]) . ' DB rows: ' . json_encode($rows_debug));

        $resultado = $this->seccion->ValidarClaseEnVivo($cedulaDocente, 'UC002', ['numero' => '102', 'tipo' => 'Aula', 'edificio' => 'Principal'], 'Lunes', '09:00:00', '11:00:00', $codigoSeccion2, $anioNum, 'regular');

        $this->assertTrue($resultado['conflicto'], 'Resultado de ValidarClaseEnVivo: ' . json_encode($resultado));
        $this->assertArrayHasKey('mensajes', $resultado);
    }

    public function testValidarClaseEnVivo_ConflictoEspacio_Detectado()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion1 = 'IN' . rand(1000, 4999);
        $codigoSeccion2 = 'IN' . rand(5000, 9999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoSeccion1, 25, $anioNum, 'regular', true);
        $this->seccion->RegistrarSeccion($codigoSeccion2, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion1, 'anio' => $anioNum, 'tipo' => 'regular'];
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion2, 'anio' => $anioNum, 'tipo' => 'regular'];

        $ucCode = 'UC001';
        $ucObj = new UC();
        $ucObj->setcodigoUC($ucCode);
        $ucObj->setnombreUC('UC Test');
        $ucObj->setcreditosUC(3);
        $ucObj->settrayectoUC(0);
        $ucObj->setperiodoUC('Fase I');
        $ucObj->setejeUC('Eje_Test');
        $ucObj->setareaUC('Area_Test');
        $ucObj->Registrar();
        $this->datosCreados['ucs'][] = $ucCode;

        $co = getConnection($this->seccion);
        $chk = $co->prepare("SELECT 1 FROM tbl_uc WHERE uc_codigo = ? AND uc_estado = 1 LIMIT 1");
        $chk->execute([$ucCode]);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT IGNORE INTO tbl_eje (eje_nombre) VALUES (?)")->execute(['Eje_Test']);
            $co->prepare("INSERT IGNORE INTO tbl_area (area_nombre) VALUES (?)")->execute(['Area_Test']);
            $co->prepare("INSERT INTO tbl_uc (eje_nombre, area_nombre, uc_codigo, uc_nombre, uc_creditos, uc_trayecto, uc_periodo, uc_estado) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")
                ->execute(['Eje_Test', 'Area_Test', $ucCode, 'UC Test', 3, 0, 'Fase I']);
        }

        $docCed = '12345678';
        $doc = new Docente();
        $doc->setCedula($docCed);
        $doc->setNombre('Doc');
        $doc->setApellido('Test');
        $doc->setCorreo('docente@test.local');
        $doc->setDedicacion('1');
        $doc->setCondicion('Titular');
        $doc->Registrar();
        $this->datosCreados['docentes'][] = $docCed;

        $esp = new Espacio();
        $esp->setNumero('101');
        $esp->setEdificio('Principal');
        $esp->setTipo('Aula');
        $esp->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => '101', 'edificio' => 'Principal', 'tipo' => 'Aula'];

        $co = getConnection($this->seccion);
        $co->prepare("INSERT INTO tbl_horario (sec_codigo, ani_anio, ani_tipo, tur_nombre, hor_estado) VALUES (?, ?, ?, ?, 1)")->execute([$codigoSeccion1, $anioNum, 'regular', 'Mañana']);
        $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, ani_anio, ani_tipo, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([$ucCode, '12345678', $codigoSeccion1, $anioNum, 'regular', 'A', '101', 'Aula', 'Principal', 'Lunes', '08:00:00', '10:00:00']);

        $resultado = $this->seccion->ValidarClaseEnVivo('87654321', 'UC002', ['numero' => '101', 'tipo' => 'Aula', 'edificio' => 'Principal'], 'Lunes', '09:00:00', '11:00:00', $codigoSeccion2, $anioNum, 'regular');

        $this->assertTrue($resultado['conflicto']);
        $this->assertArrayHasKey('mensajes', $resultado);
    }

    public function testValidarClaseEnVivo_SinConflicto_Exito()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN' . rand(1000, 9999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->seccion->ValidarClaseEnVivo('12345678', 'UC001', ['numero' => '101', 'tipo' => 'Aula', 'edificio' => 'Principal'], 'Lunes', '08:00:00', '10:00:00', $codigoSeccion, $anioNum, 'regular');

        $this->assertFalse($resultado['conflicto']);
    }

    public function testListarAgrupado_ConSeccionesActivas_Exito()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN' . rand(1000, 9999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->seccion->ListarAgrupado();

        $this->assertEquals('consultar_agrupado', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        $encontrada = false;
        foreach ($resultado['mensaje'] as $sec) {
            if ($sec['sec_codigo'] === $codigoSeccion) {
                $encontrada = true;
                $this->assertEquals($anioNum, $sec['ani_anio']);
                break;
            }
        }
        $this->assertTrue($encontrada);
    }

    public function testUnirHorarios_SeccionesMismoTrayectoYTurno_Exito()
    {
        $anioNum = rand(2024, 2030);
        $codigoOrigen = 'IN1' . rand(100, 999);
        $codigoDestino = 'IN1' . rand(100, 999);

        while ($codigoOrigen === $codigoDestino) {
            $codigoDestino = 'IN1' . rand(100, 999);
        }

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoOrigen, 25, $anioNum, 'regular', true);
        $this->seccion->RegistrarSeccion($codigoDestino, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoOrigen, 'anio' => $anioNum, 'tipo' => 'regular'];
        $this->datosCreados['secciones'][] = ['codigo' => $codigoDestino, 'anio' => $anioNum, 'tipo' => 'regular'];

        $co = getConnection($this->seccion);
        $chk = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $chk->execute([$codigoOrigen, $anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoOrigen, 25, $anioNum, 'regular']);
        }
        $chk->execute([$codigoDestino, $anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoDestino, 25, $anioNum, 'regular']);
        }
        $chk = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $chk->execute([$codigoOrigen, $anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoOrigen, 25, $anioNum, 'regular']);
        }
        $chk->execute([$codigoDestino, $anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (?, ?, ?, ?, 1)")
                ->execute([$codigoDestino, 25, $anioNum, 'regular']);
        }

        $resultado = $this->seccion->UnirHorarios($codigoOrigen, [$codigoOrigen, $codigoDestino]);

        $this->assertEquals('unir_horarios_ok', $resultado['resultado'], 'Resultado de UnirHorarios: ' . json_encode($resultado));
    }

    public function testUnirHorarios_SeccionesDiferenteTrayecto_Error()
    {
        $anioNum = rand(2024, 2030);
        $codigoOrigen = 'IN1' . rand(100, 999);
        $codigoDestino = 'JN2' . rand(100, 999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoOrigen, 25, $anioNum, 'regular', true);
        $this->seccion->RegistrarSeccion($codigoDestino, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoOrigen, 'anio' => $anioNum, 'tipo' => 'regular'];
        $this->datosCreados['secciones'][] = ['codigo' => $codigoDestino, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->seccion->UnirHorarios($codigoOrigen, [$codigoOrigen, $codigoDestino]);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('mismo año, tipo, trayecto y turno', $resultado['mensaje']);
    }

    public function testVerificarCodigoSeccion_SeccionExistente_RetornaTrue()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN' . rand(1000, 9999);

        $this->createAnioFixture($anioNum, 'regular');
        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $existe = $this->seccion->VerificarCodigoSeccion($codigoSeccion, $anioNum, 'regular');

        $this->assertTrue($existe);
    }

    public function testVerificarCodigoSeccion_SeccionNoExistente_RetornaFalse()
    {
        $codigoInexistente = 'IN' . rand(1000, 9999);

        $existe = $this->seccion->VerificarCodigoSeccion($codigoInexistente, 2024, 'regular');

        $this->assertFalse($existe);
    }
}
