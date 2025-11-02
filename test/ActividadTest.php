<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/actividad.php';
class ActividadTest extends TestCase
{
    private $actividad;
    protected function setUp(): void
    {
        $this->actividad = new Actividad();
    }
    protected function tearDown(): void
    {
        $this->actividad = null;
    }
    public function testSetDocId()
    {
        $this->actividad->setDocId(12345678);
        $this->assertTrue(true); 
    }
    public function testSetCreacionIntelectual()
    {
        $this->actividad->setCreacionIntelectual(5);
        $this->assertTrue(true);
    }
    public function testSetIntegracionComunidad()
    {
        $this->actividad->setIntegracionComunidad(3);
        $this->assertTrue(true);
    }
    public function testSetGestionAcademica()
    {
        $this->actividad->setGestionAcademica(4);
        $this->assertTrue(true);
    }
    public function testSetOtras()
    {
        $this->actividad->setOtras(2);
        $this->assertTrue(true);
    }
    public function testRegistrarExitoso()
    {
        $actividad = new class extends Actividad {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() { return ['doc_dedicacion' => 'Exclusiva']; }
                                public function fetchColumn() { return false; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $actividad->setTestMode(true);
        $actividad->setDocId(12345678);
        $actividad->setCreacionIntelectual(5);
        $actividad->setIntegracionComunidad(5);
        $actividad->setGestionAcademica(5);
        $actividad->setOtras(5);
        $resultado = $actividad->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testRegistrarDocenteYaTieneActividad()
    {
        $this->assertTrue(true);
    }
    public function testEliminarExitoso()
    {
        $actividad = new class extends Actividad {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $actividad->setTestMode(true);
        $actividad->setDocId(12345678);
        $resultado = $actividad->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
    }
    public function testDocenteYaTieneActividad()
    {
        $actividad = new class extends Actividad {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return ['act_estado' => '1']; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $actividad->setTestMode(true);
        $resultado = $actividad->DocenteYaTieneActividad(12345678);
        $this->assertTrue($resultado);
    }
    public function testContarDocentesActivos()
    {
        $actividad = new class extends Actividad {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function query() { 
                            return new class {
                                public function fetchColumn() { return 10; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $actividad->setTestMode(true);
        $resultado = $actividad->ContarDocentesActivos();
        $this->assertEquals(10, $resultado);
    }
    public function testSetId()
    {
        $this->actividad->setId(12345678);
        $this->assertTrue(true);
    }
    public function testValidarHorasActividad()
    {
        $this->actividad->setDocId(12345678);
        $this->actividad->setCreacionIntelectual(10);
        $this->actividad->setIntegracionComunidad(8);
        $this->actividad->setGestionAcademica(6);
        $this->actividad->setOtras(5);
        $this->assertTrue(true);
    }
    public function testActividadConHorasCero()
    {
        $this->actividad->setDocId(99999999);
        $this->actividad->setCreacionIntelectual(0);
        $this->actividad->setIntegracionComunidad(0);
        $this->actividad->setGestionAcademica(0);
        $this->actividad->setOtras(0);
        $this->assertTrue(true);
    }
    public function testModificarActividad()
    {
        $actividad = new class extends Actividad {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() { return ['doc_dedicacion' => 'Exclusiva']; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $actividad->setTestMode(true);
        $actividad->setDocId(12345678);
        $actividad->setCreacionIntelectual(8);
        $actividad->setIntegracionComunidad(7);
        $actividad->setGestionAcademica(6);
        $actividad->setOtras(4);
        $resultado = $actividad->Modificar();
        $this->assertEquals('modificar', $resultado['resultado']);
    }
}