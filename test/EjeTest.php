<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/eje.php';
class EjeTest extends TestCase
{
    private $eje;
    protected function setUp(): void
    {
        $this->eje = new Eje();
    }
    protected function tearDown(): void
    {
        $this->eje = null;
    }
    public function testSetAndGetEje()
    {
        $this->eje->setEje('Eje Profesional');
        $this->assertEquals('Eje Profesional', $this->eje->getEje());
    }
    public function testSetAndGetDescripcion()
    {
        $this->eje->setDescripcion('Descripción del eje profesional');
        $this->assertEquals('Descripción del eje profesional', $this->eje->getDescripcion());
    }
    public function testRegistrarExitoso()
    {
        $eje = new class extends Eje {
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
        $eje->setTestMode(true);
        $eje->setEje('Eje Profesional');
        $eje->setDescripcion('Descripción del eje');
        $resultado = $eje->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testRegistrarEjeYaExisteActivo()
    {
        $eje = new class extends Eje {
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
                                    return ['eje_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Existente');
        $eje->setDescripcion('Descripción');
        $resultado = $eje->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }
    public function testRegistrarReactivarEjeInactivo()
    {
        $eje = new class extends Eje {
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
                                    return ['eje_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Inactivo');
        $eje->setDescripcion('Reactivar');
        $resultado = $eje->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testModificarExitoso()
    {
        $eje = new class extends Eje {
            private $testMode = false;
            private $callCount = 0;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($ejeNombre, $ejeExcluir = null) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($ejeNombre, $ejeExcluir);
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
                        public function prepare($sql) {
                            $callCount = &$this->callCount;
                            return new class($callCount) {
                                private $callCount;
                                public function __construct(&$count) {
                                    $this->callCount = &$count;
                                }
                                public function execute() { 
                                    return true; 
                                }
                                public function fetch() {
                                    $result = ($this->callCount == 0) ? [
                                        'eje_nombre' => 'Eje Original',
                                        'eje_descripcion' => 'Descripción Original'
                                    ] : false;
                                    $this->callCount++;
                                    return $result;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Modificado');
        $eje->setDescripcion('Nueva Descripción');
        $resultado = $eje->Modificar('Eje Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testModificarEjeNoExiste()
    {
        $eje = new class extends Eje {
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
        $eje->setTestMode(true);
        $eje->setEje('Eje Nuevo');
        $eje->setDescripcion('Descripción');
        $resultado = $eje->Modificar('Eje Inexistente');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testModificarSinCambios()
    {
        $eje = new class extends Eje {
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
                                        'eje_nombre' => 'Eje Test',
                                        'eje_descripcion' => 'Descripción Test'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Test');
        $eje->setDescripcion('Descripción Test');
        $resultado = $eje->Modificar('Eje Test');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }
    public function testModificarConNombreExistente()
    {
        $eje = new class extends Eje {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($ejeNombre, $ejeExcluir = null) {
                if ($this->testMode) {
                    return ['resultado' => 'existe', 'mensaje' => 'El EJE colocado YA existe!'];
                }
                return parent::Existe($ejeNombre, $ejeExcluir);
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
                                        'eje_nombre' => 'Eje Original',
                                        'eje_descripcion' => 'Descripción'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Existente');
        $eje->setDescripcion('Descripción');
        $resultado = $eje->Modificar('Eje Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }
    public function testEliminarExitoso()
    {
        $eje = new class extends Eje {
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
                                    return ['eje_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje a Eliminar');
        $resultado = $eje->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testEliminarEjeNoExiste()
    {
        $eje = new class extends Eje {
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
        $eje->setTestMode(true);
        $eje->setEje('Eje Inexistente');
        $resultado = $eje->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testEliminarEjeYaDesactivado()
    {
        $eje = new class extends Eje {
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
                                    return ['eje_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $eje->setEje('Eje Desactivado');
        $resultado = $eje->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivado', $resultado['mensaje']);
    }
    public function testExisteEjeActivo()
    {
        $eje = new class extends Eje {
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
                                public function fetchColumn() {
                                    return 1;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $resultado = $eje->Existe('Eje Existente');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testNoExisteEje()
    {
        $eje = new class extends Eje {
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
                                public function fetchColumn() {
                                    return 0;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $resultado = $eje->Existe('Eje Inexistente');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
    public function testExisteConExclusion()
    {
        $eje = new class extends Eje {
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
                                public function fetchColumn() {
                                    return 0;
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $eje->setTestMode(true);
        $resultado = $eje->Existe('Eje Test', 'Eje Test');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}