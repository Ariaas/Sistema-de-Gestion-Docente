<?php
use PHPUnit\Framework\TestCase;
require_once 'model/titulo.php';
class TituloTest extends TestCase
{
 private $pdoMock;
 private $titulo;
 protected function setUp(): void
 {
 $this->pdoMock = $this->createMock(PDO::class);
 $this->titulo = $this->getMockBuilder(Titulo::class)
  ->disableOriginalConstructor()
  ->onlyMethods(['Con'])
  ->getMock();
 $this->titulo->method('Con')->willReturn($this->pdoMock);
 $this->pdoMock->method('setAttribute')->willReturn(true);
 }
 public function testGettersAndSetters()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        $this->assertEquals('Ing.', $this->titulo->get_prefijo());
        $this->assertEquals('Sistemas', $this->titulo->get_nombreTitulo());
    }
    public function testConsultarDevuelveDatosCorrectamente()
    {
        $datosEsperados = [
            ['tit_prefijo' => 'Ing.', 'tit_nombre' => 'Sistemas'],
            ['tit_prefijo' => 'Lic.', 'tit_nombre' => 'Letras'],
            ['tit_prefijo' => 'Dr.', 'tit_nombre' => 'Ciencias']
        ];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn($datosEsperados);
        $this->pdoMock->method('query')->willReturn($stmt);
        $resultado = $this->titulo->Consultar();
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }
    public function testConsultarManejaExcepciones()
    {
        $exception = new PDOException("Error de consulta SQL");
        $this->pdoMock->method('query')->will($this->throwException($exception));
        $resultado = $this->titulo->Consultar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de consulta SQL', $resultado['mensaje']);
    }
    public function testExisteDevuelveTrueSiExiste()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(['1']); 
        $this->pdoMock->method('prepare')->willReturn($stmt);
        $resultado = $this->titulo->Existe();
        $this->assertTrue($resultado);
    }
    public function testExisteDevuelveFalseSiNoExiste()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false); 
        $this->pdoMock->method('prepare')->willReturn($stmt);
        $resultado = $this->titulo->Existe();
        $this->assertFalse($resultado);
    }
    public function testExisteDevuelveFalseEnModoModificarCuandoDatosNoCambian()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        $this->titulo->set_original_prefijo('Ing.'); 
        $this->titulo->set_original_nombre('Sistemas'); 
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false); 
        $this->pdoMock->method('prepare')->willReturn($stmt);
        $resultado = $this->titulo->Existe();
        $this->assertFalse($resultado);
    }
    public function testRegistrarConNombreVacioFalla()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(''); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false); 
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
    }
    public function testRegistrarConPrefijoVacioFalla()
    {
        $this->titulo->set_prefijo(''); 
        $this->titulo->set_nombreTitulo('Un Nombre Valido');
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false); 
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
    }
    public function testModificarAUnNombreVacioFalla()
    {
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(''); 
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); 
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $exception->errorInfo[1] = null; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);
        $resultado = $this->titulo->Modificar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringNotContainsString('utilizado por uno o más docentes', $resultado['mensaje']);
    }
    public function testRegistrarConEspaciosEnBlancoCircundantes()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(' En Sistemas '); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testModificarSoloPrefijoExitoso()
    {
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Dr.'); 
        $this->titulo->set_nombreTitulo('En Sistemas'); 
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); 
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);
        $resultado = $this->titulo->Modificar();
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
    }
    public function testRegistrarConTipoDeDatoIncorrectoFalla()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(['Esto es un array']); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException("Error en bindParam, tipo incorrecto");
        $stmtInsert->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bindParam, tipo incorrecto', $resultado['mensaje']);
    }
    public function testRegistrarTituloConDiferenteCaseEsExitoso()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('sistemas');
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testConsultarDevuelveArrayVacioCuandoNoHayTitulos()
    {
        $datosEsperados = [];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn($datosEsperados); 
        $this->pdoMock->method('query')->willReturn($stmt);
        $resultado = $this->titulo->Consultar();
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']); 
    }
    public function testExisteDevuelveTrueErroneamenteCuandoLaConsultaFalla()
    {
        $this->titulo->set_prefijo('Error');
        $this->titulo->set_nombreTitulo('Fatal');
        $exception = new PDOException("Error de SQL");
        $this->pdoMock->method('prepare')->will($this->throwException($exception));
        $resultado = $this->titulo->Existe();
        $this->assertTrue($resultado);
    }
    public function testModificarManejaExcepcionGenerica()
    {
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Informática');
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); 
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $exception = new PDOException("Tabla 'tbl_titulo' no existe");
        $exception->errorInfo[0] = '42S02'; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);
        $resultado = $this->titulo->Modificar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals("Tabla 'tbl_titulo' no existe", $resultado['mensaje']);
    }
    public function testRegistrarConLongitudMinimaExacta()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Cinco'); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testRegistrarConLongitudMaximaExacta()
    {
        $nombreLargo = str_repeat('a', 80); 
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo($nombreLargo); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testRegistrarConNombreDemasiadoCortoFalla()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Abc'); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
    }
    public function testRegistrarConComillasSimplesEsExitoso()
    {
        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo("Doctor 'Honoris Causa'"); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testRegistrarConCaracterUTF8ExtensoFalla()
    {
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Ingeniería Aeroespacial 🚀'); 
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '22007'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );
        $resultado = $this->titulo->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
    }
    public function testModificarSinCambiosFunciona()
    {
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Sistemas');
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtUpdate);
        $resultado = $this->titulo->Modificar();
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
    }
    public function testModificarTituloEnUsoGeneraErrorDeConstraint()
    {
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Informática'); 
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); 
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);
        $resultado = $this->titulo->Modificar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('utilizado por uno o más docentes', $resultado['mensaje']);
    }
 public function testRegistrarTituloNuevoExitoso()
 {
  $this->titulo->set_prefijo('Ing.');
 $this->titulo->set_nombreTitulo('En Sistemas');
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
 $stmtExisteInactivo = $this->createMock(PDOStatement::class);
 $stmtExisteInactivo->method('fetchColumn')->willReturn(false); 
 $stmtInsert = $this->createMock(PDOStatement::class);
 $stmtInsert->method('execute')->willReturn(true); 
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls(
  $stmtExisteActivo,   
  $stmtExisteInactivo, 
  $stmtInsert          
  );
 $resultado = $this->titulo->Registrar();
 $this->assertEquals('registrar', $resultado['resultado']);
 $this->assertStringContainsString('correctamente', $resultado['mensaje']);
 }
 public function testRegistrarTituloCuandoYaExisteActivo()
 {
 $this->titulo->set_prefijo('Lic.');
 $this->titulo->set_nombreTitulo('En Contaduría');
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(true); 
 $this->pdoMock->method('prepare')->willReturn($stmtExisteActivo);
 $resultado = $this->titulo->Registrar();
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El título colocado ya existe', $resultado['mensaje']);
 }
 public function testRegistrarTituloCuandoExisteInactivoDebeReactivar()
 {
 $this->titulo->set_prefijo('TSU.');
 $this->titulo->set_nombreTitulo('En Informática');
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(false); 
 $stmtExisteInactivo = $this->createMock(PDOStatement::class);
 $stmtExisteInactivo->method('fetchColumn')->willReturn(true); 
 $stmtReactivar = $this->createMock(PDOStatement::class);
 $stmtReactivar->method('execute')->willReturn(true); 
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls(
  $stmtExisteActivo,   
  $stmtExisteInactivo, 
  $stmtReactivar         
  );
 $resultado = $this->titulo->Registrar();
 $this->assertEquals('registrar', $resultado['resultado']);
 $this->assertStringContainsString('Se registró el título correctamente', $resultado['mensaje']);
 }
 public function testModificarTituloExitoso()
 {
 $this->titulo->set_original_prefijo('Ing.');
 $this->titulo->set_original_nombre('En Sistemas');
 $this->titulo->set_prefijo('Ing.');
 $this->titulo->set_nombreTitulo('En Informática'); 
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(false); 
 $stmtDelete = $this->createMock(PDOStatement::class); 
 $stmtDelete->method('execute')->willReturn(true);
 $stmtUpdate = $this->createMock(PDOStatement::class); 
 $stmtUpdate->method('execute')->willReturn(true);
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);
 $resultado = $this->titulo->Modificar();
 $this->assertEquals('modificar', $resultado['resultado']);
 $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
 }
 public function testModificarTituloAUnNombreQueYaExiste()
 {
 $this->titulo->set_original_prefijo('Ing.');
 $this->titulo->set_original_nombre('En Sistemas');
 $this->titulo->set_prefijo('Lic.'); 
 $this->titulo->set_nombreTitulo('En Educación'); 
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); 
 $this->pdoMock->method('prepare')->willReturn($stmtExiste);
 $resultado = $this->titulo->Modificar();
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El titulo colocado ya existe', $resultado['mensaje']);
 }
 public function testEliminarTituloExitoso()
 {
 $this->titulo->set_prefijo('Dr.');
 $this->titulo->set_nombreTitulo('En Ciencias');
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); 
 $stmtUpdate = $this->createMock(PDOStatement::class);
 $stmtUpdate->method('execute')->willReturn(true); 
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);
 $resultado = $this->titulo->Eliminar();
 $this->assertEquals('eliminar', $resultado['resultado']);
 $this->assertStringContainsString('Se eliminó el título correctamente', $resultado['mensaje']);
 }
 public function testEliminarTituloQueNoExiste()
 {
 $this->titulo->set_prefijo('Msc.');
 $this->titulo->set_nombreTitulo('En Gerencia');
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(false); 
 $this->pdoMock->method('prepare')->willReturn($stmtExiste);
 $resultado = $this->titulo->Eliminar();
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El título que intenta eliminar no existe', $resultado['mensaje']);
 }
 public function testEliminarTituloEnUsoGeneraErrorDeConstraint()
 {
 $this->titulo->set_prefijo('Esp.');
 $this->titulo->set_nombreTitulo('En Redes');
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); 
 $stmtUpdate = $this->createMock(PDOStatement::class);
 $exception = new PDOException();
 $exception->errorInfo[0] = '23000'; 
 $stmtUpdate->method('execute')->will($this->throwException($exception));
 $this->pdoMock->method('prepare')
->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);
 $resultado = $this->titulo->Eliminar();
 $this->assertEquals('error', $resultado['resultado']);
$this->assertStringContainsString('utilizado por uno o más docentes', $resultado['mensaje']); }
}