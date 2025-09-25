<?php

use PHPUnit\Framework\TestCase;

require_once 'model/archivo.php';

class ArchivoTest extends TestCase
{
    private $archivoMock;
    private $mockPdo;
    private $mockStmt;
    private static $archivosDir = __DIR__ . '/../archivos_subidos';
    private static $archivosPerDir = __DIR__ . '/../archivos_per';

    public static function setUpBeforeClass(): void
    {
        if (!is_dir(self::$archivosDir)) {
            mkdir(self::$archivosDir, 0777, true);
        }
        if (!is_dir(self::$archivosPerDir)) {
            mkdir(self::$archivosPerDir, 0777, true);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::$archivosDir)) {
            rmdir(self::$archivosDir);
        }
        if (is_dir(self::$archivosPerDir)) {
            rmdir(self::$archivosPerDir);
        }
    }

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $this->archivoMock = $this->getMockBuilder(Archivo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerFaseActual'])
            ->getMock();

        $this->archivoMock->method('Con')->willReturn($this->mockPdo);
    }

    public function testSettersYGettersFuncionanCorrectamente()
    {
        $this->archivoMock->setUcCodigo('PIFOC090103');
        $this->assertEquals('PIFOC090103', $this->archivoMock->getUcCodigo());
    }

    public function testGenerarIdentificadorUnicoEsCorrecto()
    {
        $this->archivoMock->setUcCodigo('PIALP306112');
        $this->archivoMock->setSecCodigo('2103,2113');
        $this->archivoMock->setAnioAnio('2025');
        $this->archivoMock->setAnioTipo('regular');
        $this->archivoMock->setFaseNumero(1);

        $reflection = new ReflectionClass(Archivo::class);
        $method = $reflection->getMethod('generarIdentificadorUnico');
        $method->setAccessible(true);

        $identificador = $method->invoke($this->archivoMock);
        $this->assertEquals('PIALP306112_2103_2113_2025_regular_fase1', $identificador);
    }

    public function testGuardarRegistroInicialFallaSiNoHayArchivo()
    {
        $archivoFalso = ['error' => UPLOAD_ERR_NO_FILE];
        $resultado = $this->archivoMock->guardarRegistroInicial($archivoFalso);
        $this->assertFalse($resultado['success']);
    }

    public function testRegistrarAprobadosPerExitoso()
    {
        $this->archivoMock->setPerAprobados(8);
        $this->archivoMock->setAnioAnio('2025');
        $this->archivoMock->setAnioTipo('regular');
        $this->archivoMock->setFaseNumero(1);
        $this->archivoMock->setUcCodigo('PIBAD090203');
        $this->archivoMock->setSecCodigo('3103');

        $archivoFalso = ['error' => UPLOAD_ERR_OK, 'name' => 'per.pdf', 'tmp_name' => tempnam(sys_get_temp_dir(), 'test')];

        $this->mockPdo->expects($this->once())->method('beginTransaction');
        $this->mockPdo->expects($this->once())->method('prepare')->willReturn($this->mockStmt);
        $this->mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $this->mockPdo->expects($this->once())->method('commit');

        $resultado = $this->archivoMock->registrarAprobadosPer($archivoFalso);

        $this->assertTrue($resultado['success']);
        unlink($archivoFalso['tmp_name']);
    }

    public function testEliminarRegistroCompletoExitoso()
    {
        $this->archivoMock->setAnioAnio('2025');
        $this->archivoMock->setAnioTipo('regular');
        $this->archivoMock->setFaseNumero(1);
        $this->archivoMock->setUcCodigo('PIFOC090103');
        $this->archivoMock->setSecCodigo('2103,2113');

        $this->mockPdo->expects($this->once())->method('beginTransaction');
        $this->mockPdo->expects($this->exactly(2))->method('prepare')->willReturn($this->mockStmt);
        $this->mockStmt->expects($this->exactly(4))->method('execute')->willReturn(true);
        $this->mockPdo->expects($this->once())->method('commit');

        $resultado = $this->archivoMock->eliminarRegistroCompleto();
        $this->assertTrue($resultado['success']);
    }

    public function testListarRegistrosAgrupaCorrectamente()
    {
        $registrosDb = [
            ['ani_anio' => '2025', 'ani_tipo' => 'regular', 'fase_numero' => 1, 'uc_codigo' => 'UC101', 'sec_codigo' => 'SEC01', 'sec_cantidad' => 20, 'apro_cantidad' => 15, 'per_cantidad' => 5, 'per_aprobados' => 3, 'uc_nombre' => 'Unidad 1'],
            ['ani_anio' => '2025', 'ani_tipo' => 'regular', 'fase_numero' => 1, 'uc_codigo' => 'UC101', 'sec_codigo' => 'SEC02', 'sec_cantidad' => 25, 'apro_cantidad' => 0, 'per_cantidad' => 0, 'per_aprobados' => 0, 'uc_nombre' => 'Unidad 1'],
        ];

        $signaturesDb = [
            ['ani_anio' => '2025', 'ani_tipo' => 'regular', 'uc_codigo' => 'UC101', 'sec_codigo' => 'SEC01', 'signature' => 'lunes-08:00'],
            ['ani_anio' => '2025', 'ani_tipo' => 'regular', 'uc_codigo' => 'UC101', 'sec_codigo' => 'SEC02', 'signature' => 'lunes-08:00'],
        ];

        $this->mockPdo->expects($this->exactly(2))->method('prepare')->willReturn($this->mockStmt);
        $this->mockStmt->expects($this->exactly(2))->method('execute');
        $this->mockStmt->method('fetchAll')->will($this->onConsecutiveCalls($registrosDb, $signaturesDb));

        $this->archivoMock->method('obtenerFaseActual')->willReturn(['fase_numero' => 1, 'ani_anio' => '2025']);
        $resultado = $this->archivoMock->listarRegistros('12345', 'Administrador', false);
        $this->assertCount(1, $resultado);
        $this->assertEquals('SEC01,SEC02', $resultado[0]['sec_codigo']);
        $this->assertEquals(45, $resultado[0]['sec_cantidad']);
    }

    public function testEsPeriodoPerAbiertoParaFaseSiguiente()
    {
        $this->archivoMock->method('obtenerFaseActual')->willReturn(['ani_anio' => '2025', 'fase_numero' => 2]);
        $reflection = new ReflectionClass(Archivo::class);
        $method = $reflection->getMethod('esPeriodoPerAbierto');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($this->archivoMock, '2025', 1));
    }

    public function testEsPeriodoPerAbiertoParaAnioSiguiente()
    {
        $this->archivoMock->method('obtenerFaseActual')->willReturn(['ani_anio' => '2026', 'fase_numero' => 1]);
        $reflection = new ReflectionClass(Archivo::class);
        $method = $reflection->getMethod('esPeriodoPerAbierto');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($this->archivoMock, '2025', 2));
    }

    public function testNoEsPeriodoPerAbiertoParaMismaFase()
    {
        $this->archivoMock->method('obtenerFaseActual')->willReturn(['ani_anio' => '2025', 'fase_numero' => 1]);
        $reflection = new ReflectionClass(Archivo::class);
        $method = $reflection->getMethod('esPeriodoPerAbierto');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($this->archivoMock, '2025', 1));
    }
}
