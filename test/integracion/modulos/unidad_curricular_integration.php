<?php
require_once 'vendor/autoload.php';
require_once 'IntegrationTestCase.php'; 
require_once 'Mock_ValidacionSelect.php'; 
require_once 'Mock_Bitacora.php'; 

use PHPUnit\Framework\TestCase;
use App\Model\UC; 
use App\Model\ValidacionSelect; 
use App\Model\Bitacora; 
use PDO;
use Exception;

class UCIntegrationTest extends IntegrationTestCase
{
    private $datosCreados = [];

    
    private function crearDependencias()
    {
        if (empty($this->datosCreados['ejes'])) {
            $nombreEje = 'EJE-TEST-' . rand(1000, 9999);
            self::$pdo->prepare("INSERT INTO tbl_eje (eje_nombre, eje_descripcion, eje_estado) VALUES (?, 'Test', 1)")
                      ->execute([$nombreEje]);
            $this->datosCreados['ejes'][] = $nombreEje;
        }

        if (empty($this->datosCreados['areas'])) {
            $nombreArea = 'AREA-TEST-' . rand(1000, 9999);
            self::$pdo->prepare("INSERT INTO tbl_area (area_nombre, area_descripcion, area_estado) VALUES (?, 'Test', 1)")
                      ->execute([$nombreArea]);
            $this->datosCreados['areas'][] = $nombreArea;
        }
    }

    
    private function crearUCValida($codigo, $nombre)
    {
        $this->crearDependencias();
        $uc = new UC();
        $uc->setcodigoUC($codigo);
        $uc->setnombreUC($nombre);
        $uc->setcreditosUC(4);
        $uc->settrayectoUC('1');
        $uc->setperiodoUC('Fase I');
        $uc->setejeUC($this->datosCreados['ejes'][0]);
        $uc->setareaUC($this->datosCreados['areas'][0]);
        return $uc;
    }

    protected function setUp(): void
    {
        global $integrationTestPdo;
        $integrationTestPdo = self::$pdo;
        
        $this->datosCreados = [
            'ucs' => [],
            'ejes' => [],
            'areas' => [],
            'horarios' => []
        ];
    }

    protected function tearDown(): void
    {
        
        foreach ($this->datosCreados['horarios'] as $codigo) {
             try { self::$pdo->prepare("DELETE FROM uc_horario WHERE uc_codigo = ?")->execute([$codigo]); } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['ucs'] as $codigo) {
             try { self::$pdo->prepare("DELETE FROM tbl_uc WHERE uc_codigo = ?")->execute([$codigo]); } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['ejes'] as $nombre) {
             try { self::$pdo->prepare("DELETE FROM tbl_eje WHERE eje_nombre = ?")->execute([$nombre]); } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['areas'] as $nombre) {
             try { self::$pdo->prepare("DELETE FROM tbl_area WHERE area_nombre = ?")->execute([$nombre]); } catch (Exception $e) {}
         }
         
        global $integrationTestPdo;
        $integrationTestPdo = null;
    }

    

    public function testRegistrar_ConDatosValidos_Exito()
    {
        $uc = $this->crearUCValida('UC-TEST-01', 'UC de Prueba');
        $this->datosCreados['ucs'][] = 'UC-TEST-01'; 

        $resultado = $uc->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
    }

    public function testRegistrar_CodigoExistenteActivo_Error()
    {
        
        $uc1 = $this->crearUCValida('UC-TEST-02', 'UC 1');
        $this->datosCreados['ucs'][] = 'UC-TEST-02';
        $uc1->Registrar();

        
        $uc2 = $this->crearUCValida('UC-TEST-02', 'UC 2');
        $resultado = $uc2->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ERROR!', $resultado['mensaje']);
    }

    public function testRegistrar_ConCodigoExistenteDesactivado_DebeReactivar()
    {
        
        $codigo = 'UC-TEST-03';
        $uc1 = $this->crearUCValida($codigo, 'UC Original Desactivada');
        $this->datosCreados['ucs'][] = $codigo;
        $uc1->Registrar();
        self::$pdo->prepare("UPDATE tbl_uc SET uc_estado = 0 WHERE uc_codigo = ?")->execute([$codigo]);

        
        $uc2 = $this->crearUCValida($codigo, 'UC Reactivada y Modificada');
        $resultado = $uc2->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $stmt = self::$pdo->prepare("SELECT uc_nombre, uc_estado FROM tbl_uc WHERE uc_codigo = ?");
        $stmt->execute([$codigo]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $dbData['uc_estado']);
        $this->assertEquals('UC Reactivada y Modificada', $dbData['uc_nombre']);
    }

    public function testRegistrar_ConNombreVacio_Error()
    {
        $uc = $this->crearUCValida('UC-TEST-04', ''); 
        $resultado = $uc->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nombre de la UC no puede estar vacío', $resultado['mensaje']);
    }

    public function testRegistrar_ConEjeInvalido_Error()
    {
        $uc = $this->crearUCValida('UC-TEST-05', 'UC Eje Malo');
        $uc->setejeUC('EJE-QUE-NO-EXISTE'); 
        $resultado = $uc->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('El valor de Eje no es válido', $resultado['mensaje']);
    }

    public function testModificar_ConDatosValidos_Exito()
    {
        
        $codigo = 'UC-TEST-06';
        $uc1 = $this->crearUCValida($codigo, 'Nombre Original');
        $this->datosCreados['ucs'][] = $codigo;
        $uc1->Registrar();

        
        $uc2 = $this->crearUCValida($codigo, 'Nombre Modificado');
        $resultado = $uc2->Modificar($codigo);

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Modificado', $resultado['mensaje']);
        
        
        $stmt = self::$pdo->prepare("SELECT uc_nombre FROM tbl_uc WHERE uc_codigo = ?");
        $stmt->execute([$codigo]);
        $this->assertEquals('Nombre Modificado', $stmt->fetchColumn());
    }

    public function testModificar_SinCambios_DevuelveMensajeNoCambios()
    {
        
        $codigo = 'UC-TEST-07';
        $uc1 = $this->crearUCValida($codigo, 'Nombre Sin Cambios');
        $this->datosCreados['ucs'][] = $codigo;
        $uc1->Registrar();

        
        $uc2 = $this->crearUCValida($codigo, 'Nombre Sin Cambios');
        $resultado = $uc2->Modificar($codigo);

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }

    public function testEliminar_SoftDelete_Exito()
    {
        
        $codigo = 'UC-TEST-08';
        $uc = $this->crearUCValida($codigo, 'UC a Eliminar');
        $this->datosCreados['ucs'][] = $codigo;
        $uc->Registrar();

        
        $uc->Eliminar();

        
        $stmt = self::$pdo->prepare("SELECT uc_estado FROM tbl_uc WHERE uc_codigo = ?");
        $stmt->execute([$codigo]);
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    public function testActivar_Exito(){
    
        $codigo = 'UC-TEST-09';
        $uc = $this->crearUCValida($codigo, 'UC a Activar');
        $this->datosCreados['ucs'][] = $codigo;
        $uc->Registrar();
        $uc->Eliminar();

        $uc->Activar();
        
        $stmt = self::$pdo->prepare("SELECT uc_estado FROM tbl_uc WHERE uc_codigo = ?");
        $stmt->execute([$codigo]);
        $this->assertEquals(1, $stmt->fetchColumn());
    }

    public function testVerificarEnHorario_ConDependencia(){
        
        $codigo = 'UC-TEST-10';
        $uc = $this->crearUCValida($codigo, 'UC en Horario');
        $this->datosCreados['ucs'][] = $codigo;
        $uc->Registrar();

        
        self::$pdo->prepare("INSERT INTO uc_horario (uc_codigo, id_horario) VALUES (?, 1)")
                  ->execute([$codigo]); 
        $this->datosCreados['horarios'][] = $codigo;

        
        $resultado = $uc->verificarEnHorario($codigo);
        $this->assertEquals('en_horario', $resultado['resultado']);
    }

    public function testVerificarEnHorario_SinDependencia(){
        $codigo = 'UC-TEST-11';
        $uc = $this->crearUCValida($codigo, 'UC Sin Horario');
        $this->datosCreados['ucs'][] = $codigo;
        $uc->Registrar();

        $resultado = $uc->verificarEnHorario($codigo);
        $this->assertEquals('no_en_horario', $resultado['resultado']);
    }
}