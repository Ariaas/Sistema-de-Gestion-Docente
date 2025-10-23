<?php

use PHPUnit\Framework\TestCase;

require_once 'model/anio.php';

class AnioTest extends TestCase
{
    private $anio;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->anio = $this->getMockBuilder(Anio::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con'])
            ->getMock();

        $this->anio->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }

    public function testRegistrar_ConAnioNull()
    {
        $this->anio->setAnio(null);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConAnioString()
    {
        $this->anio->setAnio("TEXTO_INVALIDO");
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConAnioNegativo()
    {
        $this->anio->setAnio(-2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConAnioCero()
    {
        $this->anio->setAnio(0);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConTipoNull()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo(null);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConTipoVacio()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConTipoInvalido()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('TIPO_MALICIOSO');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_IntensivoConFasesVacias()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $this->pdoMock->method('beginTransaction');

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConFasesNoArray()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases("NO_ES_ARRAY");

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_RegularConSoloUnaFase()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_FaseSinClaveNumero()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $this->pdoMock->method('beginTransaction');

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConFechaInvalida()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => 'FECHA_INVALIDA', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_CierreAntesDeApertura()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-06-30', 'cierre' => '2024-01-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_SQLInjectionEnTipo()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo("'; DROP TABLE tbl_anio; --");
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testModificar_ConParametrosOriginalesNull()
    {
        $this->anio->setAnio(2025);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2025-01-15', 'cierre' => '2025-06-30'],
            ['numero' => 2, 'apertura' => '2025-07-15', 'cierre' => '2025-12-15']
        ]);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Modificar(null, null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testModificar_IntensivoConFasesVacias()
    {
        $this->anio->setAnio(2025);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([]);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);
        $this->pdoMock->method('beginTransaction');

        $resultado = $this->anio->Modificar(2024, 'intensivo');

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testEliminar_ConAnioNull()
    {
        $this->anio->setAnio(null);
        $this->anio->setTipo('regular');

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConAnioMuyGrande()
    {
        $this->anio->setAnio(999999999);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConNumeroFaseNegativo()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => -1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConTipoConEspacios()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('  regular  ');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConAnioCaracteresEspeciales()
    {
        $this->anio->setAnio("2024@#$");
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_FaseSinClaveApertura()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $this->pdoMock->method('beginTransaction');

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_FaseSinClaveCierre()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $this->pdoMock->method('beginTransaction');

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConTipoNumero()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo(123);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrar_ConFasesNull()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases(null);

        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(true);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testSetAndGetAnio()
    {
        $this->anio->setAnio(2023);
        $this->assertEquals(2023, $this->anio->getAnio());
    }

    public function testSetAndGetTipo()
    {
        $this->anio->setTipo('regular');
        $this->assertEquals('regular', $this->anio->getTipo());
    }

    public function testSetAndGetActivo()
    {
        $this->anio->setActivo(1);
        $this->assertEquals(1, $this->anio->getActivo());
    }

    public function testSetAndGetFases()
    {
        $fases = [
            ['numero' => 1, 'apertura' => '2023-01-01', 'cierre' => '2023-06-01'],
            ['numero' => 2, 'apertura' => '2023-07-01', 'cierre' => '2023-12-01']
        ];
        $this->anio->setFases($fases);
        $this->assertEquals($fases, $this->anio->getFases());
    }

    public function testEliminar_AnioYTipoValidos()
    {
        $this->anio->setAnio(2022);
        $this->anio->setTipo('regular');

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $resultado = $this->anio->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
    }

    public function testEliminar_AnioYTipoNoExisten()
    {
        $this->anio->setAnio(2022);
        $this->anio->setTipo('regular');

        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        $resultado = $this->anio->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
    }

    public function testVerificar_MallaActivaYTiposActivos()
    {
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(1);

        $stmtTipos = $this->createMock(PDOStatement::class);
        $stmtTipos->method('fetchAll')->with(PDO::FETCH_COLUMN, 0)->willReturn(['regular', 'intensivo']);

        $this->pdoMock->method('query')
            ->willReturnOnConsecutiveCalls($stmtMalla, $stmtTipos);

        $resultado = $this->anio->Verificar();
        $tipos = $resultado['tipos_activos'];
        $this->assertTrue($resultado['malla_activa']);
        $this->assertContains('regular', $tipos);
        $this->assertContains('intensivo', $tipos);
    }

    public function testVerificar_SinMallaActiva()
    {
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(0);
        $this->pdoMock->method('query')->willReturn($stmtMalla);

        $stmtTipos = $this->createMock(PDOStatement::class);
        $stmtTipos->method('fetchAll')->willReturn([]);
        $this->pdoMock->method('prepare')->willReturn($stmtTipos);

        $resultado = $this->anio->Verificar();
        $this->assertFalse($resultado['malla_activa']);
        $this->assertIsArray($resultado['tipos_activos']);
        $this->assertEmpty($resultado['tipos_activos']);
    }
}
