<?php

use PHPUnit\Framework\TestCase;
use App\Model\Turno;

class TurnoTest extends TestCase
{
    private $turno;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->turno = new Turno();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->turno = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    private function setupMocksBasicos()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
    }

    public function providerNombresInvalidos()
    {
        return [
            'nombre null' => [null, '07:00:00', '12:00:00'],
            'nombre vacío' => ['', '07:00:00', '12:00:00'],
            'nombre espacios' => ['   ', '07:00:00', '12:00:00'],
            'nombre muy corto' => ['AB', '07:00:00', '12:00:00'],
            'nombre muy largo' => [str_repeat('A', 51), '07:00:00', '12:00:00'],
        ];
    }

    public function providerHorasInvalidas()
    {
        return [
            'hora inicio null' => ['Mañana', null, '12:00:00'],
            'hora inicio vacía' => ['Mañana', '', '12:00:00'],
            'hora fin null' => ['Mañana', '07:00:00', null],
            'hora fin vacía' => ['Mañana', '07:00:00', ''],
        ];
    }

    public function providerDatosValidos()
    {
        return [
            'turno mañana' => ['Mañana', '07:00:00', '12:00:00'],
            'turno tarde' => ['Tarde', '13:00:00', '18:00:00'],
            'turno noche' => ['Noche', '18:30:00', '22:00:00'],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetNombreTurno()
    {
        $this->turno->setNombreTurno('Mañana');
        $this->assertEquals('Mañana', $this->turno->getNombreTurno());
    }

    /**
     * @test
     */
    public function testSetAndGetHoraInicio()
    {
        $this->turno->setHoraInicio('07:00:00');
        $this->assertEquals('07:00:00', $this->turno->getHoraInicio());
    }

    /**
     * @test
     */
    public function testSetAndGetHoraFin()
    {
        $this->turno->setHoraFin('12:00:00');
        $this->assertEquals('12:00:00', $this->turno->getHoraFin());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre, $horaInicio, $horaFin)
    {
        $this->turno->setNombreTurno($nombre);
        $this->turno->setHoraInicio($horaInicio);
        $this->turno->setHoraFin($horaFin);

        $resultado = $this->turno->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerHorasInvalidas
     */
    public function testRegistrar_ConHorasInvalidas($nombre, $horaInicio, $horaFin)
    {
        $this->turno->setNombreTurno($nombre);
        $this->turno->setHoraInicio($horaInicio);
        $this->turno->setHoraFin($horaFin);

        $resultado = $this->turno->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testRegistrar_HoraFinMenorQueInicio()
    {
        $this->setupMocksBasicos();

        $turnoMock = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con', 'chequearSolapamiento'])
            ->getMock();
        $turnoMock->method('Con')->willReturn($this->pdoMock);
        $turnoMock->method('chequearSolapamiento')->willReturn(['solapamiento' => false]);

        $turnoMock->setNombreTurno('Mañana');
        $turnoMock->setHoraInicio('12:00:00');
        $turnoMock->setHoraFin('07:00:00');

        $resultado = $turnoMock->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('mayor', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_TurnoYaExisteActivo()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['tur_estado' => 1]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $turnoMock = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con', 'chequearSolapamiento'])
            ->getMock();
        $turnoMock->method('Con')->willReturn($this->pdoMock);
        $turnoMock->method('chequearSolapamiento')->willReturn(['solapamiento' => false]);

        $turnoMock->setNombreTurno('Mañana');
        $turnoMock->setHoraInicio('07:00:00');
        $turnoMock->setHoraFin('12:00:00');

        $resultado = $turnoMock->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivarTurnoInactivo()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['tur_estado' => 0]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $turnoMock = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con', 'chequearSolapamiento'])
            ->getMock();
        $turnoMock->method('Con')->willReturn($this->pdoMock);
        $turnoMock->method('chequearSolapamiento')->willReturn(['solapamiento' => false]);

        $turnoMock->setNombreTurno('Mañana');
        $turnoMock->setHoraInicio('07:00:00');
        $turnoMock->setHoraFin('12:00:00');

        $resultado = $turnoMock->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_SinNombreOriginal()
    {
        $this->turno->setNombreTurno('Mañana');
        $this->turno->setHoraInicio('07:00:00');
        $this->turno->setHoraFin('12:00:00');

        $resultado = $this->turno->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre, $horaInicio, $horaFin)
    {
        $this->turno->setNombreTurnoOriginal('Viejo');
        $this->turno->setNombreTurno($nombre);
        $this->turno->setHoraInicio($horaInicio);
        $this->turno->setHoraFin($horaFin);

        $resultado = $this->turno->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_TurnoNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->turno = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->turno->method('Con')->willReturn($this->pdoMock);

        $this->turno->setNombreTurnoOriginal('Inexistente');
        $this->turno->setNombreTurno('Mañana');
        $this->turno->setHoraInicio('07:00:00');
        $this->turno->setHoraFin('12:00:00');

        $resultado = $this->turno->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_SinCambios()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'tur_nombre' => 'Mañana',
            'tur_horaInicio' => '07:00:00',
            'tur_horaFin' => '12:00:00'
        ]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->turno = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->turno->method('Con')->willReturn($this->pdoMock);

        $this->turno->setNombreTurnoOriginal('Mañana');
        $this->turno->setNombreTurno('Mañana');
        $this->turno->setHoraInicio('07:00:00');
        $this->turno->setHoraFin('12:00:00');

        $resultado = $this->turno->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testEliminar_ConNombresInvalidos($nombre, $hora1, $hora2)
    {
        $this->turno->setNombreTurno($nombre);

        $resultado = $this->turno->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_TurnoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['tur_estado' => 1]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->turno = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->turno->method('Con')->willReturn($this->pdoMock);

        $this->turno->setNombreTurno('Mañana');

        $resultado = $this->turno->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_TurnoNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->turno = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->turno->method('Con')->willReturn($this->pdoMock);

        $this->turno->setNombreTurno('Inexistente');

        $resultado = $this->turno->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_TurnoYaDesactivado()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['tur_estado' => 0]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->turno = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->turno->method('Con')->willReturn($this->pdoMock);

        $this->turno->setNombreTurno('Mañana');

        $resultado = $this->turno->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('desactivado', $resultado['mensaje']);
    }
}
