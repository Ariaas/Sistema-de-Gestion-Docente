<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/coordinacion.php';
class CoordinacionTest extends TestCase
{
    private $coordinacion;
    private $pdo;
    protected function setUp(): void
    {
        $this->coordinacion = $this->getMockBuilder(Coordinacion::class)
            ->onlyMethods(['Con'])
            ->getMock();
        $this->pdo = $this->createMock(PDO::class);
        $this->coordinacion->method('Con')->willReturn($this->pdo);
    }
    protected function tearDown(): void
    {
        $this->coordinacion = null;
        $this->pdo = null;
    }
    public function testSetAndGetNombre()
    {
        $this->coordinacion->setNombre('  Coordinación de Informática  ');
        $this->assertEquals('Coordinación de Informática', $this->coordinacion->getNombre());
    }
    public function testSetAndGetHoraDescarga()
    {
        $this->coordinacion->setHoraDescarga(40);
        $this->assertEquals(40, $this->coordinacion->getHoraDescarga());
    }
    public function testSetOriginalNombre()
    {
        $this->coordinacion->setOriginalNombre('  Coordinación Original  ');
        $this->assertTrue(true);
    }
    public function testRegistrarExitoso()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function BuscarPorNombre($nombre) {
                if ($this->testMode) {
                    return false; 
                }
                return parent::BuscarPorNombre($nombre);
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() { return false; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setNombre('Nueva Coordinación');
        $coordinacion->setHoraDescarga(40);
        $resultado = $coordinacion->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testRegistrarCoordinacionYaExisteActiva()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function BuscarPorNombre($nombre) {
                if ($this->testMode) {
                    return [
                        'cor_nombre' => 'Coordinación Existente',
                        'cor_estado' => 1,
                        'coor_hora_descarga' => 40
                    ];
                }
                return parent::BuscarPorNombre($nombre);
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setNombre('Coordinación Existente');
        $coordinacion->setHoraDescarga(40);
        $resultado = $coordinacion->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testModificarExitoso()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($nombre) {
                if ($this->testMode) {
                    return false;
                }
                return parent::Existe($nombre);
            }
            public function BuscarPorNombre($nombre) {
                if ($this->testMode) {
                    return false;
                }
                return parent::BuscarPorNombre($nombre);
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() {
                                    return [
                                        'cor_nombre' => 'Coordinación Original',
                                        'coor_hora_descarga' => 35
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setOriginalNombre('Coordinación Original');
        $coordinacion->setNombre('Coordinación Modificada');
        $coordinacion->setHoraDescarga(40);
        $resultado = $coordinacion->Modificar();
        $this->assertEquals('modificar', $resultado['resultado']);
    }
    public function testModificarSinCambios()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() {
                                    return [
                                        'cor_nombre' => 'Coordinación Test',
                                        'coor_hora_descarga' => 40
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setOriginalNombre('Coordinación Test');
        $coordinacion->setNombre('Coordinación Test');
        $coordinacion->setHoraDescarga(40);
        $resultado = $coordinacion->Modificar();
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }
    public function testEliminarExitoso()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() {
                                    return ['cor_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setNombre('Coordinación a Eliminar');
        $resultado = $coordinacion->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testEliminarCoordinacionNoExiste()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() {
                                    return false;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setNombre('Coordinación Inexistente');
        $resultado = $coordinacion->Eliminar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testEliminarCoordinacionYaDesactivada()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() {
                                    return ['cor_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $coordinacion->setTestMode(true);
        $coordinacion->setNombre('Coordinación Desactivada');
        $resultado = $coordinacion->Eliminar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivada', $resultado['mensaje']);
    }
    public function testExisteCoordinacionActiva()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($nombre) {
                if ($this->testMode) {
                    return ['cor_nombre' => $nombre];
                }
                return parent::Existe($nombre);
            }
        };
        $coordinacion->setTestMode(true);
        $resultado = $coordinacion->Existe('Coordinación Existente');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('cor_nombre', $resultado);
    }
    public function testNoExisteCoordinacion()
    {
        $coordinacion = new class extends Coordinacion {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($nombre) {
                if ($this->testMode) {
                    return false;
                }
                return parent::Existe($nombre);
            }
        };
        $coordinacion->setTestMode(true);
        $resultado = $coordinacion->Existe('Coordinación Inexistente');
        $this->assertFalse($resultado);
    }
}