<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/area.php';

class AreaTest extends TestCase
{
    private $area;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->area = new Area();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->area = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    private function setupMocksParaRegistroExitoso()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);
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
            'nombre normal' => ['Área de Formación', 'Nombre normal'],
            'nombre máximo' => [str_repeat('A', 100), 'Nombre 100 caracteres'],
        ];
    }

    public function testSetAndGetArea()
    {
        $this->area->setArea('Área Test');
        $this->assertEquals('Área Test', $this->area->getArea());
    }

    public function testSetAndGetDescripcion()
    {
        $this->area->setDescripcion('Descripción Test');
        $this->assertEquals('Descripción Test', $this->area->getDescripcion());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->area->setArea($nombre);
        $this->area->setDescripcion('Descripción válida');

        $resultado = $this->area->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testRegistrar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->area->setArea('Área válida');
        $this->area->setDescripcion($descripcion);

        $resultado = $this->area->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     * @dataProvider providerNombresValidos
     */
    public function testRegistrar_ConNombresValidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->area->setArea($nombre);
        $this->area->setDescripcion('Descripción válida');

        $resultado = $this->area->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    /**
     * @test
     */
    public function testRegistrar_AreaYaExisteActiva()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['area_estado' => 1]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);

        $this->area->setArea('Área Existente');
        $this->area->setDescripcion('Descripción');

        $resultado = $this->area->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivarAreaInactiva()
    {
        $callCount = 0;
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->will($this->returnCallback(function() use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return false;
            }
            if ($callCount === 2) {
                return ['area_estado' => 0];
            }
            return false;
        }));

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);

        $this->area->setArea('Área Inactiva');
        $this->area->setDescripcion('Reactivar');

        $resultado = $this->area->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_SinParametroOriginal()
    {
        $this->area->setArea('Área Nueva');
        $this->area->setDescripcion('Descripción');

        $resultado = $this->area->Modificar(null);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->area->setArea($nombre);
        $this->area->setDescripcion('Descripción válida');

        $resultado = $this->area->Modificar('Área Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testModificar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->area->setArea('Área válida');
        $this->area->setDescripcion($descripcion);

        $resultado = $this->area->Modificar('Área Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     */
    public function testModificar_NombreNoExiste()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $areaMock = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $areaMock->method('Con')->willReturn($this->pdoMock);
        $areaMock->method('Existe')->willReturn([]);

        $areaMock->setArea('Área Modificada');
        $areaMock->setDescripcion('Nueva Descripción');

        $resultado = $areaMock->Modificar('Área Original');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_NombreYaExiste()
    {
        $areaMock = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Existe'])
            ->getMock();
        $areaMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $areaMock->setArea('Área Existente');
        $areaMock->setDescripcion('Descripción');

        $resultado = $areaMock->Modificar('Área Original');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testEliminar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->area->setArea($nombre);

        $resultado = $this->area->Eliminar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     */
    public function testEliminar_AreaExiste()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $areaMock = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $areaMock->method('Con')->willReturn($this->pdoMock);
        $areaMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $areaMock->setArea('Área a Eliminar');

        $resultado = $areaMock->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_AreaNoExiste()
    {
        $areaMock = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Existe'])
            ->getMock();
        $areaMock->method('Existe')->willReturn([]);

        $areaMock->setArea('Área Inexistente');

        $resultado = $areaMock->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_AreaActiva()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([['area_nombre' => 'Área Existente']]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->area->Existe('Área Existente');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testExiste_AreaNoExiste()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->area->Existe('Área Inexistente');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    /**
     * @test
     */
    public function testExiste_ConExclusion()
    {
        $this->stmtMock->method('bindParam')->willReturn(true);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->area = $this->getMockBuilder(Area::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->area->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->area->Existe('Área Test', 'Área Test');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}
