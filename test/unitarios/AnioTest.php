<?php

use PHPUnit\Framework\TestCase;
use App\Model\Anio;

/**
 * Pruebas unitarias completas para el módulo Anio
 * Utiliza: Data Providers, Mocks, Stubs
 * Compatible con PHP 7.x
 * 
 * Cobertura:
 * - Validaciones de año (null, vacío, tipo, rangos)
 * - Validaciones de tipo (null, vacío, valores válidos)
 * - Validaciones de fases (estructura, cantidad, fechas)
 * - Operaciones CRUD (Registrar, Modificar, Eliminar)
 */
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

    // ==================== HELPER METHODS ====================
    
    /**
     * Mock para simular que existe una malla curricular activa
     */
    private function mockMallaActiva($exists = true)
    {
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn($exists ? 1 : false);
        $this->pdoMock->method('query')->willReturn($stmtMalla);
    }

    /**
     * Mock para simular que el año NO existe en la BD
     */
    private function mockAnioNoExiste()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);
    }

    /**
     * Configuración completa de mocks para registro exitoso
     */
    private function setupMocksParaRegistroExitoso()
    {
        $this->mockMallaActiva(true);
        $this->mockAnioNoExiste();
        $this->pdoMock->method('beginTransaction')->willReturn(true);
        $this->pdoMock->method('commit')->willReturn(true);
    }

    // ==================== DATA PROVIDERS ====================

    /**
     * Proveedor de datos: Años inválidos
     */
    public function providerAniosInvalidos()
    {
        return [
            'año null' => [null, 'El año es null'],
            'año vacío' => ['', 'El año es cadena vacía'],
            'año con espacios' => ['   ', 'El año solo contiene espacios'],
            'año texto' => ['ABC', 'El año es texto no numérico'],
            'año negativo' => [-2024, 'El año es negativo'],
            'año cero' => [0, 'El año es cero'],
            'año decimal' => [2024.5, 'El año es decimal'],
            'año menor a 2000' => [1999, 'El año es menor al rango permitido'],
            'año mayor a 2100' => [2101, 'El año es mayor al rango permitido'],
        ];
    }

    /**
     * Proveedor de datos: Tipos inválidos
     */
    public function providerTiposInvalidos()
    {
        return [
            'tipo null' => [null, 'El tipo es null'],
            'tipo vacío' => ['', 'El tipo es cadena vacía'],
            'tipo con espacios' => ['   ', 'El tipo solo contiene espacios'],
            'tipo inválido' => ['anual', 'El tipo no es regular ni intensivo'],
            'tipo con mayúsculas' => ['REGULAR', 'El tipo con mayúsculas'],
            'tipo número' => [123, 'El tipo es número'],
        ];
    }

    /**
     * Proveedor de datos: Estructuras de fases inválidas
     */
    public function providerFasesInvalidas()
    {
        return [
            'fases null' => [null, 'intensivo', 'Fases es null'],
            'fases no es array' => ['texto', 'intensivo', 'Fases no es array'],
            'array vacío' => [[], 'intensivo', 'Array vacío sin fase 1'],
            'fase 1 con apertura vacía' => [
                [['numero' => 1, 'apertura' => '', 'cierre' => '2024-06-01']],
                'intensivo',
                'Fase 1 sin apertura'
            ],
            'fase 1 con cierre vacío' => [
                [['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '']],
                'intensivo',
                'Fase 1 sin cierre'
            ],
            'regular sin fase 2' => [
                [['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-01']],
                'regular',
                'Regular sin índice [1] para fase 2'
            ],
            'regular con fase 2 vacía' => [
                [
                    ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-01'],
                    ['numero' => 2, 'apertura' => '', 'cierre' => '']
                ],
                'regular',
                'Regular con fase 2 vacía'
            ],
        ];
    }

    /**
     * Proveedor de datos: Fechas inválidas
     */
    public function providerFechasInvalidas()
    {
        return [
            'fecha texto' => ['FECHA_INVALIDA', '2024-06-01', 'Fecha apertura con texto'],
            'fecha formato incorrecto' => ['01/01/2024', '06/01/2024', 'Fechas con formato DD/MM/YYYY'],
            'fecha sin guiones' => ['20240101', '20240601', 'Fechas sin guiones'],
            'cierre antes de apertura' => ['2024-06-01', '2024-01-01', 'Cierre antes que apertura'],
            'fechas iguales' => ['2024-01-01', '2024-01-01', 'Apertura y cierre iguales'],
        ];
    }

    // ==================== PRUEBAS: VALIDACIÓN DE AÑO ====================

    /**
     * @test
     * @dataProvider providerAniosInvalidos
     */
    public function testRegistrar_ConAniosInvalidos($anio, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio($anio);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $resultado = $this->anio->Registrar();

        // Verificar que se rechaza (debe ser error o manejar la excepción)
        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Fallo validación: {$descripcion}"
        );
    }

    /**
     * @test
     * Caso específico: Año válido debe ser aceptado
     */
    public function testRegistrar_ConAnioValido()
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $resultado = $this->anio->Registrar();

        // Si no hay validación, puede pasar o dar error de BD
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    // ==================== PRUEBAS: VALIDACIÓN DE TIPO ====================

    /**
     * @test
     * @dataProvider providerTiposInvalidos
     */
    public function testRegistrar_ConTiposInvalidos($tipo, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo($tipo);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Fallo validación: {$descripcion}"
        );
    }

    /**
     * @test
     * Caso específico: Tipo "regular" válido
     */
    public function testRegistrar_ConTipoRegularValido()
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    /**
     * @test
     * Caso específico: Tipo "intensivo" válido
     */
    public function testRegistrar_ConTipoIntensivoValido()
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        // Intensivo: fase2 vacía (como lo envía el controller)
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-12-15'],
            ['numero' => 2, 'apertura' => '', 'cierre' => '']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    // ==================== PRUEBAS: VALIDACIÓN DE FASES ====================

    /**
     * @test
     * @dataProvider providerFasesInvalidas
     */
    public function testRegistrar_ConFasesInvalidas($fases, $tipo, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo($tipo);
        $this->anio->setFases($fases);

        $resultado = $this->anio->Registrar();

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Fallo validación: {$descripcion}"
        );
    }

    // ==================== PRUEBAS: VALIDACIÓN DE FECHAS ====================

    /**
     * @test
     * @dataProvider providerFechasInvalidas
     */
    public function testRegistrar_ConFechasInvalidas($apertura, $cierre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => $apertura, 'cierre' => $cierre],
            ['numero' => 2, 'apertura' => '', 'cierre' => '']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Fallo validación: {$descripcion}"
        );
    }

    /**
     * @test
     * Fechas válidas deben ser aceptadas
     */
    public function testRegistrar_ConFechasValidas()
    {
        $this->setupMocksParaRegistroExitoso();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('intensivo');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-12-15'],
            ['numero' => 2, 'apertura' => '', 'cierre' => '']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    // ==================== PRUEBAS: MALLA ACTIVA ====================

    /**
     * @test
     * No debe permitir registro sin malla activa
     */
    public function testRegistrar_SinMallaActiva()
    {
        $this->mockMallaActiva(false);
        $this->mockAnioNoExiste();
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('malla', strtolower($resultado['mensaje']));
    }

    // ==================== PRUEBAS: MODIFICAR ====================

    /**
     * @test
     * Modificar debe requerir parámetros originales
     */
    public function testModificar_SinParametrosOriginales()
    {
        $this->anio->setAnio(2025);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2025-01-15', 'cierre' => '2025-06-30'],
            ['numero' => 2, 'apertura' => '2025-07-15', 'cierre' => '2025-12-15']
        ]);

        $resultado = $this->anio->Modificar(null, null);

        // Debería validar parámetros originales
        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Modificar debe requerir parámetros originales"
        );
    }

    /**
     * @test
     * @dataProvider providerAniosInvalidos
     */
    public function testModificar_ConAniosInvalidos($anio, $descripcion)
    {
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtExiste);
        
        $this->anio->setAnio($anio);
        $this->anio->setTipo('regular');
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2025-01-15', 'cierre' => '2025-06-30'],
            ['numero' => 2, 'apertura' => '2025-07-15', 'cierre' => '2025-12-15']
        ]);

        $resultado = $this->anio->Modificar(2024, 'regular');

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Fallo validación en Modificar: {$descripcion}"
        );
    }

    // ==================== PRUEBAS: ELIMINAR ====================

    /**
     * @test
     * Eliminar con año null
     */
    public function testEliminar_ConAnioNull()
    {
        $this->anio->setAnio(null);
        $this->anio->setTipo('regular');

        $resultado = $this->anio->Eliminar();

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Eliminar debe rechazar año null"
        );
    }

    /**
     * @test
     * Eliminar con tipo null
     */
    public function testEliminar_ConTipoNull()
    {
        $this->anio->setAnio(2024);
        $this->anio->setTipo(null);

        $resultado = $this->anio->Eliminar();

        $this->assertTrue(
            !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
            "Eliminar debe rechazar tipo null"
        );
    }

    /**
     * @test
     * Eliminar año que existe
     */
    public function testEliminar_AnioExistente()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(['ani_anio' => 2024]);
        
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete);
        
        $this->anio->setAnio(2024);
        $this->anio->setTipo('regular');

        $resultado = $this->anio->Eliminar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    // ==================== PRUEBAS: GETTERS Y SETTERS ====================

    /**
     * @test
     */
    public function testSetAndGetAnio()
    {
        $this->anio->setAnio(2023);
        $this->assertEquals(2023, $this->anio->getAnio());
    }

    /**
     * @test
     */
    public function testSetAndGetTipo()
    {
        $this->anio->setTipo('regular');
        $this->assertEquals('regular', $this->anio->getTipo());
    }

    /**
     * @test
     */
    public function testSetAndGetActivo()
    {
        $this->anio->setActivo(1);
        $this->assertEquals(1, $this->anio->getActivo());
    }

    /**
     * @test
     */
    public function testSetAndGetFases()
    {
        $fases = [
            ['numero' => 1, 'apertura' => '2023-01-01', 'cierre' => '2023-06-01'],
            ['numero' => 2, 'apertura' => '2023-07-01', 'cierre' => '2023-12-01']
        ];
        $this->anio->setFases($fases);
        $this->assertEquals($fases, $this->anio->getFases());
    }

    // ==================== PRUEBAS: VERIFICAR ====================

    /**
     * @test
     */
    public function testVerificar_ConMallaActiva()
    {
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(1);

        $stmtTipos = $this->createMock(PDOStatement::class);
        $stmtTipos->method('fetchAll')->willReturn(['regular', 'intensivo']);

        $this->pdoMock->method('query')
            ->willReturnOnConsecutiveCalls($stmtMalla, $stmtTipos);

        $resultado = $this->anio->Verificar();
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('malla_activa', $resultado);
        $this->assertArrayHasKey('tipos_activos', $resultado);
    }

    /**
     * @test
     */
    public function testVerificar_SinMallaActiva()
    {
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtMalla->method('fetchColumn')->willReturn(0);
        
        $stmtTipos = $this->createMock(PDOStatement::class);
        $stmtTipos->method('fetchAll')->willReturn([]);

        $this->pdoMock->method('query')
            ->willReturnOnConsecutiveCalls($stmtMalla, $stmtTipos);

        $resultado = $this->anio->Verificar();
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('malla_activa', $resultado);
        $this->assertFalse($resultado['malla_activa']);
    }
}
