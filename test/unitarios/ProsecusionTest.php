<?php

use PHPUnit\Framework\TestCase;

require_once 'model/prosecusion.php';

class ProsecusionTest extends TestCase
{
    /** @var Prosecusion|\PHPUnit\Framework\MockObject\MockObject */
    private $prosecusion;

    /** @var PDO|\PHPUnit\Framework\MockObject\MockObject */
    private $pdoMock;

    /** @var PDOStatement|\PHPUnit\Framework\MockObject\MockObject */
    private $stmtMock;

    private const SECCION_ORIGEN = 'IN2104';
    private const SECCION_DESTINO = 'IIN3104';
    private const SECCION_DESTINO_EXTENDIDA = 'IIN4104';
    private const SECCION_ORIGEN_SECUNDARIA = 'IN2105';
    private const SECCION_INVALIDA = 'IN9999';
    private const SECCION_NO_DESTINO = 'IIN4199';

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->prosecusion = $this->getMockBuilder(Prosecusion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con'])
            ->getMock();

        $this->prosecusion->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }

    public function testSettersYGetters()
    {
        $prosecusion = new Prosecusion();

        $prosecusion->setProId('123-456-789-012');
        $this->assertEquals('123-456-789-012', $prosecusion->getProId());

        $prosecusion->setEstado(1);
        $this->assertEquals(1, $prosecusion->getEstado());
    }

    public function testConstructorConParametros()
    {
        $prosecusion = new Prosecusion('ABC-123', 1);
        $this->assertEquals('ABC-123', $prosecusion->getProId());
        $this->assertEquals(1, $prosecusion->getEstado());
    }

    public function testConstructorSinParametros()
    {
        $prosecusion = new Prosecusion();
        $this->assertNull($prosecusion->getProId());
        $this->assertNull($prosecusion->getEstado());
    }

    public function testVerificarEstado_AnioActivoExiste()
    {
        $stmt2 = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with("SELECT ani_anio FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1 AND ani_tipo = 'regular'")
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt2);

        $stmt2->expects($this->once())
            ->method('execute')
            ->with([2025]);

        $stmt2->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('1');

        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertTrue($resultado['anio_activo_existe']);
        $this->assertEquals(2024, $resultado['anio_activo']);
        $this->assertTrue($resultado['anio_destino_existe']);
    }

    public function testVerificarEstado_NoExisteAnioActivo()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0');

        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertFalse($resultado['anio_activo_existe']);
    }

    public function testVerificarEstado_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willThrowException(new PDOException('Error verificando año activo'));

        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error verificando año activo', $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_Exito()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestinos = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestinos);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $destinosEsperados = [
            ['sec_codigo' => self::SECCION_DESTINO, 'ani_anio' => '2025'],
            ['sec_codigo' => self::SECCION_DESTINO_EXTENDIDA, 'ani_anio' => '2026']
        ];

        $stmtDestinos->expects($this->once())
            ->method('execute')
            ->with([2025, 'regular', 'IN2%', 'IIN3%', '%4']);

        $stmtDestinos->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($destinosEsperados);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual(self::SECCION_ORIGEN);

        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);
        $this->assertEquals($destinosEsperados, $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_AnioOrigenNoEncontrado()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigen);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_INVALIDA]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual(self::SECCION_INVALIDA);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('No se encontró la sección de origen en el año activo.', $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_ListaVacia()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestinos = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestinos);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_DESTINO_EXTENDIDA]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $stmtDestinos->expects($this->once())
            ->method('execute');

        $stmtDestinos->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual(self::SECCION_DESTINO_EXTENDIDA);

        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);
        $this->assertEmpty($resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en consulta'));

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual(self::SECCION_ORIGEN);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error al buscar opciones', $resultado['mensaje']);
    }

    public function testVerificarDestinoAutomatico_Existe()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestino);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $stmtDestino->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_DESTINO, 2025, 'regular']);

        $stmtDestino->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(self::SECCION_DESTINO);

        $resultado = $this->prosecusion->verificarDestinoAutomatico(self::SECCION_ORIGEN);

        $this->assertTrue($resultado['existe']);
        $this->assertEquals(self::SECCION_DESTINO, $resultado['seccion_destino']);
    }

    public function testVerificarDestinoAutomatico_NoExiste()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestino);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_NO_DESTINO]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $stmtDestino->expects($this->once())
            ->method('execute')
            ->with(['IIN5199', 2025, 'regular']);

        $stmtDestino->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->verificarDestinoAutomatico(self::SECCION_NO_DESTINO);

        $this->assertFalse($resultado['existe']);
        $this->assertNull($resultado['seccion_destino']);
    }

    public function testVerificarDestinoAutomatico_SeccionOrigenNoEncontrada()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigen);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_INVALIDA]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $resultado = $this->prosecusion->verificarDestinoAutomatico(self::SECCION_INVALIDA);

        $this->assertFalse($resultado['existe']);
        $this->assertNull($resultado['seccion_destino']);
    }

    public function testVerificarDestinoAutomatico_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error verificando destino'));

        $resultado = $this->prosecusion->verificarDestinoAutomatico(self::SECCION_ORIGEN);

        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('Error verificando destino', $resultado['error']);
    }

    public function testCalcularCantidadProsecusion_Exito()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['sec_cantidad' => '30', 'ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $stmtProsecusionados->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN, 2024, 'regular']);

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10');

        $resultado = $this->prosecusion->calcularCantidadProsecusion(self::SECCION_ORIGEN);

        $this->assertTrue($resultado['puede_prosecusionar']);
        $this->assertEquals(20, $resultado['cantidad_disponible']);
        $this->assertEquals(20, $resultado['cantidad_final']);
        $this->assertEquals('Cálculo exitoso.', $resultado['mensaje']);
    }

    public function testCalcularCantidadProsecusion_SeccionInvalida()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigen);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_INVALIDA]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $resultado = $this->prosecusion->calcularCantidadProsecusion(self::SECCION_INVALIDA);

        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertEquals('La sección de origen no es válida o no pertenece al año activo.', $resultado['mensaje']);
    }

    public function testCalcularCantidadProsecusion_NoHayDisponibles()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['sec_cantidad' => '30', 'ani_anio' => '2024', 'ani_tipo' => 'regular']);

        $stmtProsecusionados->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN, 2024, 'regular']);

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $resultado = $this->prosecusion->calcularCantidadProsecusion(self::SECCION_ORIGEN);

        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertEquals(0, $resultado['cantidad_disponible']);
        $this->assertStringContainsString('No quedan estudiantes disponibles', $resultado['mensaje']);
    }

    public function testCalcularCantidadProsecusion_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error calculando cantidad'));

        $resultado = $this->prosecusion->calcularCantidadProsecusion(self::SECCION_ORIGEN);

        $this->assertStringContainsString('Error al calcular la cantidad', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_Cero()
    {
        $resultado = $this->prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 0);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_Negativa()
    {
        $resultado = $this->prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, -5);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_NoNumerico()
    {
        $resultado = $this->prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 'abc');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadSuperaDisponible()
    {
        /** @var Prosecusion&\PHPUnit\Framework\MockObject\MockObject $prosecusion */
        $prosecusion = $this->getMockBuilder(Prosecusion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'calcularCantidadProsecusion'])
            ->getMock();

        $prosecusion->method('Con')->willReturn($this->pdoMock);
        $prosecusion->method('calcularCantidadProsecusion')->willReturn([
            'puede_prosecusionar' => true,
            'cantidad_disponible' => 5,
            'anio_origen' => 2024,
            'ani_tipo_origen' => 'regular'
        ]);

        $resultado = $prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 10);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Solo hay 5 estudiantes disponibles para prosecusionar', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_SeccionDestinoNoExiste()
    {
        $stmtDestino = $this->createMock(PDOStatement::class);

        /** @var Prosecusion&\PHPUnit\Framework\MockObject\MockObject $prosecusion */
        $prosecusion = $this->getMockBuilder(Prosecusion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'calcularCantidadProsecusion'])
            ->getMock();

        $prosecusion->method('Con')->willReturn($this->pdoMock);
        $prosecusion->method('calcularCantidadProsecusion')->willReturn([
            'puede_prosecusionar' => true,
            'cantidad_disponible' => 20,
            'anio_origen' => 2024,
            'ani_tipo_origen' => 'regular'
        ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtDestino);

        $stmtDestino->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_DESTINO, 2025]);

        $stmtDestino->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $resultado = $prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 5, self::SECCION_DESTINO);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe o no está activa', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_RequiereConfirmacionExceso()
    {
        $stmtDestino = $this->createMock(PDOStatement::class);

        /** @var Prosecusion&\PHPUnit\Framework\MockObject\MockObject $prosecusion */
        $prosecusion = $this->getMockBuilder(Prosecusion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'calcularCantidadProsecusion'])
            ->getMock();

        $prosecusion->method('Con')->willReturn($this->pdoMock);
        $prosecusion->method('calcularCantidadProsecusion')->willReturn([
            'puede_prosecusionar' => true,
            'cantidad_disponible' => 20,
            'anio_origen' => 2024,
            'ani_tipo_origen' => 'regular'
        ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtDestino);

        $stmtDestino->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_DESTINO, 2025]);

        $stmtDestino->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['sec_cantidad' => '42', 'ani_anio' => '2025', 'ani_tipo' => 'regular']);

        $resultado = $prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 5, self::SECCION_DESTINO, false);

        $this->assertEquals('confirmacion_requerida', $resultado['resultado']);
        $this->assertStringContainsString('alcanzará 47', $resultado['mensaje']);
        $this->assertStringContainsString('superando el límite de 45', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en la base de datos'));

        $resultado = $this->prosecusion->RealizarProsecusion(self::SECCION_ORIGEN, 5);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la base de datos', $resultado['mensaje']);
    }

    public function testProsecusionSeccion_NuevoRegistro_Exito()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtUpdateDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtInsert, $stmtUpdateDestino);

        $stmtCheck->expects($this->once())
            ->method('execute')
            ->with([
                self::SECCION_ORIGEN,
                2024,
                'regular',
                self::SECCION_DESTINO,
                2025,
                'regular'
            ]);

        $stmtCheck->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $stmtInsert->expects($this->once())
            ->method('execute')
            ->with([
                self::SECCION_ORIGEN,
                2024,
                'regular',
                self::SECCION_DESTINO,
                2025,
                'regular',
                10
            ]);

        $stmtUpdateDestino->expects($this->once())
            ->method('execute')
            ->with([10, self::SECCION_DESTINO, 2025, 'regular']);

        $resultado = $this->prosecusion->ProsecusionSeccion(
            self::SECCION_ORIGEN,
            2024,
            'regular',
            self::SECCION_DESTINO,
            2025,
            'regular',
            10,
            40
        );

        $this->assertEquals('prosecusion', $resultado['resultado']);
        $this->assertStringContainsString('ahora tiene 50 estudiantes', $resultado['mensaje']);
        $this->assertEquals(self::SECCION_DESTINO, $resultado['seccionDestinoCodigo']);
    }

    public function testProsecusionSeccion_ActualizarRegistroExistente_Exito()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdateDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtUpdate, $stmtUpdateDestino);

        $stmtCheck->expects($this->once())
            ->method('execute')
            ->with([
                self::SECCION_ORIGEN,
                2024,
                'regular',
                self::SECCION_DESTINO,
                2025,
                'regular'
            ]);

        $stmtCheck->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['pro_cantidad' => 5]);

        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->with([
                10,
                self::SECCION_ORIGEN,
                2024,
                'regular',
                self::SECCION_DESTINO,
                2025,
                'regular'
            ]);

        $stmtUpdateDestino->expects($this->once())
            ->method('execute')
            ->with([10, self::SECCION_DESTINO, 2025, 'regular']);

        $resultado = $this->prosecusion->ProsecusionSeccion(
            self::SECCION_ORIGEN,
            2024,
            'regular',
            self::SECCION_DESTINO,
            2025,
            'regular',
            10,
            30
        );

        $this->assertEquals('prosecusion', $resultado['resultado']);
        $this->assertStringContainsString('ahora tiene 40 estudiantes', $resultado['mensaje']);
        $this->assertEquals(self::SECCION_DESTINO, $resultado['seccionDestinoCodigo']);
    }

    public function testProsecusionSeccion_Falla_DBException_ConRollback()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->method('inTransaction')->willReturn(true);
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en transacción'));

        $resultado = $this->prosecusion->ProsecusionSeccion(
            self::SECCION_ORIGEN,
            2024,
            'regular',
            self::SECCION_DESTINO,
            2025,
            'regular',
            10,
            35
        );

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la prosecusión', $resultado['mensaje']);
    }

    public function testListar_Exito()
    {
        $datosEsperados = [
            [
                'sec_origen' => self::SECCION_ORIGEN,
                'ani_origen' => '2024',
                'sec_promocion' => self::SECCION_DESTINO,
                'ani_destino' => '2025',
                'pro_cantidad' => 15,
                'origen_codigo' => self::SECCION_ORIGEN,
                'origen_cantidad' => 30,
                'destino_codigo' => self::SECCION_DESTINO,
                'destino_cantidad' => 40
            ]
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($datosEsperados);

        $resultado = $this->prosecusion->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    public function testListar_ListaVacia()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $resultado = $this->prosecusion->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEmpty($resultado['mensaje']);
    }

    public function testListar_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willThrowException(new PDOException('Error listando prosecusiones'));

        $resultado = $this->prosecusion->Listar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error listando prosecusiones', $resultado['mensaje']);
    }

    public function testListarSeccionesOrigen_Exito()
    {
        $datosEsperados = [
            [
                'sec_codigo' => self::SECCION_ORIGEN,
                'sec_cantidad' => 30,
                'ani_anio' => '2024',
                'cantidad_prosecusionada' => 10
            ],
            [
                'sec_codigo' => self::SECCION_ORIGEN_SECUNDARIA,
                'sec_cantidad' => 25,
                'ani_anio' => '2024',
                'cantidad_prosecusionada' => 5
            ]
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($datosEsperados);

        $resultado = $this->prosecusion->ListarSeccionesOrigen();

        $this->assertEquals('consultarSeccionesOrigen', $resultado['resultado']);
        $this->assertCount(2, $resultado['mensaje']);
        $this->assertEquals(20, $resultado['mensaje'][0]['cantidad_disponible']);
        $this->assertEquals(20, $resultado['mensaje'][1]['cantidad_disponible']);
    }

    public function testListarSeccionesOrigen_ListaVacia()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $resultado = $this->prosecusion->ListarSeccionesOrigen();

        $this->assertEquals('consultarSeccionesOrigen', $resultado['resultado']);
        $this->assertEmpty($resultado['mensaje']);
    }

    public function testListarSeccionesOrigen_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error listando secciones'));

        $resultado = $this->prosecusion->ListarSeccionesOrigen();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error listando secciones', $resultado['mensaje']);
    }

    public function testEliminar_Exito()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $stmtOrigenTipo = $this->createMock(PDOStatement::class);
        $stmtCantidad = $this->createMock(PDOStatement::class);
        $stmtRevertir = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(4))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigenTipo, $stmtCantidad, $stmtRevertir, $stmtDelete);

        $stmtOrigenTipo->expects($this->exactly(2))->method('execute');
        $stmtOrigenTipo->expects($this->exactly(2))->method('fetchColumn')
            ->willReturnOnConsecutiveCalls('regular', 'regular');

        $stmtCantidad->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN, '2024', 'regular', self::SECCION_DESTINO, '2025', 'regular']);

        $stmtCantidad->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('15');

        $stmtRevertir->expects($this->once())
            ->method('execute')
            ->with([15, self::SECCION_DESTINO, '2025', 'regular']);

        $stmtDelete->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN, '2024', 'regular', self::SECCION_DESTINO, '2025', 'regular']);

        $this->prosecusion->setProId(sprintf('%s-2024-%s-2025', self::SECCION_ORIGEN, self::SECCION_DESTINO));

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Eliminado', $resultado['mensaje']);
    }

    public function testEliminar_IDNoProporcionado()
    {
        $this->prosecusion->setProId('');

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('ID de prosecusión no proporcionado.', $resultado['mensaje']);
    }

    public function testEliminar_IDNull()
    {
        $this->prosecusion->setProId(null);

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('ID de prosecusión no proporcionado.', $resultado['mensaje']);
    }

    public function testEliminar_FormatoIDInvalido()
    {
        $this->prosecusion->setProId(sprintf('%s-2024-%s', self::SECCION_ORIGEN, self::SECCION_DESTINO));

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Formato de ID inválido.', $resultado['mensaje']);
    }

    public function testEliminar_Falla_DBException_ConRollback()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->method('inTransaction')->willReturn(true);
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error eliminando prosecusión'));

        $this->prosecusion->setProId(sprintf('%s-2024-%s-2025', self::SECCION_ORIGEN, self::SECCION_DESTINO));

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error al eliminar la prosecusión', $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_SQLMalformada()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('SQL mal formada'));

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual(self::SECCION_ORIGEN);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('SQL mal formada', $resultado['mensaje']);
    }

    public function testVerificarDestinoAutomatico_SQLMalformada()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error SQL destino'));

        $resultado = $this->prosecusion->verificarDestinoAutomatico(self::SECCION_ORIGEN);
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('Error SQL destino', $resultado['error']);
    }

    public function testCalcularCantidadProsecusion_OrigenDevuelveNull()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigen);

        $stmtOrigen->expects($this->once())
            ->method('execute')
            ->with([self::SECCION_ORIGEN]);

        $stmtOrigen->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(null);

        $resultado = $this->prosecusion->calcularCantidadProsecusion(self::SECCION_ORIGEN);
        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertStringContainsString('no pertenece al año activo', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_OrigenNoExiste()
    {
        /** @var Prosecusion&\PHPUnit\Framework\MockObject\MockObject $prosecusion */
        $prosecusion = $this->getMockBuilder(Prosecusion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'calcularCantidadProsecusion'])
            ->getMock();

        $prosecusion->method('Con')->willReturn($this->pdoMock);
        $prosecusion->method('calcularCantidadProsecusion')->willReturn([
            'puede_prosecusionar' => false,
            'mensaje' => 'La sección de origen no es válida o no pertenece al año activo.'
        ]);

        $resultado = $prosecusion->RealizarProsecusion(self::SECCION_INVALIDA, 5);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no pertenece al año activo', $resultado['mensaje']);
    }

    public function testProsecusionSeccion_OrigenAnioNull()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->method('inTransaction')->willReturn(true);
        $this->pdoMock->expects($this->once())->method('rollBack');
        $stmtOrigenAnio = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigenAnio);

        $stmtOrigenAnio->expects($this->once())
            ->method('execute')
            ->with([
                self::SECCION_ORIGEN,
                2024,
                'regular',
                self::SECCION_DESTINO,
                2025,
                'regular'
            ])
            ->willThrowException(new \PDOException('Error en la prosecusión'));

        $resultado = $this->prosecusion->ProsecusionSeccion(
            self::SECCION_ORIGEN,
            2024,
            'regular',
            self::SECCION_DESTINO,
            2025,
            'regular',
            10,
            35
        );
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la prosecusión', $resultado['mensaje']);
    }
}
