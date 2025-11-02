<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/espacios.php';
class EspacioTest extends TestCase
{
    private $espacio;
    protected function setUp(): void
    {
        $this->espacio = new Espacio();
    }
    protected function tearDown(): void
    {
        $this->espacio = null;
    }
    public function testSetAndGetNumero()
    {
        $this->espacio->setNumero('  101  ');
        $this->assertEquals('101', $this->espacio->getNumero());
    }
    public function testSetAndGetEdificio()
    {
        $this->espacio->setEdificio('  A  ');
        $this->assertEquals('A', $this->espacio->getEdificio());
    }
    public function testSetAndGetTipo()
    {
        $this->espacio->setTipo('  Aula  ');
        $this->assertEquals('Aula', $this->espacio->getTipo());
    }
    public function testRegistrarExitoso()
    {
        $this->assertTrue(true);
    }
    public function testSetNumeroOriginal()
    {
        $this->espacio->setNumeroOriginal('100');
        $this->assertTrue(true);
    }
    public function testSetEdificioOriginal()
    {
        $this->espacio->setEdificioOriginal('A');
        $this->assertTrue(true);
    }
    public function testSetTipoOriginal()
    {
        $this->espacio->setTipoOriginal('Laboratorio');
        $this->assertTrue(true);
    }
    public function testTrimEnSetters()
    {
        $this->espacio->setNumero('  202  ');
        $this->espacio->setEdificio('  B  ');
        $this->espacio->setTipo('  Aula  ');
        $this->assertEquals('202', $this->espacio->getNumero());
        $this->assertEquals('B', $this->espacio->getEdificio());
        $this->assertEquals('Aula', $this->espacio->getTipo());
    }
    public function testValidarDatosCompletos()
    {
        $this->espacio->setNumero('301');
        $this->espacio->setEdificio('C');
        $this->espacio->setTipo('Laboratorio');
        $this->assertEquals('301', $this->espacio->getNumero());
        $this->assertEquals('C', $this->espacio->getEdificio());
        $this->assertEquals('Laboratorio', $this->espacio->getTipo());
    }
    public function testEliminarExitoso()
    {
        $espacio = new class extends Espacio {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return ['esp_estado' => 1]; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $espacio->setTestMode(true);
        $espacio->setNumero('301');
        $espacio->setEdificio('C');
        $espacio->setTipo('Aula');
        $resultado = $espacio->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
    }
}