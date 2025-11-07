<?php
use PHPUnit\Framework\TestCase;
use App\Model\Login;

class LoginTest extends TestCase
{
    private $login;
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->login = $this->getMockBuilder(Login::class)
            ->onlyMethods(['Con'])
            ->getMock();

        $this->login->method('Con')->willReturn($this->pdoMock);
        $this->pdoMock->method('setAttribute');
    }

    protected function tearDown(): void
    {
        $this->login = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    /**
     * @test
     */
    public function testExiste_Falla_UsuarioVacio()
    {
        $this->login->set_nombreUsuario('');
        $this->login->set_contraseniaUsuario('password123');

        $resultado = $this->login->existe();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('usuario es requerido', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_Falla_ContraseniaVacia()
    {
        $this->login->set_nombreUsuario('usuario1');
        $this->login->set_contraseniaUsuario('');

        $resultado = $this->login->existe();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('contraseña es requerida', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_CredencialesCorrectas()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $userData = [
            'usu_id' => 1,
            'usu_cedula' => '12345678',
            'usu_nombre' => 'usuario1',
            'usu_contrasenia' => $hashedPassword,
            'usu_foto' => 'foto.jpg',
            'usu_estado' => 1,
            'usu_docente' => 1
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $this->login->set_nombreUsuario('usuario1');
        $this->login->set_contraseniaUsuario('password123');

        $resultado = $this->login->existe();

        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('usuario1', $resultado['mensaje']);
        $this->assertEquals(1, $resultado['usu_id']);
        $this->assertEquals('foto.jpg', $resultado['usu_foto']);
    }

    /**
     * @test
     */
    public function testExiste_ContraseniaIncorrecta()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $userData = [
            'usu_id' => 1,
            'usu_cedula' => '12345678',
            'usu_nombre' => 'usuario1',
            'usu_contrasenia' => $hashedPassword,
            'usu_foto' => 'foto.jpg',
            'usu_estado' => 1,
            'usu_docente' => 1
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $this->login->set_nombreUsuario('usuario1');
        $this->login->set_contraseniaUsuario('wrongpassword');

        $resultado = $this->login->existe();

        $this->assertEquals('noexiste', $resultado['resultado']);
        $this->assertStringContainsString('Verifique su usuario o contraseña', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testExiste_UsuarioNoEncontrado()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);

        $this->login->set_nombreUsuario('noexiste');
        $this->login->set_contraseniaUsuario('password123');

        $resultado = $this->login->existe();

        $this->assertEquals('noexiste', $resultado['resultado']);
        $this->assertStringContainsString('Verifique su usuario o contraseña', $resultado['mensaje']);
    }

    /**
     * @test
     */
    public function testEnviarCodigoRecuperacion_Falla_UsuarioVacio()
    {
        $resultado = $this->login->enviarCodigoRecuperacionPorUsuario('');

        $this->assertStringContainsString('usuario es requerido', $resultado);
    }

    /**
     * @test
     */
    public function testEnviarCodigoRecuperacion_Falla_UsuarioNull()
    {
        $resultado = $this->login->enviarCodigoRecuperacionPorUsuario(null);

        $this->assertStringContainsString('usuario es requerido', $resultado);
    }

    /**
     * @test
     */
    public function testValidarCodigoRecuperacion_Falla_UsuarioVacio()
    {
        $resultado = $this->login->validarCodigoRecuperacion('', 'abc123');

        $this->assertStringContainsString('usuario es requerido', $resultado);
    }

    /**
     * @test
     */
    public function testValidarCodigoRecuperacion_Falla_CodigoVacio()
    {
        $resultado = $this->login->validarCodigoRecuperacion('usuario1', '');

        $this->assertStringContainsString('código', $resultado);
    }

    /**
     * @test
     */
    public function testValidarCodigoRecuperacion_CodigoCorrecto()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        
        $futureTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $userData = [
            'reset_token' => 'abc123',
            'reset_token_expira' => $futureTime
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $resultado = $this->login->validarCodigoRecuperacion('usuario1', 'abc123');

        $this->assertEquals('ok', $resultado);
    }

    /**
     * @test
     */
    public function testValidarCodigoRecuperacion_CodigoExpirado()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        
        $pastTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $userData = [
            'reset_token' => 'abc123',
            'reset_token_expira' => $pastTime
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $resultado = $this->login->validarCodigoRecuperacion('usuario1', 'abc123');

        $this->assertStringContainsString('expirado', $resultado);
    }

    /**
     * @test
     */
    public function testValidarCodigoRecuperacion_CodigoIncorrecto()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        
        $futureTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $userData = [
            'reset_token' => 'abc123',
            'reset_token_expira' => $futureTime
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $resultado = $this->login->validarCodigoRecuperacion('usuario1', 'wrongcode');

        $this->assertStringContainsString('incorrecto', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_Falla_UsuarioVacio()
    {
        $resultado = $this->login->cambiarClaveConToken('', 'abc123', 'nuevaPassword');

        $this->assertStringContainsString('usuario es requerido', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_Falla_CodigoVacio()
    {
        $resultado = $this->login->cambiarClaveConToken('usuario1', '', 'nuevaPassword');

        $this->assertStringContainsString('código', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_Falla_ClaveVacia()
    {
        $resultado = $this->login->cambiarClaveConToken('usuario1', 'abc123', '');

        $this->assertStringContainsString('contraseña es requerida', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_Falla_ClaveMuyCorta()
    {
        $resultado = $this->login->cambiarClaveConToken('usuario1', 'abc123', '12345');

        $this->assertStringContainsString('al menos 6 caracteres', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_Exito()
    {
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtSelect, $stmtUpdate);

        $futureTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $userData = [
            'usu_id' => 1,
            'reset_token_expira' => $futureTime
        ];
        
        $stmtSelect->method('fetch')->willReturn($userData);
        $stmtUpdate->expects($this->once())->method('execute');

        $resultado = $this->login->cambiarClaveConToken('usuario1', 'abc123', 'nuevaPassword');

        $this->assertStringContainsString('actualizada correctamente', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_TokenExpirado()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $pastTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $userData = [
            'usu_id' => 1,
            'reset_token_expira' => $pastTime
        ];
        
        $this->stmtMock->method('fetch')->willReturn($userData);

        $resultado = $this->login->cambiarClaveConToken('usuario1', 'abc123', 'nuevaPassword');

        $this->assertStringContainsString('expirado', $resultado);
    }

    /**
     * @test
     */
    public function testCambiarClaveConToken_TokenIncorrecto()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);

        $resultado = $this->login->cambiarClaveConToken('usuario1', 'wrongtoken', 'nuevaPassword');

        $this->assertStringContainsString('incorrecto', $resultado);
    }

    /**
     * @test
     */
    public function testGetPermisos_Exito()
    {
        $stmtRol = $this->createMock(PDOStatement::class);
        $stmtPermisos = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtRol, $stmtPermisos);

        $stmtRol->method('fetch')->willReturn(['rol_id' => 1]);
        $stmtPermisos->method('fetch')->willReturnOnConsecutiveCalls(
            ['per_modulo' => 'usuario', 'per_accion' => 'crear'],
            ['per_modulo' => 'usuario', 'per_accion' => 'editar'],
            ['per_modulo' => 'docente', 'per_accion' => 'ver'],
            false
        );

        $resultado = $this->login->get_permisos(1);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('usuario', $resultado);
        $this->assertArrayHasKey('docente', $resultado);
        $this->assertContains('crear', $resultado['usuario']);
        $this->assertContains('editar', $resultado['usuario']);
    }

    /**
     * @test
     */
    public function testGetPermisos_UsuarioSinRol()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['rol_id' => null]);

        $resultado = $this->login->get_permisos(1);

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    /**
     * @test
     */
    public function testGetDatosUsuario_Exito()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['rol_id' => 2]);

        $resultado = $this->login->getDatosUsuario(1);

        $this->assertIsArray($resultado);
        $this->assertEquals(2, $resultado['rol_id']);
    }

    /**
     * @test
     */
    public function testValidarCaptcha_TokenValido()
    {
        $loginMock = $this->getMockBuilder(Login::class)
            ->onlyMethods(['validarCaptcha'])
            ->getMock();

        $loginMock->method('validarCaptcha')->willReturn(true);

        $resultado = $loginMock->validarCaptcha('valid_token');

        $this->assertTrue($resultado);
    }

    /**
     * @test
     */
    public function testValidarCaptcha_TokenInvalido()
    {
        $loginMock = $this->getMockBuilder(Login::class)
            ->onlyMethods(['validarCaptcha'])
            ->getMock();

        $loginMock->method('validarCaptcha')->willReturn(false);

        $resultado = $loginMock->validarCaptcha('invalid_token');

        $this->assertFalse($resultado);
    }
}
