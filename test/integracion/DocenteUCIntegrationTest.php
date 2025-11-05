<?php

use PHPUnit\Framework\TestCase;

require_once 'model/docente.php';
require_once 'model/uc.php';
require_once 'model/categoria.php';
require_once 'model/area.php';
require_once 'model/eje.php';

class DocenteUCIntegrationTest extends IntegrationTestCase
{
    private $docente;
    private $uc;
    private $categoria;
    private $area;
    private $eje;
    private $datosCreados = [];

    protected function setUp(): void
    {
        $this->docente = new Docente();
        $this->uc = new UC();
        $this->categoria = new Categoria();
        $this->area = new Area();
        $this->eje = new Eje();
        $this->datosCreados = [
            'docentes' => [],
            'ucs' => [],
            'categorias' => [],
            'areas' => [],
            'ejes' => []
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->datosCreados['docentes'] as $cedula) {
            try {
                $docTemp = new Docente();
                $docTemp->setCedula($cedula);
                $docTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['ucs'] as $codigo) {
            try {
                $ucTemp = new UC();
                $ucTemp->setcodigoUC($codigo);
                $ucTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['categorias'] as $nombre) {
            try {
                $catTemp = new Categoria();
                $catTemp->setCategoria($nombre);
                $catTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['areas'] as $nombre) {
            try {
                $areaTemp = new Area();
                $areaTemp->setArea($nombre);
                $areaTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['ejes'] as $nombre) {
            try {
                $ejeTemp = new Eje();
                $ejeTemp->setEje($nombre);
                $ejeTemp->Eliminar();
            } catch (Exception $e) {
            }
        }
    }

    public function testRegistrarDocente_ConCategoria_Exito()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría de prueba');
        $this->categoria->Registrar();
        $this->datosCreados['categorias'][] = $nombreCategoria;

        $cedula = rand(10000000, 29999999);
        $this->docente->setCedula($cedula);
        $this->docente->setNombre('Juan');
        $this->docente->setApellido('Pérez');
        $this->docente->setCorreo('juan.perez' . $cedula . '@test.com');
        $this->docente->setCategoriaNombre($nombreCategoria);
        $this->docente->setPrefijo('Prof.');
        $this->docente->setDedicacion('Tiempo Completo');
        $this->docente->setCondicion('Ordinario');
        $this->docente->setIngreso('2020-01-01');
        $this->docente->setHorasAcademicas(40);
        $this->docente->setCreacionIntelectual(0);
        $this->docente->setIntegracionComunidad(0);
        $this->docente->setGestionAcademica(0);
        $this->docente->setOtras(0);

        $resultado = $this->docente->Registrar();

        $this->assertEquals('incluir', $resultado['resultado']);
        $this->datosCreados['docentes'][] = $cedula;
    }

    public function testRegistrarDocente_CedulaDuplicada_Error()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría de prueba');
        $this->categoria->Registrar();
        $this->datosCreados['categorias'][] = $nombreCategoria;

        $cedula = rand(10000000, 29999999);
        $this->docente->setCedula($cedula);
        $this->docente->setNombre('Juan');
        $this->docente->setApellido('Pérez');
        $this->docente->setCorreo('juan.perez' . $cedula . '@test.com');
        $this->docente->setCategoriaNombre($nombreCategoria);
        $this->docente->setPrefijo('Prof.');
        $this->docente->setDedicacion('Tiempo Completo');
        $this->docente->setCondicion('Ordinario');
        $this->docente->setIngreso('2020-01-01');
        $this->docente->setHorasAcademicas(40);
        $this->docente->setCreacionIntelectual(0);
        $this->docente->setIntegracionComunidad(0);
        $this->docente->setGestionAcademica(0);
        $this->docente->setOtras(0);

        $this->docente->Registrar();
        $this->datosCreados['docentes'][] = $cedula;

        $docente2 = new Docente();
        $docente2->setCedula($cedula);
        $docente2->setNombre('Pedro');
        $docente2->setApellido('González');
        $docente2->setCorreo('pedro.gonzalez' . $cedula . '@test.com');
        $docente2->setCategoriaNombre($nombreCategoria);
        $docente2->setPrefijo('Prof.');
        $docente2->setDedicacion('Medio Tiempo');
        $docente2->setCondicion('Contratado');
        $docente2->setIngreso('2021-01-01');
        $docente2->setHorasAcademicas(20);
        $docente2->setCreacionIntelectual(0);
        $docente2->setIntegracionComunidad(0);
        $docente2->setGestionAcademica(0);
        $docente2->setOtras(0);

        $resultado2 = $docente2->Registrar();

        $this->assertEquals('error', $resultado2['resultado']);
        $this->assertStringContainsString('ya se encuentra registrada', $resultado2['mensaje']);
    }

    public function testModificarDocente_CambiarDatos_Exito()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría de prueba');
        $this->categoria->Registrar();
        $this->datosCreados['categorias'][] = $nombreCategoria;

        $cedula = rand(10000000, 29999999);
        $this->docente->setCedula($cedula);
        $this->docente->setNombre('Juan');
        $this->docente->setApellido('Pérez');
        $this->docente->setCorreo('juan.perez' . $cedula . '@test.com');
        $this->docente->setCategoriaNombre($nombreCategoria);
        $this->docente->setPrefijo('Prof.');
        $this->docente->setDedicacion('Tiempo Completo');
        $this->docente->setCondicion('Ordinario');
        $this->docente->setIngreso('2020-01-01');
        $this->docente->setHorasAcademicas(40);
        $this->docente->setCreacionIntelectual(0);
        $this->docente->setIntegracionComunidad(0);
        $this->docente->setGestionAcademica(0);
        $this->docente->setOtras(0);
        $this->docente->Registrar();
        $this->datosCreados['docentes'][] = $cedula;

        $docenteMod = new Docente();
        $docenteMod->setCedula($cedula);
        $docenteMod->setNombre('Juan Carlos');
        $docenteMod->setApellido('Pérez López');
        $docenteMod->setCorreo('juan.perez' . $cedula . '@test.com');
        $docenteMod->setCategoriaNombre($nombreCategoria);
        $docenteMod->setPrefijo('Dr.');
        $docenteMod->setDedicacion('Tiempo Completo');
        $docenteMod->setCondicion('Ordinario');
        $docenteMod->setIngreso('2020-01-01');
        $docenteMod->setHorasAcademicas(40);
        $docenteMod->setCreacionIntelectual(0);
        $docenteMod->setIntegracionComunidad(0);
        $docenteMod->setGestionAcademica(0);
        $docenteMod->setOtras(0);

        $resultado = $docenteMod->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    public function testRegistrarUC_ConAreaYEje_Exito()
    {
        $nombreArea = 'Area_Test_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área de prueba');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Test_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje de prueba');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        $codigoUC = 'UC' . rand(1000, 9999);
        $this->uc->setcodigoUC($codigoUC);
        $this->uc->setnombreUC('Unidad Curricular Test');
        $this->uc->setcreditosUC(4);
        $this->uc->settrayectoUC(1);
        $this->uc->setperiodoUC('Fase I');
        $this->uc->setejeUC($nombreEje);
        $this->uc->setareaUC($nombreArea);

        $resultado = $this->uc->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->datosCreados['ucs'][] = $codigoUC;
    }

    public function testRegistrarUC_CodigoDuplicado_Error()
    {
        $nombreArea = 'Area_Test_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área de prueba');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Test_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje de prueba');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        $codigoUC = 'UC' . rand(1000, 9999);
        $this->uc->setcodigoUC($codigoUC);
        $this->uc->setnombreUC('Unidad Curricular Test 1');
        $this->uc->setcreditosUC(4);
        $this->uc->settrayectoUC(1);
        $this->uc->setperiodoUC('Fase I');
        $this->uc->setejeUC($nombreEje);
        $this->uc->setareaUC($nombreArea);
        $this->uc->Registrar();
        $this->datosCreados['ucs'][] = $codigoUC;

        $uc2 = new UC();
        $uc2->setcodigoUC($codigoUC);
        $uc2->setnombreUC('Unidad Curricular Test 2');
        $uc2->setcreditosUC(3);
        $uc2->settrayectoUC(2);
        $uc2->setperiodoUC('Fase II');
        $uc2->setejeUC($nombreEje);
        $uc2->setareaUC($nombreArea);

        $resultado2 = $uc2->Registrar();

        $this->assertEquals('registrar', $resultado2['resultado']);
        $this->assertStringContainsString('ya existe', $resultado2['mensaje']);
    }

    public function testModificarUC_CambiarNombre_Exito()
    {
        $nombreArea = 'Area_Test_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área de prueba');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Test_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje de prueba');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        $codigoUC = 'UC' . rand(1000, 9999);
        $this->uc->setcodigoUC($codigoUC);
        $this->uc->setnombreUC('Unidad Curricular Original');
        $this->uc->setcreditosUC(4);
        $this->uc->settrayectoUC(1);
        $this->uc->setperiodoUC('Fase I');
        $this->uc->setejeUC($nombreEje);
        $this->uc->setareaUC($nombreArea);
        $this->uc->Registrar();
        $this->datosCreados['ucs'][] = $codigoUC;

        $ucMod = new UC();
        $ucMod->setcodigoUC($codigoUC);
        $ucMod->setnombreUC('Unidad Curricular Modificada');
        $ucMod->setcreditosUC(5);
        $ucMod->settrayectoUC(1);
        $ucMod->setperiodoUC('Fase I');
        $ucMod->setejeUC($nombreEje);
        $ucMod->setareaUC($nombreArea);

        $resultado = $ucMod->Modificar($codigoUC);

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    public function testListarDocentes_ConDocentes_Exito()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría de prueba');
        $this->categoria->Registrar();
        $this->datosCreados['categorias'][] = $nombreCategoria;

        $cedula = rand(10000000, 29999999);
        $this->docente->setCedula($cedula);
        $this->docente->setNombre('María');
        $this->docente->setApellido('García');
        $this->docente->setCorreo('maria.garcia' . $cedula . '@test.com');
        $this->docente->setCategoriaNombre($nombreCategoria);
        $this->docente->setPrefijo('Profa.');
        $this->docente->setDedicacion('Tiempo Completo');
        $this->docente->setCondicion('Ordinario');
        $this->docente->setIngreso('2020-01-01');
        $this->docente->setHorasAcademicas(40);
        $this->docente->setCreacionIntelectual(0);
        $this->docente->setIntegracionComunidad(0);
        $this->docente->setGestionAcademica(0);
        $this->docente->setOtras(0);
        $this->docente->Registrar();
        $this->datosCreados['docentes'][] = $cedula;

        $resultado = $this->docente->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        $encontrado = false;
        foreach ($resultado['mensaje'] as $doc) {
            if ($doc['doc_cedula'] == $cedula) {
                $encontrado = true;
                $this->assertEquals('María', $doc['doc_nombre']);
                $this->assertEquals('García', $doc['doc_apellido']);
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testListarUC_ConUCs_Exito()
    {
        $nombreArea = 'Area_Test_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área de prueba');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Test_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje de prueba');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        $codigoUC = 'UC' . rand(1000, 9999);
        $this->uc->setcodigoUC($codigoUC);
        $this->uc->setnombreUC('Matemática I');
        $this->uc->setcreditosUC(4);
        $this->uc->settrayectoUC(1);
        $this->uc->setperiodoUC('Fase I');
        $this->uc->setejeUC($nombreEje);
        $this->uc->setareaUC($nombreArea);
        $this->uc->Registrar();
        $this->datosCreados['ucs'][] = $codigoUC;

        $resultado = $this->uc->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        $encontrada = false;
        foreach ($resultado['mensaje'] as $uc) {
            if ($uc['uc_codigo'] === $codigoUC) {
                $encontrada = true;
                $this->assertEquals('Matemática I', $uc['uc_nombre']);
                break;
            }
        }
        $this->assertTrue($encontrada);
    }

    public function testExisteDocente_DocenteRegistrado_RetornaTrue()
    {
        $nombreCategoria = 'Cat_Test_' . rand(1000, 9999);
        $this->categoria->setCategoria($nombreCategoria);
        $this->categoria->setDescripcion('Categoría de prueba');
        $this->categoria->Registrar();
        $this->datosCreados['categorias'][] = $nombreCategoria;

        $cedula = rand(10000000, 29999999);
        $this->docente->setCedula($cedula);
        $this->docente->setNombre('Carlos');
        $this->docente->setApellido('Rodríguez');
        $this->docente->setCorreo('carlos.rodriguez' . $cedula . '@test.com');
        $this->docente->setCategoriaNombre($nombreCategoria);
        $this->docente->setPrefijo('Prof.');
        $this->docente->setDedicacion('Tiempo Completo');
        $this->docente->setCondicion('Ordinario');
        $this->docente->setIngreso('2020-01-01');
        $this->docente->setHorasAcademicas(40);
        $this->docente->setCreacionIntelectual(0);
        $this->docente->setIntegracionComunidad(0);
        $this->docente->setGestionAcademica(0);
        $this->docente->setOtras(0);
        $this->docente->Registrar();
        $this->datosCreados['docentes'][] = $cedula;

        $existe = $this->docente->Existe($cedula);

        $this->assertTrue($existe);
    }

    public function testExisteUC_UCRegistrada_RetornaExiste()
    {
        $nombreArea = 'Area_Test_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área de prueba');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Test_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje de prueba');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        $codigoUC = 'UC' . rand(1000, 9999);
        $this->uc->setcodigoUC($codigoUC);
        $this->uc->setnombreUC('Física I');
        $this->uc->setcreditosUC(4);
        $this->uc->settrayectoUC(1);
        $this->uc->setperiodoUC('Fase I');
        $this->uc->setejeUC($nombreEje);
        $this->uc->setareaUC($nombreArea);
        $this->uc->Registrar();
        $this->datosCreados['ucs'][] = $codigoUC;

        $resultado = $this->uc->Existe($codigoUC);

        $this->assertEquals('existe', $resultado['resultado']);
    }
}
