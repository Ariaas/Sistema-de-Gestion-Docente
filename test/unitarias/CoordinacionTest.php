<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/coordinacion.php';

class CoordinacionTest extends TestCase
{
    private $coordinacion;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->coordinacion = new Coordinacion();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->coordinacion = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    public function providerNombresInvalidos()
    {
        return [
            'nombre null' => [null],
            'nombre vacío' => [''],
            'nombre espacios' => ['   '],
            'nombre muy corto' => ['AB'],
            'nombre muy largo' => [str_repeat('A', 101)],
        ];
    }

    public function providerNombresValidos()
    {
        return [
            'nombre válido 3 chars' => ['ABC'],
            'nombre válido medio' => ['Coordinación de Informática'],
            'nombre válido 100 chars' => [str_repeat('A', 100)],
        ];
    }

    public function providerHorasInvalidas()
    {
        return [
            'hora 0' => [0],
            'hora negativa' => [-5],
            'hora 100' => [100],
            'hora 150' => [150],
        ];
    }

    public function providerHorasValidas()
    {
        return [
            'hora 1' => [1],
            'hora 50' => [50],
            'hora 99' => [99],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetNombre()
    {
        $this->coordinacion->setNombre('Coordinación Académica');
        $this->assertEquals('Coordinación Académica', $this->coordinacion->getNombre());
    }

    /**
     * @test
     */
    public function testSetAndGetHoraDescarga()
    {
        $this->coordinacion->setHoraDescarga(10);
        $this->assertEquals(10, $this->coordinacion->getHoraDescarga());
    }

    /**
     * @test
     * @dataProvider providerHorasInvalidas
     */
    public function testSetHoraDescarga_ConHorasInvalidas($hora)
    {
        $this->expectException(Exception::class);
        $this->coordinacion->setHoraDescarga($hora);
    }

    /**
     * @test
     * @dataProvider providerHorasValidas
     */
    public function testSetHoraDescarga_ConHorasValidas($hora)
    {
        $this->coordinacion->setHoraDescarga($hora);
        $this->assertEquals($hora, $this->coordinacion->getHoraDescarga());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre)
    {
        $this->coordinacion->setNombre($nombre);
        $this->coordinacion->setHoraDescarga(10);

        $resultado = $this->coordinacion->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre)
    {
        $this->coordinacion->setOriginalNombre('Original');
        $this->coordinacion->setNombre($nombre);
        $this->coordinacion->setHoraDescarga(10);

        $resultado = $this->coordinacion->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_SinNombreOriginal()
    {
        $this->coordinacion->setNombre('Nueva Coordinación');
        $this->coordinacion->setHoraDescarga(10);

        $resultado = $this->coordinacion->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testModificar_CoordinacionNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->coordinacion = $this->getMockBuilder(Coordinacion::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->coordinacion->method('Con')->willReturn($this->pdoMock);

        $this->coordinacion->setOriginalNombre('Inexistente');
        $this->coordinacion->setNombre('Nuevo Nombre');
        $this->coordinacion->setHoraDescarga(10);

        $resultado = $this->coordinacion->Modificar();

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
            'cor_nombre' => 'Coordinación',
            'coor_hora_descarga' => 10
        ]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->coordinacion = $this->getMockBuilder(Coordinacion::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->coordinacion->method('Con')->willReturn($this->pdoMock);

        $this->coordinacion->setOriginalNombre('Coordinación');
        $this->coordinacion->setNombre('Coordinación');
        $this->coordinacion->setHoraDescarga(10);

        $resultado = $this->coordinacion->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testEliminar_ConNombresInvalidos($nombre)
    {
        $this->coordinacion->setNombre($nombre);

        $resultado = $this->coordinacion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_CoordinacionNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->coordinacion = $this->getMockBuilder(Coordinacion::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->coordinacion->method('Con')->willReturn($this->pdoMock);

        $this->coordinacion->setNombre('Inexistente');

        $resultado = $this->coordinacion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testEliminar_CoordinacionYaDesactivada()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['cor_estado' => 0]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->coordinacion = $this->getMockBuilder(Coordinacion::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->coordinacion->method('Con')->willReturn($this->pdoMock);

        $this->coordinacion->setNombre('Coordinación');

        $resultado = $this->coordinacion->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('desactivad', strtolower($resultado['mensaje']));
    }
}
