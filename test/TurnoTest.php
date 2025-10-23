<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../model/turno.php';
class TurnoTest extends TestCase
{
    private $turno;
    protected function setUp(): void
    {
        $this->turno = new Turno();
    }
    protected function tearDown(): void
    {
        $this->turno = null;
    }
    public function testSetAndGetNombreTurno()
    {
        $this->turno->setNombreTurno('  Mañana  ');
        $this->assertEquals('Mañana', $this->turno->getNombreTurno());
    }
    public function testSetAndGetHoraInicio()
    {
        $this->turno->setHoraInicio('07:00:00');
        $this->assertEquals('07:00:00', $this->turno->getHoraInicio());
    }
    public function testSetAndGetHoraFin()
    {
        $this->turno->setHoraFin('12:00:00');
        $this->assertEquals('12:00:00', $this->turno->getHoraFin());
    }
    public function testRegistrarHoraFinMenorQueInicio()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function chequearSolapamiento() {
                if ($this->testMode) return ['solapamiento' => false];
                return parent::chequearSolapamiento();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Turno Test');
        $turno->setHoraInicio('12:00:00');
        $turno->setHoraFin('08:00:00');
        $resultado = $turno->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('mayor que la hora de inicio', $resultado['mensaje']);
    }
    public function testRegistrarConSolapamiento()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function chequearSolapamiento() {
                if ($this->testMode) return ['solapamiento' => true, 'turno_choca' => 'Mañana'];
                return parent::chequearSolapamiento();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Turno Nuevo');
        $turno->setHoraInicio('08:00:00');
        $turno->setHoraFin('12:00:00');
        $resultado = $turno->Registrar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('solapa', $resultado['mensaje']);
    }
    public function testRegistrarExitoso()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function chequearSolapamiento() {
                if ($this->testMode) return ['solapamiento' => false];
                return parent::chequearSolapamiento();
            }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return false; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Tarde');
        $turno->setHoraInicio('13:00:00');
        $turno->setHoraFin('18:00:00');
        $resultado = $turno->Registrar();
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    public function testEliminarExitoso()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return ['tur_estado' => 1]; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Noche');
        $resultado = $turno->Eliminar();
        $this->assertEquals('eliminar', $resultado['resultado']);
    }
    public function testEliminarNoExiste()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return false; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Inexistente');
        $resultado = $turno->Eliminar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe', $resultado['mensaje']);
    }
    public function testEliminarYaDesactivado()
    {
        $turno = new class extends Turno {
            private $testMode = false;
            public function setTestMode($mode) { $this->testMode = $mode; }
            public function Con() {
                if ($this->testMode) {
                    return new class {
                        public function setAttribute() { return true; }
                        public function prepare() { 
                            return new class {
                                public function execute() { return true; }
                                public function fetch() { return ['tur_estado' => 0]; }
                            };
                        }
                    };
                }
                return parent::Con();
            }
        };
        $turno->setTestMode(true);
        $turno->setNombreTurno('Desactivado');
        $resultado = $turno->Eliminar();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya está desactivado', $resultado['mensaje']);
    }
}