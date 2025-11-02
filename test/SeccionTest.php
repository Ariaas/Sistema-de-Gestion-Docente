<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\StringStartsWith;
require_once 'model/seccion.php'; 
class SeccionTest extends TestCase
{
    private $seccion;
    private $pdoMock;
    private $stmtMock;
    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor() 
            ->onlyMethods(['Con']) 
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }
    protected function tearDown(): void
    {
        $this->seccion = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }
    public function testRegistrarSeccion_Exito_NuevaSeccion()
    {
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT sec_estado FROM tbl_seccion WHERE sec_codigo =')],
                [$this->stringContains('INSERT INTO tbl_seccion')]
            )
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtInsert);
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(false);
        $stmtInsert->expects($this->once())->method('execute')->willReturn(true);
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
        $this->assertStringContainsString('Se registró la sección', $resultado['mensaje']);
        $this->assertEquals($codigo, $resultado['nuevo_codigo']);
    }
    public function testRegistrarSeccion_Falla_CodigoYaExisteYActivo()
    {
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack'); 
        $stmtCheck = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sec_estado FROM tbl_seccion'))
            ->willReturn($stmtCheck);
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(['sec_estado' => 1]);
        $this->pdoMock->expects($this->never())->method($this->stringContains('INSERT INTO'));
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testRegistrarSeccion_Exito_SobrescribeInactiva()
    {
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtDeleteUC = $this->createMock(PDOStatement::class);
        $stmtDeleteDoc = $this->createMock(PDOStatement::class);
        $stmtDeleteHor = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(5))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT sec_estado FROM tbl_seccion')],
                [$this->stringContains('DELETE FROM uc_horario')],
                [$this->stringContains('DELETE FROM docente_horario')],
                [$this->stringContains('DELETE FROM tbl_horario')],
                [$this->stringContains('UPDATE tbl_seccion SET')]
            )
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtDeleteUC, $stmtDeleteDoc, $stmtDeleteHor, $stmtUpdate);
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(['sec_estado' => 0]);
        $stmtDeleteUC->expects($this->once())->method('execute');
        $stmtDeleteDoc->expects($this->once())->method('execute');
        $stmtDeleteHor->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
    }
    public function testRegistrarSeccion_Falla_ValidacionTemprana($codigo, $cantidad, $anio, $tipo, $mensaje_esperado)
    {
        $this->pdoMock->expects($this->never())->method('prepare');
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString($mensaje_esperado, $resultado['mensaje']);
    }
    public static function providerValidacionRegistrarSeccion(): array
    {
        return [
            'Codigo nulo' => [null, 30, 2025, 'regular', 'Todos los campos'],
            'Cantidad nula' => ['IN1101', null, 2025, 'regular', 'Todos los campos'],
            'Año nulo' => ['IN1101', 30, null, 'regular', 'Todos los campos'],
            'Tipo nulo' => ['IN1101', 30, 2025, null, 'Todos los campos'],
            'Formato codigo invalido' => ['1101', 30, 2025, 'regular', 'Formato de código inválido'],
            'Cantidad fuera de rango (alta)' => ['IN1101', 100, 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
            'Cantidad fuera de rango (baja)' => ['IN1101', -1, 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
            'Cantidad no numerica' => ['IN1101', 'abc', 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
        ];
    }
    public function testModificar_RetornaConfirmarConflicto_PorExcesoHorasDocente()
    {
        $sec_codigo = 'IN1101';
        $ani_anio = 2025;
        $items_horario = [
            ['uc_codigo' => 'UC-001', 'doc_cedula' => 123456, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20', 'espacio' => null]
        ];
        $items_json = json_encode($items_horario);
        $cantidad = 30;
        $forzar = false;
        $stmtCheckUC = $this->createMock(PDOStatement::class);
        $stmtMaxHoras = $this->createMock(PDOStatement::class);
        $stmtHorasActuales = $this->createMock(PDOStatement::class);
        $stmtHorasUC = $this->createMock(PDOStatement::class);
        $stmtDocNombre = $this->createMock(PDOStatement::class);
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);
        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use (
                $stmtCheckUC, $stmtMaxHoras, $stmtHorasActuales, $stmtHorasUC, $stmtDocNombre,
                $stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp
            ) {
                if (str_contains($sql, 'SELECT uc_nombre')) return $stmtCheckUC;
                if (str_contains($sql, 'SELECT act_academicas')) return $stmtMaxHoras;
                if (str_contains($sql, 'SELECT SUM(um.mal_hora_academica)')) return $stmtHorasActuales;
                if (str_contains($sql, 'SELECT mal_hora_academica')) return $stmtHorasUC;
                if (str_contains($sql, 'SELECT doc_nombre, doc_apellido')) return $stmtDocNombre;
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp;
                return $this->createMock(PDOStatement::class);
            });
        $stmtMallaActiva = $this->createMock(PDOStatement::class);
        $this->pdoMock->method('query')->with($this->stringContains('SELECT mal_codigo FROM tbl_malla'))->willReturn($stmtMallaActiva);
        $stmtMallaActiva->method('fetchColumn')->willReturn('MALLA-ACTIVA');
        $stmtCheckUC->method('fetchColumn')->willReturn('UC Test');
        $stmtMaxHoras->method('fetchColumn')->willReturn(10); 
        $stmtHorasActuales->method('fetchColumn')->willReturn(5); 
        $stmtHorasUC->method('fetchColumn')->willReturn(6); 
        $stmtDocNombre->method('fetch')->willReturn(['doc_nombre' => 'Juan', 'doc_apellido' => 'Perez']);
        $stmtGrupo->method('fetchColumn')->willReturn(null);
        $stmtVLV_Doc->method('fetchAll')->willReturn([]); 
        $stmtVLV_Esp->method('fetch')->willReturn(false); 
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $resultado = $this->seccion->Modificar($sec_codigo, $ani_anio, $items_json, $cantidad, $forzar);
        $this->assertEquals('confirmar_conflicto', $resultado['resultado']);
        $this->assertStringContainsString('excedería sus horas académicas', $resultado['mensaje']);
        $this->assertStringContainsString('Juan Perez', $resultado['mensaje']);
    }
    public function testModificar_Falla_RollbackEnErrorDeInsert()
    {
        $sec_codigo = 'IN1101';
        $ani_anio = 2025;
        $items_horario = [
            ['uc_codigo' => 'UC-001', 'doc_cedula' => 123, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20']
        ];
        $items_json = json_encode($items_horario);
        $cantidad = 30;
        $forzar = true; 
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack'); 
        $isTransactionActive = false;
        $this->pdoMock->method('beginTransaction')
            ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = true; 
                return true; 
            });
        $this->pdoMock->method('inTransaction')
            ->willReturnCallback(function () use (&$isTransactionActive) {
                return $isTransactionActive;
            });
        $this->pdoMock->method('commit')
             ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = false;
                return true;
             });
        $this->pdoMock->method('rollBack')
             ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = false; 
                return true; 
             });
        $stmtUpdateSec = $this->createMock(PDOStatement::class);
        $stmtDeleteUC = $this->createMock(PDOStatement::class);
        $stmtDeleteDoc = $this->createMock(PDOStatement::class);
        $stmtDeleteHor = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class); 
        $this->pdoMock->expects($this->exactly(5)) 
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('UPDATE tbl_seccion SET sec_cantidad')],
                [$this->stringContains('DELETE FROM uc_horario')],
                [$this->stringContains('DELETE FROM docente_horario')],
                [$this->stringContains('DELETE FROM tbl_horario')],
                [$this->stringContains('INSERT INTO uc_horario')]
            )
            ->willReturnOnConsecutiveCalls($stmtUpdateSec, $stmtDeleteUC, $stmtDeleteDoc, $stmtDeleteHor, $stmtInsert);
        $stmtUpdateSec->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteUC->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteDoc->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteHor->expects($this->once())->method('execute')->willReturn(true);
        $exceptionMessage = "Error de FK: doc_cedula no existe";
        $stmtInsert->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException($exceptionMessage));
        $resultado = $this->seccion->Modificar($sec_codigo, $ani_anio, $items_json, $cantidad, $forzar);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString($exceptionMessage, $resultado['mensaje']);
    }
    public function testValidarClaseEnVivo_DetectaConflictoDocente()
    {
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);
        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc; 
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp; 
                return $this->createMock(PDOStatement::class);
            });
        $stmtGrupo->method('fetchColumn')->willReturn(null);
        $conflicto_doc = [['sec_codigo' => 'OTRA-SEC', 'doc_nombre' => 'Juan', 'doc_apellido' => 'Perez']];
        $stmtVLV_Doc->method('fetchAll')->willReturn($conflicto_doc);
        $stmtVLV_Esp->method('fetch')->willReturn(false);
        $resultado = $this->seccion->ValidarClaseEnVivo(123, 'UC-001', ['numero' => '1', 'tipo' => 'Aula', 'edificio' => 'A'], 'Lunes', '08:00', '10:00', 'MI-SEC');
        $this->assertTrue($resultado['conflicto']);
        $this->assertCount(1, $resultado['mensajes']);
        $this->assertEquals('docente', $resultado['mensajes'][0]['tipo']);
        $this->assertStringContainsString('Juan Perez', $resultado['mensajes'][0]['mensaje']);
    }
    public function testValidarClaseEnVivo_IgnoraConflictoEnMismoGrupoUnion()
    {
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtSeccionesGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);
        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtSeccionesGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id FROM tbl_seccion WHERE sec_codigo = ?')) return $stmtGrupo;
                if (str_contains($sql, 'SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id = ?')) return $stmtSeccionesGrupo;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?') && str_contains($sql, 'NOT IN')) return $stmtVLV_Doc;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?') && str_contains($sql, 'NOT IN')) return $stmtVLV_Esp;
                return $this->createMock(PDOStatement::class); 
            });
        $stmtGrupo->expects($this->once())->method('execute')->with(['MI-SEC']);
        $stmtGrupo->method('fetchColumn')->willReturn('grupo-A');
        $stmtSeccionesGrupo->expects($this->once())->method('execute')->with(['grupo-A']);
        $stmtSeccionesGrupo->method('fetchAll')->with(PDO::FETCH_COLUMN)->willReturn(['MI-SEC', 'OTRA-SEC']);
        $parametrosEsperadosDoc = array_merge([123], ['MI-SEC', 'OTRA-SEC'], ['Lunes', '10:00', '08:00']);
        $stmtVLV_Doc->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosDoc); 
        $stmtVLV_Doc->method('fetchAll')->willReturn([]);
        $parametrosEsperadosEsp = array_merge(['1', 'Aula', 'A'], ['MI-SEC', 'OTRA-SEC'], ['Lunes', '10:00', '08:00']);
         $stmtVLV_Esp->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosEsp);
        $stmtVLV_Esp->method('fetch')->willReturn(false);
        $resultado = $this->seccion->ValidarClaseEnVivo(123, 'UC-001', ['numero' => '1', 'tipo' => 'Aula', 'edificio' => 'A'], 'Lunes', '08:00', '10:00', 'MI-SEC');
        $this->assertFalse($resultado['conflicto']);
    }
    public function testConsultarDetalles_FormateaDatosCorrectamente()
    {
        $datosBD = [
            [
                'uc_codigo' => 'UC-001', 'doc_cedula' => 123, 'subgrupo' => null,
                'esp_numero' => 'L1', 'esp_tipo' => 'Laboratorio', 'esp_edificio' => 'Hilandera',
                'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20' 
            ]
        ];
        $this->pdoMock->expects($this->once())->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn($datosBD);
        $resultado = $this->seccion->ConsultarDetalles('IN1101', 2025);
        $this->assertEquals('ok', $resultado['resultado']);
        $item = $resultado['mensaje'][0];
        $this->assertEquals('08:00:00', $item['hora_inicio']);
        $this->assertEquals('09:20:00', $item['hora_fin']);
        $this->assertArrayHasKey('espacio', $item);
        $this->assertEquals('L1', $item['espacio']['numero']);
        $this->assertEquals('Laboratorio', $item['espacio']['tipo']);
        $this->assertArrayNotHasKey('esp_numero', $item); 
    }
    public function testObtenerUcPorDocente_FiltraParaFase1()
    {
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFase = $this->createMock(PDOStatement::class);
        $stmtUCs = $this->createMock(PDOStatement::class); 
        $prepareMap = [
            ['SELECT ani_anio, ani_tipo FROM tbl_anio', $stmtAnio],
            ['SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase', $stmtFase],
            ['SELECT u.uc_codigo, u.uc_nombre, u.uc_trayecto, u.uc_periodo FROM tbl_uc u WHERE', $stmtUCs]
        ];
        $this->pdoMock->method('prepare')
             ->willReturnCallback(function ($sql) use ($prepareMap) {
                 foreach ($prepareMap as [$sqlPattern, $stmtMock]) {
                     if (str_starts_with($sql, trim(substr($sqlPattern, 0, 60)))) {
                         return $stmtMock;
                     }
                 }
                 throw new Exception("SQL no esperado en prepare: " . $sql); 
             });
        $hoy = new DateTime();
        $inicioFase1 = (clone $hoy)->modify('-1 month')->format('Y-m-d');
        $finFase1 = (clone $hoy)->modify('+1 month')->format('Y-m-d');
        $stmtAnio->method('fetch')->willReturn(['ani_anio' => 2025, 'ani_tipo' => 'regular']);
        $stmtFase->method('fetchAll')->willReturn([
            ['fase_numero' => 1, 'fase_apertura' => $inicioFase1, 'fase_cierre' => $finFase1],
            ['fase_numero' => 2, 'fase_apertura' => '2026-01-01', 'fase_cierre' => '2026-06-01']
        ]);
        $datosUCFase1 = [['uc_codigo' => 'UC-FASE1', 'uc_nombre' => 'Test Fase 1', 'uc_trayecto' => '1', 'uc_periodo' => 'Fase I']];
        $stmtUCs->expects($this->once())->method('execute')->with([':trayecto_seccion' => 1]); 
        $stmtUCs->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosUCFase1);
        $resultado = $this->seccion->obtenerUcPorDocente(null, '1'); 
        $this->assertEquals('ok', $resultado['mensaje']);
        $this->assertNotEmpty($resultado['data']);
        $this->assertEquals('UC-FASE1', $resultado['data'][0]['uc_codigo']);
    }
    public function testObtenerUcPorDocente_Falla_SiNoHayFaseActiva()
    {
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFase = $this->createMock(PDOStatement::class);
        $prepareMap = [
            ['SELECT ani_anio, ani_tipo FROM tbl_anio', $stmtAnio],
            ['SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase', $stmtFase]
        ];
         $this->pdoMock->method('prepare')
             ->willReturnCallback(function ($sql) use ($prepareMap) {
                 foreach ($prepareMap as [$sqlPattern, $stmtMock]) {
                     if (str_starts_with($sql, trim(substr($sqlPattern, 0, 60)))) {
                         return $stmtMock;
                     }
                 }
                 throw new Exception("SQL no esperado en prepare (posiblemente la consulta de UCs): " . $sql);
             });
        $stmtAnio->method('fetch')->willReturn(['ani_anio' => 2025, 'ani_tipo' => 'regular']);
        $stmtFase->method('fetchAll')->willReturn([
            ['fase_numero' => 1, 'fase_apertura' => '2024-01-01', 'fase_cierre' => '2024-06-01']
        ]);
        $resultado = $this->seccion->obtenerUcPorDocente(123456, '1');
        $this->assertEmpty($resultado['data']);
        $this->assertStringContainsString('Fuera de período', $resultado['mensaje']);
    }
    public function testUnirHorarios_Falla_SeccionInexistente()
    {
        $sec_origen = 'IN1101';
        $secs_a_unir = ['IN1101', 'IN1102', 'INEXISTENTE']; 
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sec_codigo, ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo IN'))
            ->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with($secs_a_unir);
        $this->stmtMock->method('fetchAll')->willReturn([
            ['sec_codigo' => 'IN1101', 'ani_anio' => 2025, 'ani_tipo' => 'regular'],
            ['sec_codigo' => 'IN1102', 'ani_anio' => 2025, 'ani_tipo' => 'regular'],
        ]);
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $resultado = $this->seccion->UnirHorarios($sec_origen, $secs_a_unir);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no son válidas', $resultado['mensaje']);
    }
    public function testValidarClaseEnVivo_ManejaEspacioIncompleto()
    {
        $espacio_incompleto = ['numero' => '1']; 
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class); 
        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                if (str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc;
                if (str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp;
                return $this->createMock(PDOStatement::class);
            });
        $stmtGrupo->method('fetchColumn')->willReturn(null); 
        $stmtVLV_Doc->method('fetchAll')->willReturn([]); 
        $parametrosEsperadosEsp = [
            $espacio_incompleto['numero'], 
            null,                          
            null,                          
            'MI-SEC',                      
            'Martes',                      
            '12:00',                       
            '10:00'                        
        ];
        $stmtVLV_Esp->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosEsp);
        $stmtVLV_Esp->method('fetch')->willReturn(false); 
        $resultado = $this->seccion->ValidarClaseEnVivo(456, 'UC-002', $espacio_incompleto, 'Martes', '10:00', '12:00', 'MI-SEC');
        $this->assertFalse($resultado['conflicto']); 
    }
} 