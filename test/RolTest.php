<?php

use PHPUnit\Framework\TestCase;

require_once 'model/rol.php';

class RolTest extends TestCase
{
    private $rol;

    private $pdoMock;

    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con'])
            ->getMock();

        $this->rol->method('Con')->willReturn($this->pdoMock);

        $this->pdoMock->method('setAttribute');
    }

    public function testRegistrar_Exito()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn([]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO tbl_rol'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->rol->setNombre('Docente');

        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_RolYaExiste()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn(['resultado' => 'existe']);

        $this->pdoMock->expects($this->never())->method('prepare');

        $this->rol->setNombre('Administrador');

        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_DBException()
    {
        
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('existe')->willReturn([]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error de BD'));

        $this->rol->setNombre('Docente');
        $resultado = $this->rol->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de BD', $resultado['mensaje']);
    }
    public function testModificar_Exito()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteId', 'existe', 'getNombre'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('existe')->willReturn([]);
        $this->rol->method('getNombre')->willReturn('Docente');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_rol'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->rol->setId(2);
        $this->rol->setNombre('Docente Actualizado');


        $resultado = $this->rol->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultado['mensaje']);
    }

    public function testModificar_Falla_RolAdministrador()
    {
        $this->rol->setNombre('Administrador');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
        $this->assertStringContainsString('no puede ser modificado', $resultado['mensaje']);
    }

    public function testModificar_Falla_RolNoExiste()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteId'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn([]);

        $this->rol->setId(999);
        $this->rol->setNombre('Docente');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('NO existe', $resultado['mensaje']);
    }

    public function testModificar_Falla_NombreYaExiste()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteId', 'existe', 'getNombre'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('existe')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getNombre')->willReturn('Docente');

        $this->rol->setId(2);
        $this->rol->setNombre('Coordinador');

        $resultado = $this->rol->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    public function testEliminar_Exito()
    {
       
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteId', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn(['resultado' => 'existe']);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);

       
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_rol'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->rol->setId(2);

        $resultado = $this->rol->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Eliminado', $resultado['mensaje']);
    }

    public function testEliminar_Falla_RolAdministrador()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Administrador']);

        $this->rol->setId(1);

        $resultado = $this->rol->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
        $this->assertStringContainsString('no puede ser eliminado', $resultado['mensaje']);
    }

    public function testEliminar_Falla_RolNoExiste()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteId', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('ExisteId')->willReturn([]);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);

        $this->rol->setId(999);

        $resultado = $this->rol->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('NO existe', $resultado['mensaje']);
    }

    public function testAsignarPermisos_Exito()
    {
        $permisos = [
            ['per_id' => 1, 'per_accion' => 'registrar'],
            ['per_id' => 2, 'per_accion' => 'consultar']
        ];
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('DELETE FROM rol_permisos')],
                [$this->stringContains('INSERT INTO rol_permisos')]
            )
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtInsert);

        $stmtDelete->expects($this->once())->method('execute');
        $stmtInsert->expects($this->exactly(2))->method('execute');

        $resultado = $this->rol->asignarPermisos(2, $permisos);

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertStringContainsString('Permisos asignados correctamente', $resultado['mensaje']);
    }

    public function testAsignarPermisos_Falla_RolAdministrador()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Administrador']);

        $resultado = $this->rol->asignarPermisos(1, []);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
        $this->assertStringContainsString('no se pueden modificar', $resultado['mensaje']);
    }

    public function testAsignarPermisos_Exito_PermisosVacios()
    {
        $this->rol = $this->getMockBuilder(Rol::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'getRolById'])
            ->getMock();
        $this->rol->method('Con')->willReturn($this->pdoMock);
        $this->rol->method('getRolById')->willReturn(['rol_nombre' => 'Docente']);

        $stmtDelete = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM rol_permisos'))
            ->willReturn($stmtDelete);

        $stmtDelete->expects($this->once())->method('execute');

        $resultado = $this->rol->asignarPermisos(2, []);

        $this->assertEquals('ok', $resultado['resultado']);
    }

    public function testGetRolById_Exito()
    {
        $datosRol = ['rol_nombre' => 'Docente'];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT rol_nombre FROM tbl_rol WHERE rol_id'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($datosRol);

        $resultado = $this->rol->getRolById(2);

        $this->assertEquals($datosRol, $resultado);
    }

    public function testGetRolById_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error de BD'));

        $resultado = $this->rol->getRolById(2);

        $this->assertNull($resultado);
    }

    public function testGettersYSetters()
    {
        $rol = new Rol();
        $rol->setNombre('Docente');
        $this->assertEquals('Docente', $rol->getNombre());

        $rol->setId(5);
        $this->assertEquals(5, $rol->getId());
    }

    public function testConstructor()
    {
        $this->assertTrue(method_exists(Rol::class, '__construct'));
    }
}
