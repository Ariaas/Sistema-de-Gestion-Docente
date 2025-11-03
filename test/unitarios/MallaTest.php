<?php
use PHPUnit\Framework\TestCase;
require_once 'model/mallacurricular.php';
class MallaTest extends TestCase
{
    private $malla;
    private $pdoMock;
    private $stmtMock;
    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor() 
            ->onlyMethods(['Con']) 
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }
    public function testVerificarCondicionesParaRegistrar_Exito()
    {
        $stmtCountMock = $this->createMock(PDOStatement::class);
        $stmtTrayectosMock = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(2))
    ->method('query')
    ->withConsecutive(
        ["SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1"],
        ["SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_estado = 1"]
    )
    ->willReturnOnConsecutiveCalls(
        $stmtCountMock,
        $stmtTrayectosMock
    );
        $stmtCountMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10'); 
        $stmtTrayectosMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN, 0)
            ->willReturn(['0', '1', '2', '3', '4']); 
        $resultado = $this->malla->verificarCondicionesParaRegistrar();
        $this->assertTrue($resultado['puede_registrar']);
        $this->assertEquals('', $resultado['mensaje']);
    }
    public function testVerificarCondicionesParaRegistrar_Falla_NoHayUCs()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with("SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1")
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0'); 
        $resultado = $this->malla->verificarCondicionesParaRegistrar();
        $this->assertFalse($resultado['puede_registrar']);
        $this->assertStringContainsString('No hay unidades curriculares', $resultado['mensaje']);
    }
    public function testVerificarCondicionesParaRegistrar_Falla_FaltanTrayectos()
    {
        $stmtCountMock = $this->createMock(PDOStatement::class);
        $stmtTrayectosMock = $this->createMock(PDOStatement::class);
         $this->pdoMock->expects($this->exactly(2))
    ->method('query')
    ->withConsecutive(
        ["SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1"],
        ["SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_estado = 1"]
    )
    ->willReturnOnConsecutiveCalls(
        $stmtCountMock,
        $stmtTrayectosMock
    );
        $stmtCountMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10'); 
        $stmtTrayectosMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN, 0)
            ->willReturn(['0', '1', '3']); 
        $resultado = $this->malla->verificarCondicionesParaRegistrar();
        $this->assertFalse($resultado['puede_registrar']);
        $this->assertStringContainsString('Faltan unidades curriculares en los trayectos: 2, 4', $resultado['mensaje']);
    }
    public function testVerificarCondicionesParaRegistrar_Falla_DBException()
    {
       $this->pdoMock->expects($this->once())
    ->method('query')
    ->with("SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1")
    ->willThrowException(new PDOException('Error de BD'));
        $resultado = $this->malla->verificarCondicionesParaRegistrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de BD', $resultado['mensaje']);
    }
 public function testRegistrar_Falla_PorPropiedadNull_NOT_NULL_Constraint()
 {
  $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
  $errorNotNull = new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'mal_nombre' cannot be null");
  $this->malla = $this->getMockBuilder(Malla::class)
   ->disableOriginalConstructor()
   ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
   ->getMock();
  $this->malla->method('Con')->willReturn($this->pdoMock);
  $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
  $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
  $stmtTrayectos = $this->createMock(PDOStatement::class);
  $stmtInsertMalla_Falla = $this->createMock(PDOStatement::class); 
  $this->pdoMock->expects($this->any())
   ->method('prepare')
   ->withConsecutive(
    [$this->stringContains('SELECT DISTINCT uc_trayecto')],
    [$this->stringContains('INSERT INTO tbl_malla')]
   )
   ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
  $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
  $stmtInsertMalla_Falla->method('execute')->willThrowException($errorNotNull);
  $this->pdoMock->expects($this->once())->method('beginTransaction');
  $this->pdoMock->expects($this->never())->method('commit');
  $this->pdoMock->expects($this->once())->method('rollBack');
  $this->malla->setMalCodigo('M-01');
  $this->malla->setMalNombre(null); 
  $this->malla->setMalCohorte(10);
  $this->malla->setMalDescripcion('Desc');
  $resultado = $this->malla->Registrar($unidades);
  $this->assertEquals('error', $resultado['resultado']);
  $this->assertStringContainsString("Column 'mal_nombre' cannot be null", $resultado['mensaje']);
 }
    public function testRegistrar_Falla_UnidadConValoresNulos(){
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10], 
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 10, 'hora_asistida' => 10] 
        ];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con']) 
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('prepare');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nulos o faltantes', $resultado['mensaje']);
    }
   public function testRegistrar_Exito() {
 $unidades = [
 ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
 ['uc_codigo' => 'UC-T1', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
 ['uc_codigo' => 'UC-T2', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
 ['uc_codigo' => 'UC-T3', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
 ['uc_codigo' => 'UC-T4', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
 ];
 $numero_de_inserts_esperados = 5;
 $numero_de_prepare_esperados = 3; 
 $stmtTrayectos = $this->createMock(PDOStatement::class);
 $stmtInsertMalla = $this->createMock(PDOStatement::class);
 $stmtInsertUCMalla = $this->createMock(PDOStatement::class); 
 $this->malla = $this->getMockBuilder(Malla::class)
 ->disableOriginalConstructor()
 ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
 ->getMock();
 $this->malla->method('Con')->willReturn($this->pdoMock);
 $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
 $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
 $this->pdoMock->expects($this->exactly($numero_de_prepare_esperados))
->method('prepare')
 ->withConsecutive(
[$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
 [$this->stringContains('INSERT INTO tbl_malla')],
 [$this->stringContains('INSERT INTO uc_malla')]
)
->willReturnOnConsecutiveCalls(
 $stmtTrayectos,
 $stmtInsertMalla,
 $stmtInsertUCMalla 
 );
 $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
$this->pdoMock->expects($this->once())->method('beginTransaction');
 $this->pdoMock->expects($this->once())->method('commit');
 $this->pdoMock->expects($this->never())->method('rollBack');
$stmtInsertMalla->expects($this->once())->method('execute');
 $stmtInsertUCMalla->expects($this->exactly($numero_de_inserts_esperados))->method('execute');
 $this->malla->setMalCodigo('NUEVA-MALLA');
 $this->malla->setMalNombre('Nueva Malla Curricular');
 $this->malla->setMalCohorte(5);
 $this->malla->setMalDescripcion('Descripción');
 $resultado = $this->malla->Registrar($unidades);
 $this->assertEquals('registrar', $resultado['resultado']);
 $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
 }
    public function testRegistrar_Falla_UnidadesVacias()
    {
        $resultado = $this->malla->Registrar([]);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('No se han proporcionado', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_TrayectosFaltantes()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2']); 
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error de validación: Debe incluir al menos una UC de los trayectos: Trayecto 3, Trayecto 4', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_CodigoYaExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'existe', 'mensaje' => 'Código duplicado']);
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Código duplicado', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_CohorteYaExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'existe', 'mensaje' => 'Cohorte duplicada']);
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Cohorte duplicada', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_DBExceptionEnValidacionTrayecto()
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new PDOException('Error validando trayectos'));
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error al validar los trayectos', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_NombreDemasiadoLargo()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorDataLong = new PDOException("SQLSTATE[22001]: String data, right truncation");
        $errorDataLong->errorInfo[0] = '22001';
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla = $this->createMock(PDOStatement::class); 
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorDataLong);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->malla->setMalCodigo('M-01');
        $this->malla->setMalNombre(str_repeat('a', 1000)); 
        $this->malla->setMalCohorte(10);
        $this->malla->setMalDescripcion('Desc');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('String data, right truncation', $resultado['mensaje']);
    }
    public function testRegistrar_Exito_ConComillasYEspacios()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla = $this->createMock(PDOStatement::class);
        $stmtInsertUCMalla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')],
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla, $stmtInsertUCMalla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $stmtInsertMalla->method('execute')->willReturn(true);
        $stmtInsertUCMalla->method('execute')->willReturn(true);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');
        $this->malla->setMalCodigo(' PNF-1 '); 
        $this->malla->setMalNombre("Malla 'Prueba de Inyección'"); 
        $this->malla->setMalCohorte(10);
        $this->malla->setMalDescripcion('Desc');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testExistecodigo_EsSensibleAMayusculas()
    {
        $this->pdoMock->method('prepare')
            ->with($this->stringContains("SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo"))
            ->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false); 
        $this->malla->setMalCodigo('pnf-1'); 
       $this->malla->setMalCodigoOriginal('PNF-1'); 
        $resultado =$this->malla->Existecodigo();
       $this->assertEquals('ok', $resultado['resultado']);
    }
    public function testRegistrar_Falla_CohorteNegativa()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorOutOfRange = new PDOException("SQLSTATE[22003]: Numeric value out of range");
        $errorOutOfRange->errorInfo[0] = '22003';
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
       $this->malla->method('Con')->willReturn($this->pdoMock);
       $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
       $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos =$this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla =$this->createMock(PDOStatement::class); 
       $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorOutOfRange);
       $this->pdoMock->expects($this->once())->method('beginTransaction');
       $this->pdoMock->expects($this->never())->method('commit');
       $this->pdoMock->expects($this->once())->method('rollBack');
       $this->malla->setMalCodigo('M-01');
       $this->malla->setMalNombre('Malla Negativa');
       $this->malla->setMalCohorte(-10); 
       $this->malla->setMalDescripcion('Desc');
        $resultado =$this->malla->Registrar($unidades);
       $this->assertEquals('error', $resultado['resultado']);
       $this->assertStringContainsString('Numeric value out of range', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_CohorteFlotante()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorIncorrectInt = new PDOException("SQLSTATE[HY000]: General error: 1366 Incorrect integer value: '1.5'");
        $errorIncorrectInt->errorInfo[0] = 'HY000';
       $this->malla=$this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
       $this->malla->method('Con')->willReturn($this->pdoMock);
       $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
       $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos =$this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla =$this->createMock(PDOStatement::class); 
       $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorIncorrectInt);
       $this->pdoMock->expects($this->once())->method('beginTransaction');
       $this->pdoMock->expects($this->never())->method('commit');
       $this->pdoMock->expects($this->once())->method('rollBack');
       $this->malla->setMalCodigo('M-01');
       $this->malla->setMalNombre('Malla Flotante');
       $this->malla->setMalCohorte(1.5); 
       $this->malla->setMalDescripcion('Desc');
        $resultado =$this->malla->Registrar($unidades);
       $this->assertEquals('error', $resultado['resultado']);
       $this->assertStringContainsString('Incorrect integer value', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_NombreEsBooleanoFalse()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorCheck = new PDOException("SQLSTATE[23000]: Integrity constraint violation: CHECK constraint failed");
        $errorCheck->errorInfo[0] = '23000';
       $this->malla=$this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
       $this->malla->method('Con')->willReturn($this->pdoMock);
       $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
       $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos =$this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla =$this->createMock(PDOStatement::class); 
       $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorCheck);
       $this->pdoMock->expects($this->once())->method('beginTransaction');
       $this->pdoMock->expects($this->never())->method('commit');
       $this->pdoMock->expects($this->once())->method('rollBack');
       $this->malla->setMalCodigo('M-01');
       $this->malla->setMalNombre(false); 
       $this->malla->setMalCohorte(1);
       $this->malla->setMalDescripcion('Desc');
        $resultado =$this->malla->Registrar($unidades);
       $this->assertEquals('error', $resultado['resultado']);
       $this->assertStringContainsString('Integrity constraint violation', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_DBExceptionEnInsert()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10] ];
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos, 
                $this->throwException(new PDOException('Error de INSERT')) 
            );
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $resultado = $this->malla->Registrar($unidades); 
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de INSERT', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_DBExceptionEnInsertDeUCMalla()
    {
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
        ];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla = $this->createMock(PDOStatement::class);
        $stmtInsertUCMalla_Falla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
                [$this->stringContains('INSERT INTO tbl_malla')],
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos,
                $stmtInsertMalla,
                $stmtInsertUCMalla_Falla 
            );
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); 
        $stmtInsertMalla->method('execute')->willReturn(true); 
        $stmtInsertUCMalla_Falla->method('execute')
             ->willThrowException(new PDOException('Error en bucle UC'));
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bucle UC', $resultado['mensaje']);
    }
    public function testModificar_Falla_UnidadConValoresNulos()
    {
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10] 
        ];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->expects($this->never())->method('ExisteCohorte');
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('prepare');
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nulos o faltantes', $resultado['mensaje']);
    }
   public function testModificar_Exito_SinCambioDeCodigo()
{
 $unidades = [
 ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
 ['uc_codigo' => 'UC-T1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
 ['uc_codigo' => 'UC-T2', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
 ['uc_codigo' => 'UC-T3', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
 ['uc_codigo' => 'UC-T4', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
 ];
 $num_unidades_a_insertar = count($unidades);
 $numero_de_prepare_esperados = 3; 
$this->malla = $this->getMockBuilder(Malla::class)
 ->disableOriginalConstructor()
 ->onlyMethods(['Con', 'ExisteCohorte'])
 ->getMock();
 $this->malla->method('Con')->willReturn($this->pdoMock);
 $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
 $this->pdoMock->expects($this->once())->method('beginTransaction');
 $this->pdoMock->expects($this->once())->method('commit');
 $this->pdoMock->expects($this->never())->method('rollBack');
 $stmtUpdate = $this->createMock(PDOStatement::class);
 $stmtDelete = $this->createMock(PDOStatement::class);
$stmtInsert = $this->createMock(PDOStatement::class); 
 $this->pdoMock->expects($this->exactly($numero_de_prepare_esperados))
 ->method('prepare')
 ->withConsecutive(
 [$this->stringStartsWith('UPDATE tbl_malla SET mal_codigo = :nuevo_codigo')],
 [$this->stringStartsWith('DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo_original')],
 [$this->stringStartsWith('INSERT INTO uc_malla')]
 )
 ->willReturnOnConsecutiveCalls(
 $stmtUpdate,
 $stmtDelete,
 $stmtInsert 
 );
$stmtUpdate->expects($this->once())->method('execute');
 $stmtDelete->expects($this->once())->method('execute');
 $stmtInsert->expects($this->exactly($num_unidades_a_insertar))->method('execute');
 $this->malla->setMalCodigo('MALLA-1');
 $this->malla->setMalCodigoOriginal('MALLA-1'); 
 $resultado = $this->malla->Modificar($unidades);
 $this->assertEquals('modificar', $resultado['resultado']);
}
  public function testModificar_Exito_ConCambioDeCodigo()
    {
        $unidades = [
             ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T2', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T3', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T4', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
        ];
        $num_unidades = count($unidades);
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack'); 
        $stmtCheck = $this->createMock(PDOStatement::class); 
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $consecutive_calls = [
            [$this->stringStartsWith('SELECT mal_codigo FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
            [$this->stringStartsWith('UPDATE tbl_malla SET mal_codigo = :nuevo_codigo')],
            [$this->stringStartsWith('DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo_original')],
        ];
        $return_calls = [ $stmtCheck, $stmtUpdate, $stmtDelete ];
        for ($i = 0; $i < $num_unidades; $i++) {
            $consecutive_calls[] = [$this->stringStartsWith('INSERT INTO uc_malla')];
            $return_calls[] = $stmtInsert;
        }
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(...$consecutive_calls)
            ->willReturnOnConsecutiveCalls(...$return_calls);
        $stmtCheck->method('fetch')->willReturn(false); 
        $stmtCheck->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');
        $stmtDelete->expects($this->once())->method('execute');
        $stmtInsert->expects($this->exactly($num_unidades))->method('execute');
        $this->malla->setMalCodigo('MALLA-NUEVA');
        $this->malla->setMalCodigoOriginal('MALLA-VIEJA'); 
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('modificar', $resultado['resultado']);
    }
    public function testModificar_Falla_NuevoCodigoYaExiste()
    {
        $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(true); 
        $this->malla->setMalCodigo('MALLA-NUEVA');
        $this->malla->setMalCodigoOriginal('MALLA-VIEJA'); 
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('El nuevo código ya está en uso', $resultado['mensaje']);
    }
     public function testModificar_Falla_CohorteDuplicada()
    {
        $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'existe', 'mensaje' => 'Cohorte duplicada']);
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Cohorte duplicada', $resultado['mensaje']);
        $this->pdoMock->expects($this->never())->method('beginTransaction');
    }
    public function testModificar_Falla_DBException()
    {
         $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
         $this->pdoMock->method('prepare')
            ->with($this->stringContains('UPDATE tbl_malla'))
            ->willThrowException(new PDOException('Error de UPDATE'));
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de UPDATE', $resultado['mensaje']);
    }
public function testModificar_Falla_DBExceptionEnInsertDeUCMalla()
    {
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
        ];
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert_Falla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('UPDATE tbl_malla')],
                [$this->stringContains('DELETE FROM uc_malla')],
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtUpdate,
                $stmtDelete,
                $stmtInsert_Falla
            );
        $stmtUpdate->method('execute')->willReturn(true); 
        $stmtDelete->method('execute')->willReturn(true); 
        $stmtInsert_Falla->method('execute')
             ->willThrowException(new PDOException('Error en bucle INSERT de modificar')); 
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->malla->setMalCodigo('MALLA-1');
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bucle INSERT de modificar', $resultado['mensaje']);
    }
    public function testConsultar_Exito()
    {
        $datosEsperados = [
            ['mal_codigo' => 'M-01', 'mal_nombre' => 'Malla 1', 'mal_activa' => 1],
            ['mal_codigo' => 'M-02', 'mal_nombre' => 'Malla 2', 'mal_activa' => 0]
        ];
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT * FROM tbl_malla'))
            ->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($datosEsperados);
        $resultado = $this->malla->Consultar();
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }
    public function testModificar_Falla_CambioCodigo_FKConstraint()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorFK = new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot update or delete a parent row: a foreign key constraint fails");
        $errorFK->errorInfo[0] = '23000';
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $stmtCheck = $this->createMock(PDOStatement::class); 
        $stmtUpdate_Falla = $this->createMock(PDOStatement::class); 
        $this->pdoMock->expects($this->exactly(2)) 
            ->method('prepare')
            ->withConsecutive(
                [$this->stringStartsWith('SELECT mal_codigo FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
                [$this->stringStartsWith('UPDATE tbl_malla SET mal_codigo = :nuevo_codigo')]
            )
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtUpdate_Falla);
        $stmtCheck->method('fetch')->willReturn(false); 
        $stmtCheck->method('execute')->willReturn(true);
        $stmtUpdate_Falla->method('execute')->willThrowException($errorFK);
        $this->malla->setMalCodigo('MALLA-NUEVA');
        $this->malla->setMalCodigoOriginal('MALLA-VIEJA'); 
        $this->malla->setMalCohorte(1);
        $this->malla->setMalNombre('Nombre');
        $this->malla->setMalDescripcion('Desc');
        $resultado = $this->malla->Modificar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Integrity constraint violation', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_NombreVacio()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorCheck = new PDOException("SQLSTATE[23000]: Integrity constraint violation: CHECK constraint failed for 'mal_nombre'");
        $errorCheck->errorInfo[0] = '23000';
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorCheck);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->malla->setMalCodigo('M-01');
        $this->malla->setMalNombre(''); 
        $this->malla->setMalCohorte(1);
        $this->malla->setMalDescripcion('Desc Válida');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Integrity constraint violation', $resultado['mensaje']);
    }
    public function testRegistrar_Falla_CohorteCero()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1] ];
        $errorOutOfRange = new PDOException("SQLSTATE[22003]: Numeric value out of range for 'mal_cohorte'");
        $errorOutOfRange->errorInfo[0] = '22003';
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla_Falla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')],
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls($stmtTrayectos, $stmtInsertMalla_Falla);
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);
        $stmtInsertMalla_Falla->method('execute')->willThrowException($errorOutOfRange);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->malla->setMalCodigo('M-01');
        $this->malla->setMalNombre('Malla Cero');
        $this->malla->setMalCohorte(0); 
        $this->malla->setMalDescripcion('Desc');
        $resultado = $this->malla->Registrar($unidades);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Numeric value out of range', $resultado['mensaje']);
    }
    public function testConsultar_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willThrowException(new PDOException('Error de Consulta'));
        $resultado = $this->malla->Consultar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de Consulta', $resultado['mensaje']);
    }
public function testCambiarEstadoActivo_Falla_DBExceptionEnUpdate()
    {
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate_Falla = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT mal_activa FROM tbl_malla')],
                [$this->stringContains('UPDATE tbl_malla SET mal_activa')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate_Falla
            );
        $stmtSelect->method('fetchColumn')->willReturn('1'); 
        $stmtUpdate_Falla->method('execute')
             ->willThrowException(new PDOException('Error en UPDATE de estado')); 
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $resultado = $this->malla->cambiarEstadoActivo();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en UPDATE de estado', $resultado['mensaje']);
    }
    public function testCambiarEstadoActivo_Desactivar()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT mal_activa FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
                [$this->stringContains('UPDATE tbl_malla SET mal_activa = :nuevo_estado')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate
            );
        $stmtSelect->method('fetchColumn')->willReturn('1'); 
        $stmtSelect->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');
        $this->malla->setMalCodigo('M-01');
        $resultado = $this->malla->cambiarEstadoActivo();
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals('desactivar', $resultado['accion_bitacora']);
        $this->assertStringContainsString('desactivada', $resultado['mensaje']);
    }
    public function testCambiarEstadoActivo_Activar()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT mal_activa FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
                [$this->stringContains('UPDATE tbl_malla SET mal_activa = :nuevo_estado')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate
            );
        $stmtSelect->method('fetchColumn')->willReturn('0'); 
        $stmtSelect->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');
        $resultado = $this->malla->cambiarEstadoActivo();
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals('activar', $resultado['accion_bitacora']);
        $this->assertStringContainsString('activada', $resultado['mensaje']);
    }
    public function testCambiarEstadoActivo_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');
        $this->pdoMock->method('prepare')
             ->with($this->stringContains('SELECT mal_activa'))
             ->willThrowException(new PDOException('Error de SELECT estado'));
        $resultado = $this->malla->cambiarEstadoActivo();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de SELECT estado', $resultado['mensaje']);
    }
    public function testExistecodigo_OK_CodigoEsIgualAlOriginal()
    {
        $this->pdoMock->expects($this->never())->method('prepare');
        $this->malla->setMalCodigo('CODIGO-1');
        $this->malla->setMalCodigoOriginal('CODIGO-1');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('ok', $resultado['resultado']);
    }
    public function testExistecodigo_OK_CodigoEsNuevoYNoExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->malla->setMalCodigo('CODIGO-NUEVO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('ok', $resultado['resultado']);
    }
    public function testExistecodigo_Existe_CodigoEsNuevoPeroExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'CODIGO-DUPLICADO']); 
        $this->malla->setMalCodigo('CODIGO-DUPLICADO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertStringContainsString('codigo ya existe', $resultado['mensaje']);
    }
    public function testExistecodigo_Existe_CreandoNuevo()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'CODIGO-DUPLICADO']);
        $this->malla->setMalCodigo('CODIGO-DUPLICADO');
        $this->malla->setMalCodigoOriginal(null);
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testExistecodigo_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error chequeando código'));
        $this->malla->setMalCodigo('CODIGO-NUEVO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error chequeando código', $resultado['mensaje']);
    }
   public function testExisteCohorte_Falla_PorTipoDeDatoIncorrecto()
 {
  $errorTipo = new PDOException("SQLSTATE[HY000]: General error: 1366 Incorrect integer value: 'esto-no-es-numero' for column 'mal_cohorte'");
  $this->pdoMock->method('prepare')
    ->with($this->stringContains("SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte"))
   ->willReturn($this->stmtMock);
  $this->stmtMock->method('execute')->willThrowException($errorTipo);
  $this->malla->setMalCohorte("esto-no-es-numero"); 
  $resultado = $this->malla->ExisteCohorte(false); 
  $this->assertEquals('error', $resultado['resultado']);
  $this->assertStringContainsString("Incorrect integer value", $resultado['mensaje']);
 }
    public function testExisteCohorte_Existe_ModoRegistrar()
    {
        $this->pdoMock->method('prepare')->with("SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte")->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'OTRA-MALLA']); 
        $this->malla->setMalCohorte(5);
        $resultado = $this->malla->ExisteCohorte(false);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testExisteCohorte_NoExiste_ModoModificar()
    {
        $this->pdoMock->method('prepare')
             ->with($this->stringContains("AND mal_codigo != :mal_codigo_excluir"))
             ->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->malla->setMalCohorte(5);
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->ExisteCohorte(true);
        $this->assertEquals('ok', $resultado['resultado']);
    }
     public function testExisteCohorte_Existe_ModoModificar()
    {
        $this->pdoMock->method('prepare')
             ->with($this->stringContains("AND mal_codigo != :mal_codigo_excluir"))
             ->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'OTRA-MALLA']); 
        $this->malla->setMalCohorte(5);
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->ExisteCohorte(true);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testExisteCohorte_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error chequeando cohorte'));
        $this->malla->setMalCohorte(5);
        $resultado = $this->malla->ExisteCohorte(false);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error chequeando cohorte', $resultado['mensaje']);
    }
    public function testObtenerUnidadesCurriculares_Exito()
    {
        $datosEsperados = [
            ['uc_codigo' => 'UC-01', 'uc_nombre' => 'Unidad 1', 'uc_trayecto' => '0'],
            ['uc_codigo' => 'UC-02', 'uc_nombre' => 'Unidad 2', 'uc_trayecto' => '1']
        ];
        $this->pdoMock->method('query')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosEsperados);
        $resultado = $this->malla->obtenerUnidadesCurriculares();
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }
    public function testObtenerUnidadesCurriculares_Falla_DBException()
    {
        $this->pdoMock->method('query')->willThrowException(new PDOException('Error obteniendo UCs'));
        $resultado = $this->malla->obtenerUnidadesCurriculares();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error obteniendo UCs', $resultado['mensaje']);
    }
    public function testObtenerUnidadesPorMalla_Exito()
    {
        $datosEsperados = [
            ['uc_codigo' => 'UC-01', 'uc_nombre' => 'Unidad 1', 'uc_trayecto' => '0', 'mal_hora_independiente' => 10, 'mal_hora_asistida' => 5, 'mal_hora_academica' => 3],
        ];
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosEsperados);
        $this->malla->setMalCodigo('MALLA-1');
        $resultado = $this->malla->obtenerUnidadesPorMalla();
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }
    public function testObtenerUnidadesPorMalla_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error obteniendo UCs por malla'));
        $this->malla->setMalCodigo('MALLA-1');
        $resultado = $this->malla->obtenerUnidadesPorMalla();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error obteniendo UCs por malla', $resultado['mensaje']);
    }
 public function testCambiarEstadoActivo_EstadoActualEsNuloOInvalido()
 {
  $this->pdoMock->expects($this->once())->method('beginTransaction');
  $this->pdoMock->expects($this->once())->method('commit');
  $stmtSelect = $this->createMock(PDOStatement::class);
  $stmtUpdate = $this->createMock(PDOStatement::class);
  $this->pdoMock->expects($this->exactly(2))
   ->method('prepare')
   ->withConsecutive(
    [$this->stringContains('SELECT mal_activa')],
    [$this->stringContains('UPDATE tbl_malla SET mal_activa')]
   )
   ->willReturnOnConsecutiveCalls($stmtSelect, $stmtUpdate);
  $stmtSelect->method('fetchColumn')->willReturn(null); 
  $stmtSelect->expects($this->once())->method('execute');
  $stmtUpdate->expects($this->any())->method('bindParam');
  $stmtUpdate->expects($this->once())->method('execute');
  $resultado = $this->malla->cambiarEstadoActivo();
  $this->assertEquals('ok', $resultado['resultado']);
  $this->assertEquals('activar', $resultado['accion_bitacora']);
  $this->assertStringContainsString('activada', $resultado['mensaje']);
 }   
}