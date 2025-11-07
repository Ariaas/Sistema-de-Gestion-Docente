<?php

use PHPUnit\Framework\TestCase;
use App\Model\UC;

class UCTest extends TestCase
{
    private $uc;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->uc = new UC();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->uc = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    public function providerCodigosInvalidos()
    {
        return [
            'código null' => [null, 'UC Válida'],
            'código vacío' => ['', 'UC Válida'],
            'código espacios' => ['   ', 'UC Válida'],
            'código corto' => ['AB', 'UC Válida'],
            'código largo' => [str_repeat('A', 21), 'UC Válida'],
        ];
    }

    public function providerNombresInvalidos()
    {
        return [
            'nombre null' => ['UC001', null],
            'nombre vacío' => ['UC001', ''],
            'nombre espacios' => ['UC001', '   '],
            'nombre corto' => ['UC001', 'AB'],
            'nombre largo' => ['UC001', str_repeat('A', 201)],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetCodigoUC()
    {
        $this->uc->setcodigoUC('UC001');
        $this->assertEquals('UC001', $this->uc->getcodigoUC());
    }

    /**
     * @test
     */
    public function testSetAndGetNombreUC()
    {
        $this->uc->setnombreUC('Matemáticas');
        $this->assertEquals('Matemáticas', $this->uc->getnombreUC());
    }

    /**
     * @test
     * @dataProvider providerCodigosInvalidos
     */
    public function testRegistrar_ConCodigosInvalidos($codigo, $nombre)
    {
        $this->uc->setcodigoUC($codigo);
        $this->uc->setnombreUC($nombre);

        $resultado = $this->uc->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($codigo, $nombre)
    {
        $this->uc->setcodigoUC($codigo);
        $this->uc->setnombreUC($nombre);

        $resultado = $this->uc->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testRegistrar_UCYaExisteActiva()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(['uc_estado' => 1]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->uc = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->uc->method('Con')->willReturn($this->pdoMock);

        $this->uc->setcodigoUC('UC001');
        $this->uc->setnombreUC('Matemáticas');

        $resultado = $this->uc->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->uc = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->uc->method('Con')->willReturn($this->pdoMock);

        $this->uc->setcodigoUC('UC001');
        $this->uc->setnombreUC('Matemáticas I');

        $resultado = $this->uc->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testRegistrar_ReactivarUCInactiva()
    {
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtUpdate);

        $stmtCheck->method('execute')->willReturn(true);
        $stmtCheck->method('fetch')->willReturn(['uc_estado' => 0]);
        $stmtUpdate->method('execute')->willReturn(true);

        $this->uc = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->uc->method('Con')->willReturn($this->pdoMock);

        $this->uc->setcodigoUC('UC001');
        $this->uc->setnombreUC('Matemáticas I');

        $resultado = $this->uc->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerCodigosInvalidos
     */
    public function testModificar_ConCodigosInvalidos($codigo, $nombre)
    {
        $this->uc->setcodigoUC($codigo);
        $this->uc->setnombreUC($nombre);

        $resultado = $this->uc->Modificar('UC-ORIGINAL', $nombre);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($codigo, $nombre)
    {
        $this->uc->setcodigoUC($codigo);
        $this->uc->setnombreUC($nombre);

        $resultado = $this->uc->Modificar('UC001', $nombre);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_UCNoExiste()
    {
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);

        $ucMock = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ucMock->method('Con')->willReturn($this->pdoMock);
        $ucMock->method('Existe')->willReturn([]);

        $ucMock->setcodigoUC('UC002');
        $ucMock->setnombreUC('Nombre Nuevo');

        $resultado = $ucMock->Modificar('UCXXX', 'Nombre Nuevo');

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testModificar_SinCambios()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'uc_codigo' => 'UC001',
            'uc_nombre' => 'Matemáticas I'
        ]);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $ucMock = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ucMock->method('Con')->willReturn($this->pdoMock);
        $ucMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $ucMock->setcodigoUC('UC001');
        $ucMock->setnombreUC('Matemáticas I');

        $resultado = $ucMock->Modificar('UC001', 'Matemáticas I');

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_Exitoso()
    {
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtUpdate);

        $stmtCheck->method('execute')->willReturn(true);
        $stmtCheck->method('fetch')->willReturn(['uc_codigo' => 'UC001', 'uc_nombre' => 'Matemáticas I']);
        $stmtUpdate->method('execute')->willReturn(true);

        $ucMock = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ucMock->method('Con')->willReturn($this->pdoMock);
        $ucMock->method('Existe')->willReturn([]);

        $ucMock->setcodigoUC('UC001');
        $ucMock->setnombreUC('Matemáticas II');

        $resultado = $ucMock->Modificar('UC001', 'Matemáticas II');

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    public function providerCodigosInvalidosEliminar()
    {
        return [
            'código null' => [null],
            'código vacío' => [''],
            'código espacios' => ['   '],
            'código corto' => ['AB'],
            'código largo' => [str_repeat('A', 21)],
        ];
    }

    /**
     * @test
     * @dataProvider providerCodigosInvalidosEliminar
     */
    public function testEliminar_ConCodigosInvalidos($codigo)
    {
        $this->uc->setcodigoUC($codigo);

        $resultado = $this->uc->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_UCNoExiste()
    {
        $this->pdoMock->method('setAttribute')->willReturn(true);

        $ucMock = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ucMock->method('Con')->willReturn($this->pdoMock);
        $ucMock->method('Existe')->willReturn([]);

        $ucMock->setcodigoUC('UCXXX');

        $resultado = $ucMock->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testEliminar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $ucMock = $this->getMockBuilder(UC::class)
            ->onlyMethods(['Con', 'Existe'])
            ->getMock();
        $ucMock->method('Con')->willReturn($this->pdoMock);
        $ucMock->method('Existe')->willReturn(['resultado' => 'existe']);

        $ucMock->setcodigoUC('UC001');

        $resultado = $ucMock->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
    }
}
