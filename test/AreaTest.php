<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/area.php';
class AreaTest extends TestCase
{
    private $area;
    protected function setUp(): void
    {
        $this->area = new Area();
    }
    protected function tearDown(): void
    {
        $this->area = null;
    }
    public function testSetAndGetArea()
    {
        $this->area->setArea('Área de Formación');
        $this->assertEquals('Área de Formación', $this->area->getArea());
    }
    public function testSetAndGetDescripcion()
    {
        $this->area->setDescripcion('Descripción del área');
        $this->assertEquals('Descripción del área', $this->area->getDescripcion());
    }
    public function testRegistrarExitoso()
    {
        $area = new class extends Area {
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
                                public function bindParam() { return true; }
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
        $area->setTestMode(true);
        $area->setArea('Área Nueva');
        $area->setDescripcion('Descripción del área');
        $resultado = $area->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testRegistrarAreaYaExisteActiva()
    {
        $area = new class extends Area {
            private $testMode = false;
            private $callCount = 0;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    $callCount = &$this->callCount;
                    return new class($callCount) {
                        private $callCount;
                        public function __construct(&$count) {
                            $this->callCount = &$count;
                        }
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            $callCount = &$this->callCount;
                            return new class($callCount) {
                                private $callCount;
                                public function __construct(&$count) {
                                    $this->callCount = &$count;
                                }
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() {
                                    if ($this->callCount == 0) {
                                        $this->callCount++;
                                        return ['area_estado' => 1];
                                    }
                                    return false;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $area->setTestMode(true);
        $area->setArea('Área Existente');
        $area->setDescripcion('Descripción');
        $resultado = $area->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testRegistrarReactivarAreaInactiva()
    {
        $area = new class extends Area {
            private $testMode = false;
            private $callCount = 0;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    $callCount = &$this->callCount;
                    return new class($callCount) {
                        private $callCount;
                        public function __construct(&$count) {
                            $this->callCount = &$count;
                        }
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            $callCount = &$this->callCount;
                            return new class($callCount) {
                                private $callCount;
                                public function __construct(&$count) {
                                    $this->callCount = &$count;
                                }
                                public function execute() { return true; }
                                public function bindParam() { return true; }
                                public function fetch() {
                                    if ($this->callCount == 0) {
                                        $this->callCount++;
                                        return false;
                                    }
                                    if ($this->callCount == 1) {
                                        $this->callCount++;
                                        return ['area_estado' => 0];
                                    }
                                    return false;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $area->setTestMode(true);
        $area->setArea('Área Inactiva');
        $area->setDescripcion('Reactivar');
        $resultado = $area->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testModificarExitoso()
    {
        $area = new class extends Area {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($areaNombre, $areaExcluir = NULL) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($areaNombre, $areaExcluir);
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
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
        $area->setTestMode(true);
        $area->setArea('Área Modificada');
        $area->setDescripcion('Nueva Descripción');
        $resultado = $area->Modificar('Área Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testModificarConNombreExistente()
    {
        $area = new class extends Area {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($areaNombre, $areaExcluir = NULL) {
                if ($this->testMode) {
                    return ['resultado' => 'existe', 'mensaje' => 'El Área ya existe!'];
                }
                return parent::Existe($areaNombre, $areaExcluir);
            }
        };
        $area->setTestMode(true);
        $area->setArea('Área Existente');
        $area->setDescripcion('Descripción');
        $resultado = $area->Modificar('Área Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testEliminarExitoso()
    {
        $area = new class extends Area {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($areaNombre, $areaExcluir = NULL) {
                if ($this->testMode) {
                    return ['resultado' => 'existe'];
                }
                return parent::Existe($areaNombre, $areaExcluir);
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
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
        $area->setTestMode(true);
        $area->setArea('Área a Eliminar');
        $resultado = $area->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testEliminarAreaNoExiste()
    {
        $area = new class extends Area {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($areaNombre, $areaExcluir = NULL) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($areaNombre, $areaExcluir);
            }
        };
        $area->setTestMode(true);
        $area->setArea('Área Inexistente');
        $resultado = $area->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testExisteAreaActiva()
    {
        $area = new class extends Area {
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
                                public function bindParam() { return true; }
                                public function fetchAll() {
                                    return [['area_nombre' => 'Área Existente']];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $area->setTestMode(true);
        $resultado = $area->Existe('Área Existente');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testNoExisteArea()
    {
        $area = new class extends Area {
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
                                public function bindParam() { return true; }
                                public function fetchAll() {
                                    return [];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $area->setTestMode(true);
        $resultado = $area->Existe('Área Inexistente');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
    public function testExisteConExclusion()
    {
        $area = new class extends Area {
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
                                public function bindParam() { return true; }
                                public function fetchAll() {
                                    return [];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $area->setTestMode(true);
        $resultado = $area->Existe('Área Test', 'Área Test');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}