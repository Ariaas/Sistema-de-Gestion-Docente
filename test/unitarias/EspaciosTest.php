<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/espacios.php';

class EspaciosTest extends TestCase
{
    private $espacio;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->espacio = new Espacio();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->espacio = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    public function providerNumerosInvalidos()
    {
        return [
            'número null' => [null, 'Edificio A', 'Aula'],
            'número vacío' => ['', 'Edificio A', 'Aula'],
            'número espacios' => ['   ', 'Edificio A', 'Aula'],
            'número muy largo' => [str_repeat('1', 11), 'Edificio A', 'Aula'],
        ];
    }

    public function providerEdificiosInvalidos()
    {
        return [
            'edificio null' => ['101', null, 'Aula'],
            'edificio vacío' => ['101', '', 'Aula'],
            'edificio espacios' => ['101', '   ', 'Aula'],
            'edificio muy largo' => ['101', str_repeat('A', 51), 'Aula'],
        ];
    }

    public function providerTiposInvalidos()
    {
        return [
            'tipo null' => ['101', 'Edificio A', null],
            'tipo vacío' => ['101', 'Edificio A', ''],
            'tipo espacios' => ['101', 'Edificio A', '   '],
            'tipo muy corto' => ['101', 'Edificio A', 'AB'],
            'tipo muy largo' => ['101', 'Edificio A', str_repeat('A', 51)],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetNumero()
    {
        $this->espacio->setNumero('101');
        $this->assertEquals('101', $this->espacio->getNumero());
    }

    /**
     * @test
     */
    public function testSetAndGetEdificio()
    {
        $this->espacio->setEdificio('Edificio A');
        $this->assertEquals('Edificio A', $this->espacio->getEdificio());
    }

    /**
     * @test
     */
    public function testSetAndGetTipo()
    {
        $this->espacio->setTipo('Aula');
        $this->assertEquals('Aula', $this->espacio->getTipo());
    }

    /**
     * @test
     * @dataProvider providerNumerosInvalidos
     */
    public function testRegistrar_ConNumerosInvalidos($numero, $edificio, $tipo)
    {
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio($edificio);
        $this->espacio->setTipo($tipo);

        $resultado = $this->espacio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerEdificiosInvalidos
     */
    public function testRegistrar_ConEdificiosInvalidos($numero, $edificio, $tipo)
    {
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio($edificio);
        $this->espacio->setTipo($tipo);

        $resultado = $this->espacio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerTiposInvalidos
     */
    public function testRegistrar_ConTiposInvalidos($numero, $edificio, $tipo)
    {
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio($edificio);
        $this->espacio->setTipo($tipo);

        $resultado = $this->espacio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNumerosInvalidos
     */
    public function testModificar_ConNumerosInvalidos($numero, $edificio, $tipo)
    {
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio($edificio);
        $this->espacio->setTipo($tipo);

        $resultado = $this->espacio->Modificar('101', 'Edificio A', 'Aula');

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_SinParametrosOriginales()
    {
        $this->espacio->setNumero('102');
        $this->espacio->setEdificio('Edificio B');
        $this->espacio->setTipo('Laboratorio');

        $resultado = $this->espacio->Modificar(null, null, null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_EspacioNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->espacio = $this->getMockBuilder(Espacio::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->espacio->method('Con')->willReturn($this->pdoMock);

        $this->espacio->setNumero('102');
        $this->espacio->setEdificio('Edificio B');
        $this->espacio->setTipo('Laboratorio');

        $resultado = $this->espacio->Modificar('999', 'Inexistente', 'NoExiste');

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testModificar_SinCambios()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'esp_numero' => '101',
            'esp_edificio' => 'Edificio A',
            'esp_tipo' => 'Aula'
        ]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->espacio = $this->getMockBuilder(Espacio::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->espacio->method('Con')->willReturn($this->pdoMock);

        $this->espacio->setNumero('101');
        $this->espacio->setEdificio('Edificio A');
        $this->espacio->setTipo('Aula');

        $resultado = $this->espacio->Modificar('101', 'Edificio A', 'Aula');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNumerosInvalidos
     */
    public function testEliminar_ConParametrosInvalidos($numero, $edificio, $tipo)
    {
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio($edificio);
        $this->espacio->setTipo($tipo);

        $resultado = $this->espacio->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_EspacioNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->espacio = $this->getMockBuilder(Espacio::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->espacio->method('Con')->willReturn($this->pdoMock);

        $this->espacio->setNumero('999');
        $this->espacio->setEdificio('Inexistente');
        $this->espacio->setTipo('NoExiste');

        $resultado = $this->espacio->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testEliminar_EspacioYaDesactivado()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['esp_estado' => 0]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->espacio = $this->getMockBuilder(Espacio::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->espacio->method('Con')->willReturn($this->pdoMock);

        $this->espacio->setNumero('101');
        $this->espacio->setEdificio('Edificio A');
        $this->espacio->setTipo('Aula');

        $resultado = $this->espacio->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('desactivado', strtolower($resultado['mensaje']));
    }
}
