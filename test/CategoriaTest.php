<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/categoria.php';
class CategoriaTest extends TestCase
{
    private $categoria;
    protected function setUp(): void
    {
        $this->categoria = new Categoria();
    }
    protected function tearDown(): void
    {
        $this->categoria = null;
    }
    public function testSetAndGetCategoria()
    {
        $this->categoria->setCategoria('Docente');
        $this->assertEquals('Docente', $this->categoria->getCategoria());
    }
    public function testSetAndGetDescripcion()
    {
        $this->categoria->setDescripcion('Categoría para docentes');
        $this->assertEquals('Categoría para docentes', $this->categoria->getDescripcion());
    }
    public function testRegistrarExitoso()
    {
        $categoria = new class extends Categoria {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        private $insertMode = true;
                        public function setAttribute() { return true; }
                        public function prepare($sql) { 
                            $this->insertMode = strpos($sql, 'INSERT') !== false;
                            return new class {
                                private $isInsert;
                                public function __construct() {
                                    $this->isInsert = true;
                                }
                                public function setInsertMode($mode) {
                                    $this->isInsert = $mode;
                                }
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
        $categoria->setTestMode(true);
        $categoria->setCategoria('Nueva Categoría');
        $categoria->setDescripcion('Descripción de prueba');
        $resultado = $categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testRegistrarCategoriaYaExisteActiva()
    {
        $categoria = new class extends Categoria {
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
                                    return ['cat_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Existente');
        $categoria->setDescripcion('Descripción');
        $resultado = $categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testRegistrarReactivarCategoriaInactiva()
    {
        $categoria = new class extends Categoria {
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
                                    return ['cat_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Inactiva');
        $categoria->setDescripcion('Reactivar');
        $resultado = $categoria->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testModificarExitoso()
    {
        $categoria = new class extends Categoria {
            private $testMode = false;
            private $callCount = 0;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($categoriaNombre, $categoriaExcluir = null) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($categoriaNombre, $categoriaExcluir);
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
                                        'cat_nombre' => 'Categoría Original',
                                        'cat_descripcion' => 'Descripción Original'
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
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Modificada');
        $categoria->setDescripcion('Nueva Descripción');
        $resultado = $categoria->Modificar('Categoría Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testModificarCategoriaNoExiste()
    {
        $categoria = new class extends Categoria {
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
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Nueva');
        $categoria->setDescripcion('Descripción');
        $resultado = $categoria->Modificar('Categoría Inexistente');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testModificarSinCambios()
    {
        $categoria = new class extends Categoria {
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
                                        'cat_nombre' => 'Categoría Test',
                                        'cat_descripcion' => 'Descripción Test'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Test');
        $categoria->setDescripcion('Descripción Test');
        $resultado = $categoria->Modificar('Categoría Test');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }
    public function testModificarConNombreExistente()
    {
        $categoria = new class extends Categoria {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($categoriaNombre, $categoriaExcluir = null) {
                if ($this->testMode) {
                    return ['resultado' => 'existe', 'mensaje' => 'La CATEGORÍA colocada YA existe!'];
                }
                return parent::Existe($categoriaNombre, $categoriaExcluir);
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
                                        'cat_nombre' => 'Categoría Original',
                                        'cat_descripcion' => 'Descripción'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Existente');
        $categoria->setDescripcion('Descripción');
        $resultado = $categoria->Modificar('Categoría Original');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }
    public function testEliminarExitoso()
    {
        $categoria = new class extends Categoria {
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
                                    return ['cat_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría a Eliminar');
        $resultado = $categoria->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testEliminarCategoriaNoExiste()
    {
        $categoria = new class extends Categoria {
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
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Inexistente');
        $resultado = $categoria->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testEliminarCategoriaYaDesactivada()
    {
        $categoria = new class extends Categoria {
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
                                    return ['cat_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $categoria->setTestMode(true);
        $categoria->setCategoria('Categoría Desactivada');
        $resultado = $categoria->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivada', $resultado['mensaje']);
    }
    public function testExisteCategoriaActiva()
    {
        $categoria = new class extends Categoria {
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
        $categoria->setTestMode(true);
        $resultado = $categoria->Existe('Categoría Existente');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testNoExisteCategoria()
    {
        $categoria = new class extends Categoria {
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
        $categoria->setTestMode(true);
        $resultado = $categoria->Existe('Categoría Inexistente');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
    public function testExisteConExclusion()
    {
        $categoria = new class extends Categoria {
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
        $categoria->setTestMode(true);
        $resultado = $categoria->Existe('Categoría Test', 'Categoría Test');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}