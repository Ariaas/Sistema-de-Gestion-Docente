<?php

use PHPUnit\Framework\TestCase;
use App\Model\Rol;

class RolTest extends TestCase
{
    private $rol;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->rol = new Rol();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->rol = null;
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
            'nombre muy largo' => [str_repeat('A', 51)],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetNombreRol()
    {
        $this->rol->setNombreRol('Coordinador');
        $this->assertEquals('Coordinador', $this->rol->getNombreRol());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre)
    {
        $this->rol->setNombreRol($nombre);

        $resultado = $this->rol->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testRegistrar_RolYaExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->stmtMock->method('fetchAll')->willReturn([['rol_nombre' => 'Coordinador']]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn(['resultado' => 'existe']);

        $this->rol->setNombreRol('Coordinador');

        $resultado = $this->rol->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testModificar_RolIdNull()
    {
        $this->rol->setNombreRol('Nuevo Rol');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre)
    {
        $this->rol->setRolId(1);
        $this->rol->setNombreRol($nombre);

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_RolAdministrador()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['rol_nombre' => 'Administrador']);
        $this->stmtMock->method('fetchAll')->willReturn([['rol_id' => 1]]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'ExisteId', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Administrador']);

        $this->rol->setRolId(1);
        $this->rol->setNombreRol('Nuevo Nombre');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_RolNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);

        $this->rol->setRolId(999);
        $this->rol->setNombreRol('Rol Inexistente');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testEliminar_RolIdNull()
    {
        $resultado = $this->rol->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_RolAdministrador()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['rol_nombre' => 'Administrador']);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);

        $this->rol->setRolId(1);

        $resultado = $this->rol->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_RolNoExiste()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);

        $this->rol->setRolId(999);

        $resultado = $this->rol->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testRegistrar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn([]);

        $this->rol->setNombreRol('Docente');

        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    public function providerNombresValidos()
    {
        return [
            'nombre normal' => ['Coordinador'],
            'nombre con espacios' => ['Jefe de Departamento'],
            'nombre largo' => ['Coordinador de Investigación'],
        ];
    }

    /**
     * @test
     * @dataProvider providerNombresValidos
     */
    public function testRegistrar_ConNombresValidos($nombre)
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn([]);

        $this->rol->setNombreRol($nombre);

        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['rol_nombre' => 'Coordinador']);
        $this->stmtMock->method('fetchAll')->willReturn([['rol_id' => 2]]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'ExisteId', 'getRolById', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Coordinador']);
        $this->rol->method('existe')->willReturn([]);

        $this->rol->setRolId(2);
        $this->rol->setNombreRol('Coordinador Principal');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_RolDuplicado()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['rol_nombre' => 'Docente']);
        $this->stmtMock->method('fetchAll')->willReturn([['rol_id' => 2]]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'ExisteId', 'getRolById', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);
        $this->rol->method('existe')->willReturn(['resultado' => 'existe']);

        $this->rol->setRolId(2);
        $this->rol->setNombreRol('Coordinador');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testEliminar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['rol_nombre' => 'Docente']);
        $this->stmtMock->method('fetchAll')->willReturn([['rol_id' => 3]]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['Con', 'ExisteId', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);

        $this->rol->setRolId(3);

        $resultado = $this->rol->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
    }
}
