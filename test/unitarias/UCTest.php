<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/uc.php';
class UCTest extends TestCase
{
    private $uc;
    protected function setUp(): void
    {
        $this->uc = new UC();
    }
    protected function tearDown(): void
    {
        $this->uc = null;
    }
    public function testSetAndGetCodigoUC()
    {
        $this->uc->setcodigoUC('UC001');
        $this->assertEquals('UC001', $this->uc->getcodigoUC());
    }
    public function testSetAndGetNombreUC()
    {
        $this->uc->setnombreUC('Programación I');
        $this->assertEquals('Programación I', $this->uc->getnombreUC());
    }
    public function testSetAndGetCreditosUC()
    {
        $this->uc->setcreditosUC(4);
        $this->assertEquals(4, $this->uc->getcreditosUC());
    }
    public function testSetAndGetAsistidaUC()
    {
        $this->uc->setasistidaUC(3);
        $this->assertEquals(3, $this->uc->getasistidaUC());
    }
    public function testSetAndGetAcademicaUC()
    {
        $this->uc->setacademicaUC(2);
        $this->assertEquals(2, $this->uc->getacademicaUC());
    }
    public function testSetAndGetIndependienteUC()
    {
        $this->uc->setindependienteUC(1);
        $this->assertEquals(1, $this->uc->getindependienteUC());
    }
    public function testSetAndGetTrayectoUC()
    {
        $this->uc->settrayectoUC(1);
        $this->assertEquals(1, $this->uc->gettrayectoUC());
    }
    public function testSetAndGetEjeUC()
    {
        $this->uc->setejeUC('Eje Profesional');
        $this->assertEquals('Eje Profesional', $this->uc->getejeUC());
    }
    public function testSetAndGetAreaUC()
    {
        $this->uc->setareaUC('Área de Formación');
        $this->assertEquals('Área de Formación', $this->uc->getareaUC());
    }
    public function testSetAndGetPeriodoUC()
    {
        $this->uc->setperiodoUC(1);
        $this->assertEquals(1, $this->uc->getperiodoUC());
    }
    public function testSetAndGetIdUC()
    {
        $this->uc->setidUC(1);
        $this->assertEquals(1, $this->uc->getidUC());
    }
    public function testRegistrarExitoso()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $uc->setnombreUC('Programación I');
        $uc->setcreditosUC(4);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(1);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testRegistrarUCYaExisteActiva()
    {
        $uc = new class extends UC {
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
                                    return ['uc_estado' => 1];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $uc->setnombreUC('Programación I');
        $uc->setcreditosUC(4);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(1);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testRegistrarReactivarUCInactiva()
    {
        $uc = new class extends UC {
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
                                    return ['uc_estado' => 0];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $uc->setnombreUC('Programación I');
        $uc->setcreditosUC(4);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(1);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testModificarExitoso()
    {
        $uc = new class extends UC {
            private $testMode = false;
            private $callCount = 0;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($codigoUC, $codigoExcluir = null) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($codigoUC, $codigoExcluir);
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
                                        'uc_codigo' => 'UC001',
                                        'uc_nombre' => 'Programación I',
                                        'uc_creditos' => 4,
                                        'uc_trayecto' => 1,
                                        'uc_periodo' => 1,
                                        'eje_nombre' => 'Eje Profesional',
                                        'area_nombre' => 'Área de Formación'
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
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC002');
        $uc->setnombreUC('Programación II');
        $uc->setcreditosUC(5);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(2);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Modificar('UC001');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testModificarUCNoExiste()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC999');
        $uc->setnombreUC('UC Inexistente');
        $uc->setcreditosUC(4);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(1);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Modificar('UC999');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testModificarSinCambios()
    {
        $uc = new class extends UC {
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
                                        'uc_codigo' => 'UC001',
                                        'uc_nombre' => 'Programación I',
                                        'uc_creditos' => 4,
                                        'uc_trayecto' => 1,
                                        'uc_periodo' => 1,
                                        'eje_nombre' => 'Eje Profesional',
                                        'area_nombre' => 'Área de Formación'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $uc->setnombreUC('Programación I');
        $uc->setcreditosUC(4);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(1);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Modificar('UC001');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('No se realizaron cambios', $resultado['mensaje']);
    }
    public function testModificarConCodigoExistente()
    {
        $uc = new class extends UC {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($codigoUC, $codigoExcluir = null) {
                if ($this->testMode) {
                    return ['resultado' => 'existe', 'mensaje' => 'La unidad de curricular YA existe!'];
                }
                return parent::Existe($codigoUC, $codigoExcluir);
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
                                        'uc_codigo' => 'UC001',
                                        'uc_nombre' => 'Programación I',
                                        'uc_creditos' => 4,
                                        'uc_trayecto' => 1,
                                        'uc_periodo' => 1,
                                        'eje_nombre' => 'Eje Profesional',
                                        'area_nombre' => 'Área de Formación'
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC002');
        $uc->setnombreUC('Programación II');
        $uc->setcreditosUC(5);
        $uc->settrayectoUC(1);
        $uc->setperiodoUC(2);
        $uc->setejeUC('Eje Profesional');
        $uc->setareaUC('Área de Formación');
        $resultado = $uc->Modificar('UC001');
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    public function testEliminarExitoso()
    {
        $uc = new class extends UC {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($codigoUC, $codigoExcluir = null) {
                if ($this->testMode) {
                    return ['resultado' => 'existe'];
                }
                return parent::Existe($codigoUC, $codigoExcluir);
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $resultado = $uc->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
    public function testEliminarUCNoExiste()
    {
        $uc = new class extends UC {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Existe($codigoUC, $codigoExcluir = null) {
                if ($this->testMode) {
                    return [];
                }
                return parent::Existe($codigoUC, $codigoExcluir);
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC999');
        $resultado = $uc->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testExisteUCActiva()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $resultado = $uc->Existe('UC001');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertEquals('existe', $resultado['resultado']);
    }
    public function testNoExisteUC()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $resultado = $uc->Existe('UC999');
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
    public function testObtenerEje()
    {
        $uc = new class extends UC {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function query() { 
                            return new class {
                                public function fetchAll() {
                                    return [
                                        ['eje_nombre' => 'Eje Profesional'],
                                        ['eje_nombre' => 'Eje Epistemológico']
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $resultado = $uc->obtenerEje();
        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
    }
    public function testObtenerArea()
    {
        $uc = new class extends UC {
            private $testMode = false;
            public function setTestMode($mode) {
                $this->testMode = $mode;
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function query() { 
                            return new class {
                                public function fetchAll() {
                                    return [
                                        ['area_nombre' => 'Área de Formación'],
                                        ['area_nombre' => 'Área Socio Crítica']
                                    ];
                                }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $resultado = $uc->obtenerArea();
        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
    }
    public function testVerificarEnHorario()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $resultado = $uc->verificarEnHorario('UC001');
        $this->assertEquals('en_horario', $resultado['resultado']);
        $this->assertStringContainsString('está en un horario', $resultado['mensaje']);
    }
    public function testVerificarNoEnHorario()
    {
        $uc = new class extends UC {
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
        $uc->setTestMode(true);
        $resultado = $uc->verificarEnHorario('UC999');
        $this->assertEquals('no_en_horario', $resultado['resultado']);
        $this->assertStringContainsString('no está en un horario', $resultado['mensaje']);
    }
    public function testActivar()
    {
        $uc = new class extends UC {
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
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $uc->setTestMode(true);
        $uc->setcodigoUC('UC001');
        $resultado = $uc->Activar();
        $this->assertEquals('activar', $resultado['resultado']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }
}