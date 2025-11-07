<?php

use PHPUnit\Framework\TestCase;
use App\Model\Eje;

class EjeTest extends TestCase
{
    private $eje;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->eje = new Eje();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->eje = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    private function setupMocksParaRegistroExitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);
    }

    public function providerNombresInvalidos()
    {
        return [
            'nombre null' => [null, 'Nombre null'],
            'nombre vacío' => ['', 'Nombre cadena vacía'],
            'nombre espacios' => ['   ', 'Nombre solo espacios'],
            'nombre muy corto' => ['AB', 'Nombre 2 caracteres'],
            'nombre muy largo' => [str_repeat('A', 101), 'Nombre 101 caracteres'],
        ];
    }

    public function providerDescripcionesInvalidas()
    {
        return [
            'descripción muy larga' => [str_repeat('A', 501), 'Descripción 501 caracteres'],
        ];
    }

    public function providerNombresValidos()
    {
        return [
            'nombre mínimo' => ['ABC', 'Nombre 3 caracteres'],
            'nombre normal' => ['Eje Normal', 'Nombre normal'],
            'nombre máximo' => [str_repeat('A', 100), 'Nombre 100 caracteres'],
        ];
    }

    public function testSetAndGetEje()
    {
        $this->eje->setEje('Eje Test');
        $this->assertEquals('Eje Test', $this->eje->getEje());
    }

    public function testSetAndGetDescripcion()
    {
        $this->eje->setDescripcion('Descripción Test');
        $this->assertEquals('Descripción Test', $this->eje->getDescripcion());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->eje->setEje($nombre);
        $this->eje->setDescripcion('Descripción válida');

        $resultado = $this->eje->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testRegistrar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->eje->setEje('Eje válido');
        $this->eje->setDescripcion($descripcion);

        $resultado = $this->eje->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     * @dataProvider providerNombresValidos
     */
    public function testRegistrar_ConNombresValidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->eje->setEje($nombre);
        $this->eje->setDescripcion('Descripción válida');

        $resultado = $this->eje->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    /**
     * @test
     */
    public function testRegistrar_EjeYaExisteActivo()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['eje_estado' => 1]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Existente');
        $this->eje->setDescripcion('Descripción');

        $resultado = $this->eje->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivarEjeInactivo()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['eje_estado' => 0]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Inactivo');
        $this->eje->setDescripcion('Reactivar');

        $resultado = $this->eje->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_SinParametroOriginal()
    {
        $this->eje->setEje('Eje Nuevo');
        $this->eje->setDescripcion('Descripción');

        $resultado = $this->eje->Modificar(null);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->eje->setEje($nombre);
        $this->eje->setDescripcion('Descripción válida');

        $resultado = $this->eje->Modificar('Eje Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testModificar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->eje->setEje('Eje válido');
        $this->eje->setDescripcion($descripcion);

        $resultado = $this->eje->Modificar('Eje Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     */
    public function testModificar_EjeNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Nuevo');
        $this->eje->setDescripcion('Descripción');

        $resultado = $this->eje->Modificar('Eje Inexistente');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_SinCambios()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'eje_nombre' => 'Eje Test',
            'eje_descripcion' => 'Descripción Test'
        ]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Test');
        $this->eje->setDescripcion('Descripción Test');

        $resultado = $this->eje->Modificar('Eje Test');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_NombreYaExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'eje_nombre' => 'Eje Original',
            'eje_descripcion' => 'Descripción'
        ]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $ejeMock = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ejeMock->method('Con')->willReturn($this->pdoMock);
        $ejeMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $ejeMock->setEje('Eje Existente');
        $ejeMock->setDescripcion('Descripción');

        $resultado = $ejeMock->Modificar('Eje Original');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testEliminar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->eje->setEje($nombre);

        $resultado = $this->eje->Eliminar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     */
    public function testEliminar_EjeExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['eje_estado' => 1]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje a Eliminar');

        $resultado = $this->eje->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_EjeNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Inexistente');

        $resultado = $this->eje->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_EjeYaDesactivado()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['eje_estado' => 0]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $this->eje->setEje('Eje Desactivado');

        $resultado = $this->eje->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivado', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_EjeActivo()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchColumn')->willReturn(1);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->eje->Existe('Eje Existente');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testExiste_EjeNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchColumn')->willReturn(0);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->eje->Existe('Eje Inexistente');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    /**
     * @test
     */
    public function testExiste_ConExclusion()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchColumn')->willReturn(0);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->eje = $this->getMockBuilder(Eje::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->eje->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->eje->Existe('Eje Test', 'Eje Test');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}
