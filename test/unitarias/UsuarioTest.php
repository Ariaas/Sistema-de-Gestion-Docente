<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../model/usuario.php';

class UsuarioTest extends TestCase
{
    private $usuario;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->usuario = new Usuario();
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->usuario = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    public function providerNombresInvalidos()
    {
        return [
            'nombre null' => [null, 'user@test.com', 'pass123'],
            'nombre vacío' => ['', 'user@test.com', 'pass123'],
            'nombre espacios' => ['   ', 'user@test.com', 'pass123'],
            'nombre corto' => ['AB', 'user@test.com', 'pass123'],
            'nombre largo' => [str_repeat('A', 51), 'user@test.com', 'pass123'],
        ];
    }

    public function providerCorreosInvalidos()
    {
        return [
            'correo null' => ['usuario', null, 'pass123'],
            'correo vacío' => ['usuario', '', 'pass123'],
            'correo sin @' => ['usuario', 'invalido.com', 'pass123'],
            'correo sin dominio' => ['usuario', 'user@', 'pass123'],
            'correo formato inválido' => ['usuario', 'user@invalido', 'pass123'],
        ];
    }

    public function providerContraseniasInvalidas()
    {
        return [
            'contraseña null' => ['usuario', 'user@test.com', null],
            'contraseña vacía' => ['usuario', 'user@test.com', ''],
            'contraseña corta' => ['usuario', 'user@test.com', '12345'],
        ];
    }

    /**
     * @test
     */
    public function testSetAndGetNombreUsuario()
    {
        $this->usuario->set_nombreUsuario('usuario1');
        $this->assertEquals('usuario1', $this->usuario->get_nombreUsuario());
    }

    /**
     * @test
     */
    public function testSetAndGetCorreoUsuario()
    {
        $this->usuario->set_correoUsuario('user@test.com');
        $this->assertEquals('user@test.com', $this->usuario->get_correoUsuario());
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testRegistrar_ConNombresInvalidos($nombre, $correo, $pass)
    {
        $this->usuario->set_nombreUsuario($nombre);
        $this->usuario->set_correoUsuario($correo);
        $this->usuario->set_contraseniaUsuario($pass);

        $resultado = $this->usuario->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerCorreosInvalidos
     */
    public function testRegistrar_ConCorreosInvalidos($nombre, $correo, $pass)
    {
        $this->usuario->set_nombreUsuario($nombre);
        $this->usuario->set_correoUsuario($correo);
        $this->usuario->set_contraseniaUsuario($pass);

        $resultado = $this->usuario->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerContraseniasInvalidas
     */
    public function testRegistrar_ConContraseniasInvalidas($nombre, $correo, $pass)
    {
        $this->usuario->set_nombreUsuario($nombre);
        $this->usuario->set_correoUsuario($correo);
        $this->usuario->set_contraseniaUsuario($pass);

        $resultado = $this->usuario->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testRegistrar_UsuarioYaExiste()
    {
        $this->pdoMock->method('setAttribute')->willReturn(true);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(true);

        $usuarioMock->set_nombreUsuario('usuario1');
        $usuarioMock->set_correoUsuario('user@test.com');
        $usuarioMock->set_contraseniaUsuario('password123');

        $resultado = $usuarioMock->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('existe', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testRegistrar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(false);

        $usuarioMock->set_nombreUsuario('usuario1');
        $usuarioMock->set_correoUsuario('user@test.com');
        $usuarioMock->set_contraseniaUsuario('password123');

        $resultado = $usuarioMock->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    public function providerDatosValidos()
    {
        return [
            'usuario básico' => ['usuario1', 'user1@test.com', 'pass123456'],
            'usuario con espacios' => ['Usuario Completo', 'usuario@example.com', 'secure_pass'],
            'correo largo' => ['usuario', 'very.long.email@subdomain.example.com', 'password'],
        ];
    }

    /**
     * @test
     * @dataProvider providerDatosValidos
     */
    public function testRegistrar_ConDatosValidos($nombre, $correo, $pass)
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(false);

        $usuarioMock->set_nombreUsuario($nombre);
        $usuarioMock->set_correoUsuario($correo);
        $usuarioMock->set_contraseniaUsuario($pass);

        $resultado = $usuarioMock->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_UsuarioNull()
    {
        $resultado = $this->usuario->Modificar(null, 'Nuevo Nombre', 'nuevo@test.com', null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerNombresInvalidos
     */
    public function testModificar_ConNombresInvalidos($nombre, $correo, $pass)
    {
        $resultado = $this->usuario->Modificar('usuario1', $nombre, $correo, null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     * @dataProvider providerCorreosInvalidos
     */
    public function testModificar_ConCorreosInvalidos($nombre, $correo, $pass)
    {
        $resultado = $this->usuario->Modificar('usuario1', $nombre, $correo, null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testModificar_UsuarioNoExiste()
    {
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(false);

        $resultado = $usuarioMock->Modificar('usuarioXXX', 'Nombre Nuevo', 'nuevo@test.com', null);

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', strtolower($resultado['mensaje']));
    }

    /**
     * @test
     */
    public function testModificar_Exitoso()
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(true);

        $resultado = $usuarioMock->Modificar('usuario1', 'Usuario Modificado', 'nuevo@test.com', null);

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_UsuarioNull()
    {
        $resultado = $this->usuario->Eliminar(null);

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_UsuarioVacio()
    {
        $resultado = $this->usuario->Eliminar('');

        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * @test
     */
    public function testEliminar_UsuarioNoExiste()
    {
        $this->pdoMock->method('setAttribute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(false);

        $resultado = $usuarioMock->Eliminar('usuarioXXX');

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

        $usuarioMock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['Con', 'existe'])
            ->getMock();
        $usuarioMock->method('Con')->willReturn($this->pdoMock);
        $usuarioMock->method('existe')->willReturn(true);

        $resultado = $usuarioMock->Eliminar('usuario1');

        $this->assertEquals('eliminar', $resultado['resultado']);
    }
}
