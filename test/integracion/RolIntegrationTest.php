<?php

use PHPUnit\Framework\TestCase;

require_once 'model/rol.php';

class RolIntegrationTest extends IntegrationTestCase
{
    private $rol;
    private $rolesCreados = [];

    protected function setUp(): void
    {
        $this->rol = new Rol();
        $this->rolesCreados = [];
    }

    protected function tearDown(): void
    {
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

    public function testRegistrar_Integracion_Exito()
    {
        $nombreRol = 'Test_' . rand(1000, 9999);

        $this->rol->setNombre($nombreRol);
        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);

        $verificacion = $this->rol->Existe($nombreRol);
        $this->assertEquals('existe', $verificacion['resultado']);
    }

    public function testRegistrar_Integracion_Falla_RolDuplicado()
    {
        $nombreRol = 'Dup_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultado1 = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado1['resultado']);

        $rol2 = new Rol();
        $rol2->setNombre($nombreRol);
        $resultado2 = $rol2->Registrar();

        $this->assertEquals('error', $resultado2['resultado']);
        $this->assertStringContainsString('YA existe', $resultado2['mensaje']);
    }

    public function testModificar_Integracion_Exito()
    {
        $nombreOriginal = 'Orig_' . rand(1000, 9999);
        $this->rol->setNombre($nombreOriginal);
        $resultadoRegistro = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultadoRegistro['resultado']);

        $verificacion = $this->rol->Existe($nombreOriginal);
        $this->assertEquals('existe', $verificacion['resultado']);

        $roles = $this->rol->Listar();
        $rolEncontrado = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreOriginal) {
                $rolEncontrado = $r;
                break;
            }
        }

        $this->assertNotNull($rolEncontrado, 'El rol debería existir en la BD');

        $nombreNuevo = 'Mod_' . rand(5000, 9999);
        $rolModificar = new Rol();
        $rolModificar->setId($rolEncontrado['rol_id']);
        $rolModificar->setNombre($nombreNuevo);
        $resultadoModificar = $rolModificar->Modificar();

        $this->assertEquals('modificar', $resultadoModificar['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultadoModificar['mensaje']);

        $rolInfo = $rolModificar->getRolById($rolEncontrado['rol_id']);
        $this->assertEquals($nombreNuevo, $rolInfo['rol_nombre']);
    }

    public function testModificar_Integracion_Falla_RolAdministrador()
    {
        $roles = $this->rol->Listar();
        $adminId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === 'Administrador') {
                $adminId = $r['rol_id'];
                break;
            }
        }

        if ($adminId) {
            $rolAdmin = new Rol();
            $rolAdmin->setId($adminId);
            $rolAdmin->setNombre('Administrador');
            $resultado = $rolAdmin->Modificar();

            $this->assertEquals('error', $resultado['resultado']);
            $this->assertStringContainsString('Administrador', $resultado['mensaje']);
            $this->assertStringContainsString('no puede ser modificado', $resultado['mensaje']);
        } else {
            $this->markTestSkipped('No existe el rol Administrador en la BD');
        }
    }


    public function testEliminar_Integracion_Exito()
    {
        $nombreRol = 'Del_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultadoRegistro = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultadoRegistro['resultado']);

        $roles = $this->rol->Listar();
        $rolEncontrado = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolEncontrado = $r;
                break;
            }
        }

        $this->assertNotNull($rolEncontrado);

        $rolEliminar = new Rol();
        $rolEliminar->setId($rolEncontrado['rol_id']);
        $resultadoEliminar = $rolEliminar->Eliminar();

        $this->assertEquals('eliminar', $resultadoEliminar['resultado']);
        $this->assertStringContainsString('Registro Eliminado', $resultadoEliminar['mensaje']);

        $rolesActualizados = $this->rol->Listar();
        $rolEliminadoEnLista = false;
        foreach ($rolesActualizados['mensaje'] as $r) {
            if ($r['rol_id'] === $rolEncontrado['rol_id']) {
                $rolEliminadoEnLista = true;
                break;
            }
        }

        $this->assertFalse($rolEliminadoEnLista, 'El rol eliminado no debería aparecer en el listado');
    }

    public function testEliminar_Integracion_Falla_RolAdministrador()
    {
        $roles = $this->rol->Listar();
        $adminId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === 'Administrador') {
                $adminId = $r['rol_id'];
                break;
            }
        }

        if ($adminId) {
            $rolEliminar = new Rol();
            $rolEliminar->setId($adminId);
            $resultado = $rolEliminar->Eliminar();

            $this->assertEquals('error', $resultado['resultado']);
            $this->assertStringContainsString('Administrador', $resultado['mensaje']);
            $this->assertStringContainsString('no puede ser eliminado', $resultado['mensaje']);
        } else {
            $this->markTestSkipped('No existe el rol Administrador en la BD');
        }
    }


    public function testListar_Integracion_Exito()
    {
        $resultado = $this->rol->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        if (count($resultado['mensaje']) > 0) {
            $primerRol = $resultado['mensaje'][0];
            $this->assertArrayHasKey('rol_id', $primerRol);
            $this->assertArrayHasKey('rol_nombre', $primerRol);
        }
    }

    public function testAsignarPermisos_Integracion_Exito()
    {
        $nombreRol = 'Perm_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultadoRegistro = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultadoRegistro['resultado']);

        $roles = $this->rol->Listar();
        $rolEncontrado = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolEncontrado = $r;
                break;
            }
        }

        $this->assertNotNull($rolEncontrado);

        $permisosDisponibles = $this->rol->listarPermisos($rolEncontrado['rol_id']);

        if (count($permisosDisponibles['modulosDisponibles']) > 0) {
            $permisosAsignar = [
                [
                    'per_id' => $permisosDisponibles['modulosDisponibles'][0]['per_id'],
                    'per_accion' => 'consultar'
                ]
            ];

            $resultado = $this->rol->asignarPermisos($rolEncontrado['rol_id'], $permisosAsignar);

            $this->assertEquals('ok', $resultado['resultado']);
            $this->assertStringContainsString('Permisos asignados correctamente', $resultado['mensaje']);

            $permisosAsignados = $this->rol->listarPermisos($rolEncontrado['rol_id']);
            $this->assertGreaterThan(0, count($permisosAsignados['permisosAsignados']));
        } else {
            $this->markTestSkipped('No hay permisos disponibles en la BD');
        }
    }

    public function testAsignarPermisos_Integracion_Falla_RolAdministrador()
    {
        $roles = $this->rol->Listar();
        $adminId = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === 'Administrador') {
                $adminId = $r['rol_id'];
                break;
            }
        }

        if ($adminId) {
            $resultado = $this->rol->asignarPermisos($adminId, []);

            $this->assertEquals('error', $resultado['resultado']);
            $this->assertStringContainsString('Administrador', $resultado['mensaje']);
            $this->assertStringContainsString('no se pueden modificar', $resultado['mensaje']);
        } else {
            $this->markTestSkipped('No existe el rol Administrador en la BD');
        }
    }


    public function testGetRolById_Integracion_Exito()
    {
        $roles = $this->rol->Listar();

        if (count($roles['mensaje']) > 0) {
            $primerRol = $roles['mensaje'][0];

            $resultado = $this->rol->getRolById($primerRol['rol_id']);

            $this->assertNotNull($resultado);
            $this->assertArrayHasKey('rol_nombre', $resultado);
            $this->assertEquals($primerRol['rol_nombre'], $resultado['rol_nombre']);
        } else {
            $this->markTestSkipped('No hay roles en la BD');
        }
    }

    public function testGetRolById_Integracion_RolNoExiste()
    {
        $idInexistente = 999999;

        $resultado = $this->rol->getRolById($idInexistente);

        $this->assertFalse($resultado);
    }


    public function testFlujoCompleto_Integracion_CRUD()
    {
        $nombreRol = 'CRUD_' . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultadoCrear = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultadoCrear['resultado']);

        $verificacion = $this->rol->Existe($nombreRol);
        $this->assertEquals('existe', $verificacion['resultado']);

        $roles = $this->rol->Listar();
        $rolEncontrado = null;
        foreach ($roles['mensaje'] as $r) {
            if ($r['rol_nombre'] === $nombreRol) {
                $rolEncontrado = $r;
                break;
            }
        }
        $this->assertNotNull($rolEncontrado);

        $nombreNuevo = 'CRUD_UPD_' . rand(5000, 9999);
        $rolModificar = new Rol();
        $rolModificar->setId($rolEncontrado['rol_id']);
        $rolModificar->setNombre($nombreNuevo);
        $resultadoModificar = $rolModificar->Modificar();

        $this->assertEquals('modificar', $resultadoModificar['resultado']);

        $rolInfo = $rolModificar->getRolById($rolEncontrado['rol_id']);
        $this->assertEquals($nombreNuevo, $rolInfo['rol_nombre']);

        $rolEliminar = new Rol();
        $rolEliminar->setId($rolEncontrado['rol_id']);
        $resultadoEliminar = $rolEliminar->Eliminar();

        $this->assertEquals('eliminar', $resultadoEliminar['resultado']);

        $rolesActualizados = $this->rol->Listar();
        $rolEliminadoEnLista = false;
        foreach ($rolesActualizados['mensaje'] as $r) {
            if ($r['rol_id'] === $rolEncontrado['rol_id']) {
                $rolEliminadoEnLista = true;
                break;
            }
        }
        $this->assertFalse($rolEliminadoEnLista);
    }

    public function testValidacion_Integracion_NombreConCaracteresEspeciales()
    {
        $nombreRol = "Test_Ñ_" . rand(1000, 9999);
        $this->rol->setNombre($nombreRol);
        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);

        $verificacion = $this->rol->Existe($nombreRol);
        $this->assertEquals('existe', $verificacion['resultado']);
    }

    public function testValidacion_Integracion_NombreLargo()
    {
        $nombreRol = 'Long_' . str_repeat('T', 15) . rand(100, 999);
        $this->rol->setNombre($nombreRol);
        $resultado = $this->rol->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
    }
}
