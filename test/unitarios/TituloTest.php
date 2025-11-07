<?php
use PHPUnit\Framework\TestCase;
use App\Model\Titulo;

class TituloTest extends TestCase
{
    private $titulo;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->titulo = $this->getMockBuilder(Titulo::class)
            ->onlyMethods(['Con'])
            ->getMock();

        $this->titulo->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }

    protected function tearDown(): void
    {
        $this->titulo = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    /**
     * @dataProvider providerDatosInvalidos
     * @test
     */
    public function testRegistrar_Falla_ValidacionEntrada($prefijo, $nombre, $mensajeEsperado)
    {
        $this->titulo->set_prefijo($prefijo);
        $this->titulo->set_nombreTitulo($nombre);

        $resultado = $this->titulo->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString($mensajeEsperado, $resultado['mensaje']);
    }

    public function providerDatosInvalidos()
    {
        return [
            'Prefijo null' => [null, 'Doctor en Ciencias', 'prefijo'],
            'Prefijo vacío' => ['', 'Doctor en Ciencias', 'prefijo'],
            'Prefijo solo espacios' => ['   ', 'Doctor en Ciencias', 'prefijo'],
            'Prefijo muy corto' => ['D', 'Doctor en Ciencias', 'entre 2 y 10'],
            'Prefijo muy largo' => ['DrIngMsc123', 'Doctor en Ciencias', 'entre 2 y 10'],
            'Nombre null' => ['Dr.', null, 'nombre'],
            'Nombre vacío' => ['Dr.', '', 'nombre'],
            'Nombre solo espacios' => ['Dr.', '   ', 'nombre'],
            'Nombre muy corto' => ['Dr.', 'Ab', 'entre 3 y 100'],
            'Nombre muy largo' => ['Dr.', str_repeat('A', 101), 'entre 3 y 100'],
        ];
    }

    /**
     * @test
     */
    public function testRegistrar_TituloNuevo_Exito()
    {
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExisteActivo, $stmtExisteInactivo, $stmtInsert);

        $stmtExisteActivo->method('fetchColumn')->willReturn(false);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert->expects($this->once())->method('execute');

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor en Ciencias');

        $resultado = $this->titulo->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Se registró el título correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_TituloYaExisteActivo_Error()
    {
        $stmtExisteActivo = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtExisteActivo);

        $stmtExisteActivo->method('fetchColumn')->willReturn(true);

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor en Ciencias');

        $resultado = $this->titulo->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivaTituloInactivo()
    {
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtReactivar = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExisteActivo, $stmtExisteInactivo, $stmtReactivar);

        $stmtExisteActivo->method('fetchColumn')->willReturn(false);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(true);
        $stmtReactivar->expects($this->once())->method('execute');

        $this->titulo->set_prefijo('Lic.');
        $this->titulo->set_nombreTitulo('Licenciado en Educación');

        $resultado = $this->titulo->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Se registró el título correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_Falla_DBException()
    {
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExisteActivo, $stmtExisteInactivo, $stmtInsert);

        $stmtExisteActivo->method('fetchColumn')->willReturn(false);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert->method('execute')->willThrowException(new PDOException('Error de inserción'));

        $this->titulo->set_prefijo('Msc.');
        $this->titulo->set_nombreTitulo('Magister');

        $resultado = $this->titulo->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de inserción', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testConsultar_Exito()
    {
        $datosEsperados = [
            ['tit_prefijo' => 'Dr.', 'tit_nombre' => 'Doctor en Ciencias'],
            ['tit_prefijo' => 'Lic.', 'tit_nombre' => 'Licenciado']
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('fetchAll')->willReturn($datosEsperados);

        $resultado = $this->titulo->Consultar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testConsultar_Falla_DBException()
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willThrowException(new PDOException('Error en consulta'));

        $resultado = $this->titulo->Consultar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en consulta', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_Falla_SinOriginal()
    {
        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');

        $resultado = $this->titulo->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('original', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_Exito_SinCambios()
    {
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtUpdate);

        $stmtDelete->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor en Ciencias');
        $this->titulo->set_original_prefijo('Dr.');
        $this->titulo->set_original_nombre('Doctor en Ciencias');

        $resultado = $this->titulo->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_ConCambios_TituloYaExiste()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')->willReturn($stmtExiste);
        $stmtExiste->method('fetch')->willReturn(['tit_prefijo' => 'Ing.']);

        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Ingeniero');
        $this->titulo->set_original_prefijo('Lic.');
        $this->titulo->set_original_nombre('Licenciado');

        $resultado = $this->titulo->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testModificar_Falla_ConstraintViolation()
    {
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtUpdate);

        $exception = new PDOException('Integrity constraint violation', 23000);

        $stmtUpdate->method('execute')->willThrowException($exception);

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');
        $this->titulo->set_original_prefijo('Dr.');
        $this->titulo->set_original_nombre('Doctor');

        $resultado = $this->titulo->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('está siendo utilizado', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_Exito()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);

        $stmtExiste->method('fetch')->willReturn(['tit_prefijo' => 'Dr.']);
        $stmtUpdate->expects($this->once())->method('execute');

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');

        $resultado = $this->titulo->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('Se eliminó el título correctamente', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_TituloNoExiste()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtExiste);

        $stmtExiste->method('fetch')->willReturn(false);

        $this->titulo->set_prefijo('Inexistente');
        $this->titulo->set_nombreTitulo('No Existe');

        $resultado = $this->titulo->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEliminar_Falla_ConstraintViolation()
    {
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);

        $stmtExiste->method('fetch')->willReturn(['tit_prefijo' => 'Dr.']);

        $exception = new PDOException('Integrity constraint violation', 23000);

        $stmtUpdate->method('execute')->willThrowException($exception);

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');

        $resultado = $this->titulo->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('está siendo utilizado', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_TituloExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['tit_prefijo' => 'Dr.']);

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');

        $resultado = $this->titulo->Existe();

        $this->assertTrue($resultado);
    }

    /**
     * @test
     */
    public function testExiste_TituloNoExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->titulo->set_prefijo('NoExiste');
        $this->titulo->set_nombreTitulo('No Existe');

        $resultado = $this->titulo->Existe();

        $this->assertFalse($resultado);
    }

    /**
     * @test
     */
    public function testExiste_ConOriginales_ExcluyeOriginal()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');
        $this->titulo->set_original_prefijo('Dr.');
        $this->titulo->set_original_nombre('Doctor');

        $resultado = $this->titulo->Existe();

        $this->assertFalse($resultado);
    }

    /**
     * @test
     */
    public function testExiste_Falla_DBException_RetornaTrue()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error DB'));

        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo('Doctor');

        $resultado = $this->titulo->Existe();

        $this->assertTrue($resultado);
    }
}
