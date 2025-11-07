<?php

use PHPUnit\Framework\TestCase;
use App\Model\Categoria;

class CategoriaTest extends TestCase
{
    private $categoria;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->categoria = new Categoria();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->categoria = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    private function setupMocksParaRegistroExitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);
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
            'nombre normal' => ['Categoría Normal', 'Nombre normal'],
            'nombre máximo' => [str_repeat('A', 100), 'Nombre 100 caracteres'],
        ];
    }

    public function testSetAndGetCategoria()
    {
        $this->categoria->setCategoria('Categoría Test');
        $this->assertEquals('Categoría Test', $this->categoria->getCategoria());
    }

    public function testSetAndGetDescripcion()
    {
        $this->categoria->setDescripcion('Descripción Test');
        $this->assertEquals('Descripción Test', $this->categoria->getDescripcion());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->categoria->setCategoria($nombre);
        $this->categoria->setDescripcion('Descripción válida');

        $resultado = $this->categoria->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testRegistrar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->categoria->setCategoria('Categoría válida');
        $this->categoria->setDescripcion($descripcion);

        $resultado = $this->categoria->Registrar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     * @dataProvider providerNombresValidos
     */
    public function testRegistrar_ConNombresValidos($nombre, $descripcion)
    {
        $this->setupMocksParaRegistroExitoso();

        $this->categoria->setCategoria($nombre);
        $this->categoria->setDescripcion('Descripción válida');

        $resultado = $this->categoria->Registrar();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
    }

    /**
     * @test
     */
    public function testRegistrar_CategoriaYaExisteActiva()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['cat_estado' => 1]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Existente');
        $this->categoria->setDescripcion('Descripción');

        $resultado = $this->categoria->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivarCategoriaInactiva()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['cat_estado' => 0]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Inactiva');
        $this->categoria->setDescripcion('Reactivar');

        $resultado = $this->categoria->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_SinParametroOriginal()
    {
        $this->categoria->setCategoria('Categoría Nueva');
        $this->categoria->setDescripcion('Descripción');

        $resultado = $this->categoria->Modificar(null);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->categoria->setCategoria($nombre);
        $this->categoria->setDescripcion('Descripción válida');

        $resultado = $this->categoria->Modificar('Categoría Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     * @dataProvider providerDescripcionesInvalidas
     */
    public function testModificar_ConDescripcionesInvalidas($descripcion, $descripcionTest)
    {
        $this->categoria->setCategoria('Categoría válida');
        $this->categoria->setDescripcion($descripcion);

        $resultado = $this->categoria->Modificar('Categoría Original');

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcionTest}");
    }

    /**
     * @test
     */
    public function testModificar_CategoriaNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Nueva');
        $this->categoria->setDescripcion('Descripción');

        $resultado = $this->categoria->Modificar('Categoría Inexistente');

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
            'cat_nombre' => 'Categoría Test',
            'cat_descripcion' => 'Descripción Test'
        ]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Test');
        $this->categoria->setDescripcion('Descripción Test');

        $resultado = $this->categoria->Modificar('Categoría Test');

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
            'cat_nombre' => 'Categoría Original',
            'cat_descripcion' => 'Descripción'
        ]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $categoriaMock = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $categoriaMock->method('Con')->willReturn($this->pdoMock);
        $categoriaMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $categoriaMock->setCategoria('Categoría Existente');
        $categoriaMock->setDescripcion('Descripción');

        $resultado = $categoriaMock->Modificar('Categoría Original');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testEliminar_ConNombresInvalidos($nombre, $descripcion)
    {
        $this->categoria->setCategoria($nombre);

        $resultado = $this->categoria->Eliminar();

        $this->assertEquals('error', $resultado['resultado'], "Fallo validación: {$descripcion}");
    }

    /**
     * @test
     */
    public function testEliminar_CategoriaExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['cat_estado' => 1]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría a Eliminar');

        $resultado = $this->categoria->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_CategoriaNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Inexistente');

        $resultado = $this->categoria->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_CategoriaYaDesactivada()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['cat_estado' => 0]);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $this->categoria->setCategoria('Categoría Desactivada');

        $resultado = $this->categoria->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivada', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_CategoriaActiva()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchColumn')->willReturn(1);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->categoria->Existe('Categoría Existente');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testExiste_CategoriaNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchColumn')->willReturn(0);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->categoria->Existe('Categoría Inexistente');

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

        $this->categoria = $this->getMockBuilder(Categoria::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->categoria->method('Con')->willReturn($this->pdoMock);

        $resultado = $this->categoria->Existe('Categoría Test', 'Categoría Test');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}
