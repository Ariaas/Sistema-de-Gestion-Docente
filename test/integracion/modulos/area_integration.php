<?php
require_once 'vendor/autoload.php';
require_once 'IntegrationTestCase.php'; 

use PHPUnit\Framework\TestCase;
use App\Model\Area; 
use PDO;
use Exception;

class AreaIntegrationTest extends IntegrationTestCase
{
    private $datosCreados = [];

    protected function setUp(): void
    {
        global $integrationTestPdo;
        $integrationTestPdo = self::$pdo;
        $this->datosCreados = ['areas' => []];
    }

    protected function tearDown(): void
    {
        
        foreach ($this->datosCreados['areas'] as $nombre) {
             try { 
                 self::$pdo->prepare("DELETE FROM tbl_area WHERE area_nombre = ?")
                           ->execute([$nombre]); 
             } catch (Exception $e) {}
         }
        global $integrationTestPdo;
        $integrationTestPdo = null;
    }

    public function testRegistrar_ConDatosValidos_Exito()
    {
        $nombre = 'Área de Prueba 1';
        $this->datosCreados['areas'][] = $nombre; 
        
        $area = new Area($nombre, 'Descripción de prueba');
        $resultado = $area->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
    }

    public function testRegistrar_NombreExistenteActivo_Error()
    {
        
        $nombre = 'Área Duplicada Activa';
        $this->datosCreados['areas'][] = $nombre;
        $area1 = new Area($nombre, 'Original');
        $area1->Registrar();

        
        $area2 = new Area($nombre, 'Duplicado');
        $resultado = $area2->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ERROR! <br/> El Área ya existe!', $resultado['mensaje']);
    }

    public function testRegistrar_NombreExistenteInactivo_Reactivar()
    {
       
        $nombre = 'Área Reactivada';
        $this->datosCreados['areas'][] = $nombre;
        $area1 = new Area($nombre, 'Descripción Antigua');
        $area1->Registrar();
        self::$pdo->prepare("UPDATE tbl_area SET area_estado = 0 WHERE area_nombre = ?")
                  ->execute([$nombre]);

        
        $area2 = new Area($nombre, 'Descripción Nueva Reactivada');
        $resultado = $area2->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);

        
        $stmt = self::$pdo->prepare("SELECT area_descripcion, area_estado FROM tbl_area WHERE area_nombre = ?");
        $stmt->execute([$nombre]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $dbData['area_estado']);
        $this->assertEquals('Descripción Nueva Reactivada', $dbData['area_descripcion']);
    }

    public function testRegistrar_NombreCorto_Error()
    {
        $area = new Area('A', 'Desc'); 
        $resultado = $area->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('debe tener al menos 3 caracteres', $resultado['mensaje']);
    }

    public function testModificar_ConDatosValidos_Exito()
    {
        
        $nombreOriginal = 'Área Original';
        $nombreNuevo = 'Área Modificada';
        $this->datosCreados['areas'][] = $nombreOriginal;
        $this->datosCreados['areas'][] = $nombreNuevo;
        
        $area = new Area($nombreOriginal, 'Desc Original');
        $area->Registrar();

        
        $area->setArea($nombreNuevo);
        $area->setDescripcion('Desc Modificada');
        $resultado = $area->Modificar($nombreOriginal);

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultado['mensaje']);


        $stmt = self::$pdo->prepare("SELECT area_descripcion FROM tbl_area WHERE area_nombre = ?");
        $stmt->execute([$nombreNuevo]);
        $this->assertEquals('Desc Modificada', $stmt->fetchColumn());
    }

    public function testModificar_NombreExistente_Error()
    {
       
        $nombre1 = 'Área M-1';
        $nombre2 = 'Área M-2';
        $this->datosCreados['areas'][] = $nombre1;
        $this->datosCreados['areas'][] = $nombre2;

        $area1 = new Area($nombre1, 'Desc 1');
        $area1->Registrar();
        $area2 = new Area($nombre2, 'Desc 2');
        $area2->Registrar();

        
        $area1->setArea($nombre2); 
        $resultado = $area1->Modificar($nombre1); 

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('ERROR! <br/> El ÁREA ya existe!', $resultado['mensaje']);
    }

    public function testEliminar_SoftDelete_Exito()
    {
        
        $nombre = 'Área a Eliminar';
        $this->datosCreados['areas'][] = $nombre;
        $area = new Area($nombre, 'Desc');
        $area->Registrar();

        
        $resultado = $area->Eliminar();
        
        $this->assertEquals('eliminar', $resultado['resultado']);
        
        
        $stmt = self::$pdo->prepare("SELECT area_estado FROM tbl_area WHERE area_nombre = ?");
        $stmt->execute([$nombre]);
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    public function testListar_SoloMuestraActivos()
    {
        
        $nombreActivo = 'Área Activa';
        $nombreInactivo = 'Área Inactiva';
        $this->datosCreados['areas'][] = $nombreActivo;
        $this->datosCreados['areas'][] = $nombreInactivo;
        
        $area1 = new Area($nombreActivo, 'Desc 1');
        $area1->Registrar();
        $area2 = new Area($nombreInactivo, 'Desc 2');
        $area2->Registrar();

        
        $area2->Eliminar();

        
        $resultado = (new Area())->Listar();
        $data = $resultado['mensaje'];

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertCount(1, $data);
        $this->assertEquals($nombreActivo, $data[0]['area_nombre']);
    }
}