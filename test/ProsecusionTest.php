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
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with("SELECT COUNT(*) FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1")
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('1');

        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertTrue($resultado['anio_activo_existe']);
        $this->assertArrayNotHasKey('resultado', $resultado);
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
            ->with(['1A']);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $destinosEsperados = [
            ['sec_codigo' => '2A', 'ani_anio' => '2025'],
            ['sec_codigo' => '2B', 'ani_anio' => '2025']
        ];

        $stmtDestinos->expects($this->once())
            ->method('execute');

        $stmtDestinos->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($destinosEsperados);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual('1A');

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
            ->with(['SECCION-INVALIDA']);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual('SECCION-INVALIDA');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Año de origen no encontrado.', $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_ListaVacia()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestinos = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestinos);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $stmtDestinos->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual('4A');

        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);
        $this->assertEmpty($resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en consulta'));

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual('1A');

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
            ->with(['1A']);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $stmtDestino->expects($this->once())
            ->method('execute')
            ->with(['2A', '2025']);

        $stmtDestino->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2A');

        $resultado = $this->prosecusion->verificarDestinoAutomatico('1A');

        $this->assertTrue($resultado['existe']);
        $this->assertEquals('2A', $resultado['seccion_destino']);
    }

    public function testVerificarDestinoAutomatico_NoExiste()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtDestino);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $stmtDestino->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->verificarDestinoAutomatico('4Z');

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
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->verificarDestinoAutomatico('INVALIDO');

        $this->assertFalse($resultado['existe']);
        $this->assertNull($resultado['seccion_destino']);
    }

    public function testVerificarDestinoAutomatico_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error verificando destino'));

        $resultado = $this->prosecusion->verificarDestinoAutomatico('1A');

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
            ->with(['1A']);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $stmtProsecusionados->expects($this->once())
            ->method('execute')
            ->with(['1A']);

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10');

        $resultado = $this->prosecusion->calcularCantidadProsecusion('1A');

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
            ->with(['INVALIDO']);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->calcularCantidadProsecusion('INVALIDO');

        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertEquals('La sección de origen no es válida o está inactiva.', $resultado['mensaje']);
    }

    public function testCalcularCantidadProsecusion_NoHayDisponibles()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $resultado = $this->prosecusion->calcularCantidadProsecusion('1A');

        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertEquals(0, $resultado['cantidad_disponible']);
        $this->assertStringContainsString('No quedan estudiantes disponibles', $resultado['mensaje']);
    }

    public function testCalcularCantidadProsecusion_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error calculando cantidad'));

        $resultado = $this->prosecusion->calcularCantidadProsecusion('1A');

        $this->assertStringContainsString('Error al calcular la cantidad', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_Cero()
    {
        $resultado = $this->prosecusion->RealizarProsecusion('1A', 0);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_Negativa()
    {
        $resultado = $this->prosecusion->RealizarProsecusion('1A', -5);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadInvalida_NoNumerico()
    {
        $resultado = $this->prosecusion->RealizarProsecusion('1A', 'abc');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('La cantidad de estudiantes debe ser mayor a cero.', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_CantidadSuperaDisponible()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('20');

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('15');

        $resultado = $this->prosecusion->RealizarProsecusion('1A', 10);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Solo hay 5 estudiantes disponibles para prosecusionar', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_SeccionDestinoNoExiste()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);
        $stmtDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))  
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados, $stmtDestino);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10');

        $stmtDestino->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $resultado = $this->prosecusion->RealizarProsecusion('1A', 5, '2A');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe o no está activa', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_RequiereConfirmacionExceso()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);
        $stmtDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados, $stmtDestino);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('30');

        $stmtProsecusionados->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0');

        $stmtDestino->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['sec_cantidad' => '42', 'ani_anio' => '2025']);

        $resultado = $this->prosecusion->RealizarProsecusion('1A', 5, '2A', false);

        $this->assertEquals('confirmacion_requerida', $resultado['resultado']);
        $this->assertStringContainsString('47 estudiantes', $resultado['mensaje']);
        $this->assertStringContainsString('superando el límite de 45', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en la base de datos'));

        $resultado = $this->prosecusion->RealizarProsecusion('1A', 5);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la base de datos', $resultado['mensaje']);
    }

    public function testProsecusionSeccion_NuevoRegistro_Exito()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $stmtOrigenAnio = $this->createMock(PDOStatement::class);
        $stmtDestinoAnio = $this->createMock(PDOStatement::class);
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtUpdateDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(5))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigenAnio, $stmtDestinoAnio, $stmtCheck, $stmtInsert, $stmtUpdateDestino);

        $stmtOrigenAnio->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $stmtDestinoAnio->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2025');

        $stmtCheck->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $stmtInsert->expects($this->once())->method('execute');
        $stmtUpdateDestino->expects($this->once())->method('execute');

        $resultado = $this->prosecusion->ProsecusionSeccion('1A', '2A', 10);

        $this->assertEquals('prosecusion', $resultado['resultado']);
        $this->assertEquals('Prosecusión realizada correctamente!', $resultado['mensaje']);
        $this->assertEquals('2A', $resultado['seccionDestinoCodigo']);
    }

    public function testProsecusionSeccion_ActualizarRegistroExistente_Exito()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');

        $stmtOrigenAnio = $this->createMock(PDOStatement::class);
        $stmtDestinoAnio = $this->createMock(PDOStatement::class);
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdateDestino = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(5))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigenAnio, $stmtDestinoAnio, $stmtCheck, $stmtUpdate, $stmtUpdateDestino);

        $stmtOrigenAnio->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2024');

        $stmtDestinoAnio->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2025');

        $stmtCheck->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['pro_cantidad' => 5]);

        $stmtUpdate->expects($this->once())->method('execute');
        $stmtUpdateDestino->expects($this->once())->method('execute');

        $resultado = $this->prosecusion->ProsecusionSeccion('1A', '2A', 10);

        $this->assertEquals('prosecusion', $resultado['resultado']);
        $this->assertEquals('Prosecusión realizada correctamente!', $resultado['mensaje']);
    }

    public function testProsecusionSeccion_Falla_DBException_ConRollback()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error en transacción'));

        $resultado = $this->prosecusion->ProsecusionSeccion('1A', '2A', 10);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la prosecusión', $resultado['mensaje']);
    }

    public function testListar_Exito()
    {
        $datosEsperados = [
            [
                'sec_origen' => '1A',
                'ani_origen' => '2024',
                'sec_promocion' => '2A',
                'ani_destino' => '2025',
                'pro_cantidad' => 15,
                'origen_codigo' => '1A',
                'origen_cantidad' => 30,
                'destino_codigo' => '2A',
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
                'sec_codigo' => '1A',
                'sec_cantidad' => 30,
                'ani_anio' => '2024',
                'cantidad_prosecusionada' => 10
            ],
            [
                'sec_codigo' => '1B',
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

        $stmtCantidad = $this->createMock(PDOStatement::class);
        $stmtRevertir = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtCantidad, $stmtRevertir, $stmtDelete);

        $stmtCantidad->expects($this->once())
            ->method('execute')
            ->with(['1A', '2024', '2A', '2025']);

        $stmtCantidad->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('15');

        $stmtRevertir->expects($this->once())
            ->method('execute')
            ->with([15, '2A', '2025']);

        $stmtDelete->expects($this->once())
            ->method('execute')
            ->with(['1A', '2024', '2A', '2025']);

        $this->prosecusion->setProId('1A-2024-2A-2025');

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
        $this->prosecusion->setProId('1A-2024-2A');

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Formato de ID inválido.', $resultado['mensaje']);
    }

    public function testEliminar_Falla_DBException_ConRollback()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error eliminando prosecusión'));

        $this->prosecusion->setProId('1A-2024-2A-2025');

        $resultado = $this->prosecusion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error al eliminar la prosecusión', $resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_SQLMalformada()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('SQL mal formada'));

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual('1A');
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('SQL mal formada', $resultado['mensaje']);
    }

    public function testVerificarDestinoAutomatico_SQLMalformada()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error SQL destino'));

        $resultado = $this->prosecusion->verificarDestinoAutomatico('1A');
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('Error SQL destino', $resultado['error']);
    }

    public function testCalcularCantidadProsecusion_OrigenDevuelveNull()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);
        $stmtProsecusionados = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtOrigen, $stmtProsecusionados);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(null);

        $resultado = $this->prosecusion->calcularCantidadProsecusion('1A');
        $this->assertFalse($resultado['puede_prosecusionar']);
        $this->assertStringContainsString('No quedan estudiantes disponibles', $resultado['mensaje']);
    }

    public function testRealizarProsecusion_OrigenNoExiste()
    {
        $stmtOrigen = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigen);

        $stmtOrigen->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $resultado = $this->prosecusion->RealizarProsecusion('NOEXISTE', 5);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no es válida', $resultado['mensaje']);
    }

    public function testProsecusionSeccion_OrigenAnioNull()
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $stmtOrigenAnio = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtOrigenAnio);

        $stmtOrigenAnio->expects($this->once())
            ->method('execute')
            ->willThrowException(new \PDOException('Error en la prosecusión'));

        $this->pdoMock->expects($this->once())->method('rollBack');

        $resultado = $this->prosecusion->ProsecusionSeccion('1A', '2A', 10);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error en la prosecusión', $resultado['mensaje']);
    }
}
