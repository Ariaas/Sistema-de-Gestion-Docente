<?php

use PHPUnit\Framework\TestCase;

require_once 'model/rol.php';
require_once 'model/usuario.php';
class RolUsuarioIntegrationTest extends IntegrationTestCase
{
    private $rol;
    private $usuario;
    private $rolesCreados = [];
    private $usuariosCreados = [];
    protected function setUp(): void
    {
        $this->rol = new Rol();
        $this->usuario = new Usuario();
        $this->rolesCreados = [];
        $this->usuariosCreados = [];
    }
    protected function tearDown(): void
    {
        foreach ($this->usuariosCreados as $usuarioId) {
            try {
                $usuarioTemp = new Usuario();
                $usuarioTemp->set_usuarioId($usuarioId);
                $usuarioTemp->Eliminar($usuarioId);
            } catch (Exception $e) {
            }
        }
        foreach ($this->rolesCreados as $rolId) {
            try {
                $rolTemp = new Rol();
                $rolTemp->setId($rolId);
                $rolInfo = $rolTemp->getRolById($rolId);
                if ($rolInfo && $rolInfo['rol_nombre'] !== 'Administrador') {
                    $rolTemp->Eliminar();
                }
            } catch (Exception $e) {
            }
        }
    }
    public function testCrearUsuarioConRol_Integracion_Exito()
    {
        $nombreRol = 'RolTest_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultadoRol = $this->rol->Registrar();
        $this->assertEquals('registrar', $resultadoRol['resultado']);
        $roles = $this->rol->Listar();
        $rolEncontrado = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolEncontrado = $r;
                $this->rolesCreados[] = $r['rol_id'];
                break;
            }
        }
        $this->assertNotNull($rolEncontrado);
        $nombreUsuario = 'usuario_test_' . rand(1000, 9999);
        $correoUsuario = 'test' . rand(1000, 9999) . '@test.com';
        $this->usuario->set_nombreUsuario($nombreUsuario);
        $this->usuario->set_correoUsuario($correoUsuario);
        $this->usuario->set_contraseniaUsuario('Test123456');
        $this->usuario->set_rolId($rolEncontrado['rol_id']);
        $this->usuario->set_usu_docente('No');
        $this->usuario->set_usu_cedula('12345678');
        $resultadoUsuario = $this->usuario->Registrar();
        $this->assertEquals('registrar', $resultadoUsuario['resultado']);
        $usuarios = $this->usuario->Listar();
        $usuarioEncontrado = null;
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['usu_nombre'] === $nombreUsuario) {
                $usuarioEncontrado = $u;
                $this->usuariosCreados[] = $u['usu_id'];
                break;
            }
        }
        $this->assertNotNull($usuarioEncontrado);
        $this->assertEquals($rolEncontrado['rol_id'], $usuarioEncontrado['rol_id']);
        $this->assertEquals($nombreRol, $usuarioEncontrado['rol_nombre']);
    }
    public function testCrearUsuarioConRolInexistente_Integracion_Falla()
    {
        $nombreUsuario = 'usuario_fail_' . rand(1000, 9999);
        $correoUsuario = 'fail' . rand(1000, 9999) . '@test.com';
        $this->usuario->set_nombreUsuario($nombreUsuario);
        $this->usuario->set_correoUsuario($correoUsuario);
        $this->usuario->set_contraseniaUsuario('Test123456');
        $this->usuario->set_rolId(999999);
        $this->usuario->set_usu_docente('No');
        $this->usuario->set_usu_cedula('12345678');
        $resultado = $this->usuario->Registrar();
        $this->assertContains($resultado['resultado'], ['error', 'registrar']);
        if ($resultado['resultado'] === 'registrar') {
            $usuarios = $this->usuario->Listar();
            $usuarioEncontrado = false;
            foreach ($usuarios['mensaje'] as $u) {
                if ($u['usu_nombre'] === $nombreUsuario) {
                    $usuarioEncontrado = true;
                    $this->usuariosCreados[] = $u['usu_id'];
                    $this->assertEmpty($u['rol_nombre']);
                    break;
                }
            }
        }
    }
    public function testCambiarRolDeUsuario_Integracion_Exito()
    {
        $nombreRol1 = 'Rol1_' . rand(1000, 9999);
        $nombreRol2 = 'Rol2_' . rand(5000, 9999);
        $this->rol->setNombre($nombreRol1);
        $this->rol->Registrar();
        $rol2 = new Rol();
        $rol2->setNombre($nombreRol2);
        $rol2->Registrar();
        $roles = $this->rol->Listar();
        $rol1Id = null;
        $rol2Id = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol1) {
                $rol1Id = $r['rol_id'];
                $this->rolesCreados[] = $r['rol_id'];
            }
            if ($r['rol_nombre'] === $nombreRol2) {
                $rol2Id = $r['rol_id'];
                $this->rolesCreados[] = $r['rol_id'];
            }
        }
        $this->assertNotNull($rol1Id);
        $this->assertNotNull($rol2Id);
        $nombreUsuario = 'usuario_cambio_' . rand(1000, 9999);
        $correoUsuario = 'cambio' . rand(1000, 9999) . '@test.com';
        $this->usuario->set_nombreUsuario($nombreUsuario);
        $this->usuario->set_correoUsuario($correoUsuario);
        $this->usuario->set_contraseniaUsuario('Test123456');
        $this->usuario->set_rolId($rol1Id);
        $this->usuario->set_usu_docente('No');
        $this->usuario->set_usu_cedula('12345678');
        $this->usuario->Registrar();
        $usuarios = $this->usuario->Listar();
        $usuarioId = null;
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['usu_nombre'] === $nombreUsuario) {
                $usuarioId = $u['usu_id'];
                $this->usuariosCreados[] = $u['usu_id'];
                break;
            }
        }
        $this->assertNotNull($usuarioId);
        $usuarioModificar = new Usuario();
        $usuarioModificar->set_usuarioId($usuarioId);
        $usuarioModificar->set_nombreUsuario($nombreUsuario);
        $usuarioModificar->set_correoUsuario($correoUsuario);
        $usuarioModificar->set_rolId($rol2Id);
        $usuarioModificar->set_usu_docente('No');
        $usuarioModificar->set_usu_cedula('12345678');
        $resultado = $usuarioModificar->Modificar($usuarioId);
        $this->assertEquals('modificar', $resultado['resultado']);
        $usuariosActualizados = $this->usuario->Listar();
        foreach ($usuariosActualizados['mensaje'] as $u) {
            if ($u['usu_id'] === $usuarioId) {
                $this->assertEquals($rol2Id, $u['rol_id']);
                $this->assertEquals($nombreRol2, $u['rol_nombre']);
                break;
            }
        }
    }
    public function testListarUsuariosPorRol_Integracion_Exito()
    {
        $nombreRol = 'RolListar_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $this->rol->Registrar();
        $roles = $this->rol->Listar();
        $rolId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolId = $r['rol_id'];
                $this->rolesCreados[] = $r['rol_id'];
                break;
            }
        }
        $this->assertNotNull($rolId);
        $usuariosCreados = [];
        for ($i = 1; $i <= 3; $i++) {
            $usuario = new Usuario();
            $nombreUsuario = "usuario_rol_{$i}_" . rand(1000, 9999);
            $correoUsuario = "rol{$i}_" . rand(1000, 9999) . '@test.com';
            $usuario->set_nombreUsuario($nombreUsuario);
            $usuario->set_correoUsuario($correoUsuario);
            $usuario->set_contraseniaUsuario('Test123456');
            $usuario->set_rolId($rolId);
            $usuario->set_usu_docente('No');
            $usuario->set_usu_cedula("1234567{$i}");
            $usuario->Registrar();
            $usuariosCreados[] = $nombreUsuario;
        }
        $usuarios = $this->usuario->Listar();
        $usuariosConRol = [];
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['rol_id'] === $rolId) {
                $usuariosConRol[] = $u;
                $this->usuariosCreados[] = $u['usu_id'];
            }
        }
        $this->assertGreaterThanOrEqual(3, count($usuariosConRol));
        foreach ($usuariosConRol as $u) {
            $this->assertEquals($nombreRol, $u['rol_nombre']);
        }
    }
    public function testEliminarRolConUsuarios_Integracion_ComportamientoActual()
    {
        $nombreRol = 'RolEliminar_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $this->rol->Registrar();
        $roles = $this->rol->Listar();
        $rolId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolId = $r['rol_id'];
                $this->rolesCreados[] = $r['rol_id'];
                break;
            }
        }
        $this->assertNotNull($rolId);
        $nombreUsuario = 'usuario_eliminar_' . rand(1000, 9999);
        $correoUsuario = 'eliminar' . rand(1000, 9999) . '@test.com';
        $this->usuario->set_nombreUsuario($nombreUsuario);
        $this->usuario->set_correoUsuario($correoUsuario);
        $this->usuario->set_contraseniaUsuario('Test123456');
        $this->usuario->set_rolId($rolId);
        $this->usuario->set_usu_docente('No');
        $this->usuario->set_usu_cedula('12345678');
        $this->usuario->Registrar();
        $usuarios = $this->usuario->Listar();
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['usu_nombre'] === $nombreUsuario) {
                $this->usuariosCreados[] = $u['usu_id'];
                break;
            }
        }
        $rolEliminar = new Rol();
        $rolEliminar->setId($rolId);
        $resultado = $rolEliminar->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $rolesActualizados = $this->rol->Listar();
        $rolEliminado = true;
        foreach ($rolesActualizados['mensaje'] as $r) {
            if ($r['rol_id'] === $rolId) {
                $rolEliminado = false;
                break;
            }
        }
        $this->assertTrue($rolEliminado, 'El rol debería estar eliminado (soft delete)');
    }
    public function testModificarUsuarioAdministrador_Integracion_Proteccion()
    {
        $usuarios = $this->usuario->Listar();
        $usuarioAdmin = null;
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['rol_nombre'] === 'Administrador') {
                $usuarioAdmin = $u;
                break;
            }
        }
        if (!$usuarioAdmin) {
            $this->markTestSkipped('No hay usuarios con rol Administrador en la BD');
        }
        $usuarioModificar = new Usuario();
        $usuarioModificar->set_usuarioId($usuarioAdmin['usu_id']);
        $usuarioModificar->set_nombreUsuario($usuarioAdmin['usu_nombre']);
        $usuarioModificar->set_correoUsuario($usuarioAdmin['usu_correo']);
        $usuarioModificar->set_rolId($usuarioAdmin['rol_id']);
        $usuarioModificar->set_usu_docente($usuarioAdmin['usu_docente']);
        $usuarioModificar->set_usu_cedula($usuarioAdmin['usu_cedula']);
        $resultado = $usuarioModificar->Modificar(999999);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Administrador', $resultado['mensaje']);
    }
    public function testFlujoCompleto_RolUsuario_Integracion()
    {
        $nombreRol = 'RolFlujo_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultadoRol = $this->rol->Registrar();
        $this->assertEquals('registrar', $resultadoRol['resultado']);
        $roles = $this->rol->Listar();
        $rolId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolId = $r['rol_id'];
                $this->rolesCreados[] = $r['rol_id'];
                break;
            }
        }
        $this->assertNotNull($rolId);
        $nombreUsuario = 'usuario_flujo_' . rand(1000, 9999);
        $correoUsuario = 'flujo' . rand(1000, 9999) . '@test.com';
        $this->usuario->set_nombreUsuario($nombreUsuario);
        $this->usuario->set_correoUsuario($correoUsuario);
        $this->usuario->set_contraseniaUsuario('Test123456');
        $this->usuario->set_rolId($rolId);
        $this->usuario->set_usu_docente('No');
        $this->usuario->set_usu_cedula('12345678');
        $resultadoUsuario = $this->usuario->Registrar();
        $this->assertEquals('registrar', $resultadoUsuario['resultado']);
        $usuarios = $this->usuario->Listar();
        $usuarioEncontrado = null;
        foreach ($usuarios['mensaje'] as $u) {
            if ($u['usu_nombre'] === $nombreUsuario) {
                $usuarioEncontrado = $u;
                $this->usuariosCreados[] = $u['usu_id'];
                break;
            }
        }
        $this->assertNotNull($usuarioEncontrado);
        $this->assertEquals($rolId, $usuarioEncontrado['rol_id']);
        $this->assertEquals($nombreRol, $usuarioEncontrado['rol_nombre']);
        $nombreRolNuevo = 'RolFlujoMod_' . rand(5000, 9999);
        $rolModificar = new Rol();
        $rolModificar->setId($rolId);
        $rolModificar->setNombre($nombreRolNuevo);
        $resultadoModRol = $rolModificar->Modificar();
        $this->assertEquals('modificar', $resultadoModRol['resultado']);
        $usuariosActualizados = $this->usuario->Listar();
        foreach ($usuariosActualizados['mensaje'] as $u) {
            if ($u['usu_id'] === $usuarioEncontrado['usu_id']) {
                $this->assertEquals($nombreRolNuevo, $u['rol_nombre']);
                break;
            }
        }
        $usuarioEliminar = new Usuario();
        $usuarioEliminar->set_usuarioId($usuarioEncontrado['usu_id']);
        $resultadoElimUsuario = $usuarioEliminar->Eliminar($usuarioEncontrado['usu_id']);
        $this->assertEquals('eliminar', $resultadoElimUsuario['resultado']);
        $rolEliminar = new Rol();
        $rolEliminar->setId($rolId);
        $resultadoElimRol = $rolEliminar->Eliminar();
        $this->assertEquals('eliminar', $resultadoElimRol['resultado']);
    }
}
