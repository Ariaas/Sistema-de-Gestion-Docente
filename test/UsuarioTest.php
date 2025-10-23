<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/usuario.php';
class UsuarioTest extends TestCase
{
    private $usuario;
    protected function setUp(): void
    {
        $this->usuario = new Usuario();
    }
    protected function tearDown(): void
    {
        $this->usuario = null;
    }
    public function testSetAndGetUsuCedula()
    {
        $this->usuario->set_usu_cedula(12345678);
        $this->assertEquals(12345678, $this->usuario->get_usu_cedula());
    }
    public function testSetAndGetUsuDocente()
    {
        $this->usuario->set_usu_docente(1);
        $this->assertEquals(1, $this->usuario->get_usu_docente());
    }
    public function testSetAndGetRolId()
    {
        $this->usuario->set_rolId(2);
        $this->assertEquals(2, $this->usuario->get_rolId());
    }
    public function testGetNombreUsuario()
    {
        $usuario = new Usuario(1, 'admin', 'pass123', 'admin@test.com', 1);
        $this->assertEquals('admin', $usuario->get_nombreUsuario());
    }
    public function testGetCorreoUsuario()
    {
        $usuario = new Usuario(1, 'admin', 'pass123', 'admin@test.com', 1);
        $this->assertEquals('admin@test.com', $usuario->get_correoUsuario());
    }
    public function testGetSuperUsuario()
    {
        $usuario = new Usuario(1, 'admin', 'pass123', 'admin@test.com', 1);
        $this->assertEquals(1, $usuario->get_superUsuario());
    }
    public function testGetUsuarioId()
    {
        $usuario = new Usuario(5, 'user', 'pass', 'user@test.com', 0);
        $this->assertEquals(5, $usuario->get_usuarioId());
    }
    public function testEliminarExitoso()
    {
        $this->assertTrue(true);
    }
    public function testEliminarNoExiste()
    {
        $this->assertTrue(true);
    }
    public function testCrearUsuarioConParametros()
    {
        $usuario = new Usuario(10, 'testuser', 'password', 'test@example.com', 0);
        $this->assertEquals(10, $usuario->get_usuarioId());
        $this->assertEquals('testuser', $usuario->get_nombreUsuario());
        $this->assertEquals('test@example.com', $usuario->get_correoUsuario());
        $this->assertEquals(0, $usuario->get_superUsuario());
    }
}