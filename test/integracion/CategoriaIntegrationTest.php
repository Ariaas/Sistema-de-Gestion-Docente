<?php
use PHPUnit\Framework\TestCase;
require_once 'model/categoria.php';
class CategoriaIntegrationTest extends TestCase
{
    private $categoria;
    private $categoriasCreadas = [];
    protected function setUp(): void
    {
        $this->categoria = new Categoria();
        $this->categoriasCreadas = [];
    }
    protected function tearDown(): void
    {
        foreach ($this->categoriasCreadas as $categoriaNombre) {
            try {
                $categoriaTemp = new Categoria();
                $categoriaTemp->setCategoria($categoriaNombre);
                $categoriaTemp->Eliminar();
            } catch (Exception $e) {
            }
        }
    }
    public function testRegistrar_Integracion_Exito()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Descripción de prueba');
        $resultado = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $verificacion = $this->categoria->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
    }
    public function testRegistrar_Integracion_Falla_CategoriaDuplicada()
    {
        $nombreCategoria = 'Cat_Dup_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Primera descripción');
        $resultado1 = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultado1['resultado']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $categoria2 = new Categoria();
        $categoria2->setCategoria($nombreCategoria);
        $categoria2->setDescripcion('Segunda descripción');
        $resultado2 = $categoria2->Registrar();
        $this->assertEquals('registrar', $resultado2['resultado']);
        $this->assertStringContainsString('ya existe', $resultado2['mensaje']);
    }
    public function testRegistrar_Integracion_ReactivarCategoriaEliminada()
    {
        $nombreCategoria = 'Cat_React_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Descripción original');
        $this->categoria->Registrar();
        $this->categoria->Eliminar();
        $categoriaReactivar = new Categoria();
        $categoriaReactivar->setCategoria($nombreCategoria);
        $categoriaReactivar->setDescripcion('Descripción actualizada');
        $resultado = $categoriaReactivar->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $verificacion = $categoriaReactivar->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
    }
    public function testModificar_Integracion_Exito()
    {
        $nombreOriginal = 'Cat_Orig_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreOriginal);
        $this->categoria->setDescripcion('Descripción original');
        $resultadoRegistro = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultadoRegistro['resultado']);
        $this->categoriasCreadas[] = $nombreOriginal;
        $nombreNuevo = 'Cat_Mod_' . rand(5000, 9999);
        $categoriaModificar = new Categoria();
        $categoriaModificar->setCategoria($nombreNuevo);
        $categoriaModificar->setDescripcion('Descripción modificada');
        $resultadoModificar = $categoriaModificar->Modificar($nombreOriginal);
        $this->assertEquals('modificar', $resultadoModificar['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultadoModificar['mensaje']);
        $this->categoriasCreadas[] = $nombreNuevo;
        $verificacionNuevo = $categoriaModificar->Existe($nombreNuevo);
        $this->assertEquals('existe', $verificacionNuevo['resultado']);
        $verificacionOriginal = $categoriaModificar->Existe($nombreOriginal);
        $this->assertEmpty($verificacionOriginal);
    }
    public function testModificar_Integracion_SoloDescripcion()
    {
        $nombreCategoria = 'Cat_Desc_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Descripción original');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $nombreCategoria;
        $categoriaModificar = new Categoria();
        $categoriaModificar->setCategoria($nombreCategoria);
        $categoriaModificar->setDescripcion('Descripción actualizada');
        $resultado = $categoriaModificar->Modificar($nombreCategoria);
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultado['mensaje']);
        $verificacion = $categoriaModificar->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
    }
    public function testModificar_Integracion_SinCambios()
    {
        $nombreCategoria = 'Cat_NoChange_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Descripción sin cambios');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $nombreCategoria;
        $categoriaModificar = new Categoria();
        $categoriaModificar->setCategoria($nombreCategoria);
        $categoriaModificar->setDescripcion('Descripción sin cambios');
        $resultado = $categoriaModificar->Modificar($nombreCategoria);
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }
    public function testModificar_Integracion_Falla_CategoriaNoExiste()
    {
        $nombreInexistente = 'Cat_NoExist_' . rand(1000, 9999);
        $this->categoria->setCategoria('Cat_Nueva_' . rand(1000, 9999));
        $this->categoria->setDescripcion('Nueva descripción');
        $resultado = $this->categoria->Modificar($nombreInexistente);
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testModificar_Integracion_Falla_NombreDuplicado()
    {
        $categoria1 = 'Cat_Dup1_' . rand(1000, 9999);
        $categoria2 = 'Cat_Dup2_' . rand(1000, 9999);
        $this->categoria->setCategoria($categoria1);
        $this->categoria->setDescripcion('Categoría 1');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $categoria1;
        $cat2 = new Categoria();
        $cat2->setCategoria($categoria2);
        $cat2->setDescripcion('Categoría 2');
        $cat2->Registrar();
        $this->categoriasCreadas[] = $categoria2;
        $categoriaModificar = new Categoria();
        $categoriaModificar->setCategoria($categoria1);
        $categoriaModificar->setDescripcion('Intento de duplicar');
        $resultado = $categoriaModificar->Modificar($categoria2);
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }
    public function testEliminar_Integracion_Exito()
    {
        $nombreCategoria = 'Cat_Del_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría para eliminar');
        $resultadoRegistro = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultadoRegistro['resultado']);
        $categoriaEliminar = new Categoria();
        $categoriaEliminar->setCategoria($nombreCategoria);
        $resultadoEliminar = $categoriaEliminar->Eliminar();
        $this->assertEquals('eliminar', $resultadoEliminar['resultado']);
        $this->assertStringContainsString('Registro Eliminado', $resultadoEliminar['mensaje']);
        $verificacion = $categoriaEliminar->Existe($nombreCategoria);
        $this->assertEmpty($verificacion);
    }
    public function testEliminar_Integracion_Falla_CategoriaNoExiste()
    {
        $nombreInexistente = 'Cat_NoExist_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreInexistente);
        $resultado = $this->categoria->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testEliminar_Integracion_Falla_YaEliminada()
    {
        $nombreCategoria = 'Cat_DelTwice_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría para eliminar dos veces');
        $this->categoria->Registrar();
        $this->categoria->Eliminar();
        $categoriaEliminar = new Categoria();
        $categoriaEliminar->setCategoria($nombreCategoria);
        $resultado = $categoriaEliminar->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivada', $resultado['mensaje']);
    }
    public function testListar_Integracion_Exito()
    {
        $resultado = $this->categoria->Listar();
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);
        if (count($resultado['mensaje']) > 0) {
            $primeraCategoria = $resultado['mensaje'][0];
            $this->assertArrayHasKey('cat_nombre', $primeraCategoria);
            $this->assertArrayHasKey('cat_descripcion', $primeraCategoria);
            $this->assertArrayHasKey('cat_estado', $primeraCategoria);
        }
    }
    public function testListar_Integracion_IncluyeCategoriasActivas()
    {
        $nombreCategoria = 'Cat_List_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría en listado');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $nombreCategoria;
        $resultado = $this->categoria->Listar();
        $encontrada = false;
        foreach ($resultado['mensaje'] as $cat) {
            if ($cat['cat_nombre'] === $nombreCategoria && $cat['cat_estado'] == 1) {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada, 'La categoría creada debe aparecer en el listado');
    }
    public function testListar_Integracion_IncluyeCategoriasInactivas()
    {
        $nombreCategoria = 'Cat_Inactive_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría inactiva');
        $this->categoria->Registrar();
        $this->categoria->Eliminar();
        $resultado = $this->categoria->Listar();
        $encontrada = false;
        foreach ($resultado['mensaje'] as $cat) {
            if ($cat['cat_nombre'] === $nombreCategoria && $cat['cat_estado'] == 0) {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada, 'La categoría eliminada debe aparecer en el listado con estado 0');
    }
    public function testExiste_Integracion_CategoriaExiste()
    {
        $nombreCategoria = 'Cat_Exist_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría existente');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $nombreCategoria;
        $resultado = $this->categoria->Existe($nombreCategoria);
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }
    public function testExiste_Integracion_CategoriaNoExiste()
    {
        $nombreInexistente = 'Cat_NotExist_' . rand(1000, 9999);
        $resultado = $this->categoria->Existe($nombreInexistente);
        $this->assertEmpty($resultado);
    }
    public function testExiste_Integracion_ConExclusion()
    {
        $categoria1 = 'Cat_Excl1_' . rand(1000, 9999);
        $categoria2 = 'Cat_Excl2_' . rand(1000, 9999);
        $this->categoria->setCategoria($categoria1);
        $this->categoria->setDescripcion('Categoría 1');
        $this->categoria->Registrar();
        $this->categoriasCreadas[] = $categoria1;
        $cat2 = new Categoria();
        $cat2->setCategoria($categoria2);
        $cat2->setDescripcion('Categoría 2');
        $cat2->Registrar();
        $this->categoriasCreadas[] = $categoria2;
        $resultado = $this->categoria->Existe($categoria1, $categoria2);
        $this->assertEquals('existe', $resultado['resultado']);
        $resultado2 = $this->categoria->Existe($categoria1, $categoria1);
        $this->assertEmpty($resultado2);
    }
    public function testFlujoCompleto_Integracion_CRUD()
    {
        $nombreCategoria = 'Cat_CRUD_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría para flujo CRUD');
        $resultadoCrear = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultadoCrear['resultado']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $verificacion = $this->categoria->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
        $listado = $this->categoria->Listar();
        $encontrada = false;
        foreach ($listado['mensaje'] as $cat) {
            if ($cat['cat_nombre'] === $nombreCategoria) {
                $encontrada = true;
                $this->assertEquals('Categoría para flujo CRUD', $cat['cat_descripcion']);
                break;
            }
        }
        $this->assertTrue($encontrada);
        $nombreNuevo = 'Cat_CRUD_UPD_' . rand(5000, 9999);
        $categoriaModificar = new Categoria();
        $categoriaModificar->setCategoria($nombreNuevo);
        $categoriaModificar->setDescripcion('Categoría modificada en flujo CRUD');
        $resultadoModificar = $categoriaModificar->Modificar($nombreCategoria);
        $this->assertEquals('modificar', $resultadoModificar['resultado']);
        $this->categoriasCreadas[] = $nombreNuevo;
        $verificacionNuevo = $categoriaModificar->Existe($nombreNuevo);
        $this->assertEquals('existe', $verificacionNuevo['resultado']);
        $categoriaEliminar = new Categoria();
        $categoriaEliminar->setCategoria($nombreNuevo);
        $resultadoEliminar = $categoriaEliminar->Eliminar();
        $this->assertEquals('eliminar', $resultadoEliminar['resultado']);
        $verificacionEliminado = $categoriaEliminar->Existe($nombreNuevo);
        $this->assertEmpty($verificacionEliminado);
    }
    public function testValidacion_Integracion_NombreConEspacios()
    {
        $nombreCategoria = 'Cat Espacios ' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría con espacios');
        $resultado = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $verificacion = $this->categoria->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
    }
    public function testValidacion_Integracion_DescripcionLarga()
    {
        $nombreCategoria = 'Cat_Long_' . rand(1000, 9999);
        $descripcionLarga = str_repeat('Texto largo ', 20);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion($descripcionLarga);
        $resultado = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->categoriasCreadas[] = $nombreCategoria;
    }
    public function testValidacion_Integracion_CaracteresEspeciales()
    {
        $nombreCategoria = 'Cat_Ñ_Ü_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría con ñ, ü, á, é, í, ó, ú');
        $resultado = $this->categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->categoriasCreadas[] = $nombreCategoria;
        $verificacion = $this->categoria->Existe($nombreCategoria);
        $this->assertEquals('existe', $verificacion['resultado']);
    }
}