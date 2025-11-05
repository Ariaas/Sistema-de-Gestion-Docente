<?php

use PHPUnit\Framework\TestCase;

require_once 'model/espacios.php';
require_once 'model/turno.php';

class EspacioTurnoIntegrationTest extends IntegrationTestCase
{
    private $espacio;
    private $turno;
    private $datosCreados = [];

    protected function setUp(): void
    {
        $this->espacio = new Espacio();
        $this->turno = new Turno();
        $this->datosCreados = [
            'espacios' => [],
            'turnos' => []
        ];
        try {
            $co = getConnection($this->turno);
            $co->prepare("UPDATE tbl_turno SET tur_estado = 0 WHERE tur_nombre LIKE 'Turno_Test_%' OR tur_nombre IN ('Turno_Nuevo','Mañana','Tarde','Noche')")->execute();
        } catch (Exception $e) {
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->datosCreados['espacios'] as $esp) {
            try {
                $espTemp = new Espacio();
                $espTemp->setNumero($esp['numero']);
                $espTemp->setEdificio($esp['edificio']);
                $espTemp->setTipo($esp['tipo']);
                $espTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        foreach ($this->datosCreados['turnos'] as $nombre) {
            try {
                $turnoTemp = new Turno();
                $turnoTemp->setNombreTurno($nombre);
                $turnoTemp->Eliminar();
            } catch (Exception $e) {
            }
        }
    }

    public function testRegistrarEspacio_DatosValidos_Exito()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');

        $resultado = $this->espacio->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->datosCreados['espacios'][] = ['numero' => $numero, 'edificio' => 'Principal', 'tipo' => 'Aula'];
    }

    public function testRegistrarEspacio_Duplicado_Error()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');
        $this->espacio->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => $numero, 'edificio' => 'Principal', 'tipo' => 'Aula'];

        $espacio2 = new Espacio();
        $espacio2->setNumero($numero);
        $espacio2->setEdificio('Principal');
        $espacio2->setTipo('Aula');

        $resultado = $espacio2->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('YA existe', $resultado['mensaje']);
    }

    public function testRegistrarEspacio_NumeroVacio_Error()
    {
        $this->espacio->setNumero('');
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');

        $resultado = $this->espacio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no puede estar vacío', $resultado['mensaje']);
    }

    public function testModificarEspacio_CambiarTipo_Exito()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');
        $this->espacio->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => $numero, 'edificio' => 'Principal', 'tipo' => 'Laboratorio'];

        $espacioMod = new Espacio();
        $espacioMod->setNumero($numero);
        $espacioMod->setEdificio('Principal');
        $espacioMod->setTipo('Laboratorio');

        $resultado = $espacioMod->Modificar($numero, 'Principal', 'Aula');

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    public function testEliminarEspacio_EspacioExistente_Exito()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');
        $this->espacio->Registrar();

        $espacioElim = new Espacio();
        $espacioElim->setNumero($numero);
        $espacioElim->setEdificio('Principal');
        $espacioElim->setTipo('Aula');

        $resultado = $espacioElim->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
    }

    public function testListarEspacios_ConEspacios_Exito()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');
        $this->espacio->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => $numero, 'edificio' => 'Principal', 'tipo' => 'Aula'];

        $resultado = $this->espacio->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        $encontrado = false;
        foreach ($resultado['mensaje'] as $esp) {
            if ($esp['esp_numero'] == $numero && $esp['esp_edificio'] === 'Principal') {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testRegistrarTurno_DatosValidos_Exito()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');

        $resultado = $this->turno->Registrar();

        $this->assertEquals('registrar', $resultado['resultado']);
        $this->datosCreados['turnos'][] = $nombreTurno;
    }

    public function testRegistrarTurno_Duplicado_Error()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno;

        $turno2 = new Turno();
        $turno2->setNombreTurno($nombreTurno);
        $turno2->setHoraInicio('13:00:00');
        $turno2->setHoraFin('17:00:00');

        $resultado = $turno2->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    public function testRegistrarTurno_HoraFinMenorQueInicio_Error()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('12:00:00');
        $this->turno->setHoraFin('08:00:00');

        $resultado = $this->turno->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('debe ser mayor', $resultado['mensaje']);
    }

    public function testRegistrarTurno_Solapamiento_Error()
    {
        $nombreTurno1 = 'Turno_Test_' . rand(1000, 4999);
        $this->turno->setNombreTurno($nombreTurno1);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno1;

        $nombreTurno2 = 'Turno_Test_' . rand(5000, 9999);
        $turno2 = new Turno();
        $turno2->setNombreTurno($nombreTurno2);
        $turno2->setHoraInicio('10:00:00');
        $turno2->setHoraFin('14:00:00');

        $resultado = $turno2->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('se solapa', $resultado['mensaje']);
    }

    public function testModificarTurno_CambiarHoras_Exito()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno;

        $turnoMod = new Turno();
        $turnoMod->setNombreTurno($nombreTurno);
        $turnoMod->setNombreTurnoOriginal($nombreTurno);
        $turnoMod->setHoraInicio('07:00:00');
        $turnoMod->setHoraFin('11:00:00');

        $resultado = $turnoMod->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
    }

    public function testEliminarTurno_TurnoExistente_Exito()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();

        $turnoElim = new Turno();
        $turnoElim->setNombreTurno($nombreTurno);

        $resultado = $turnoElim->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
    }

    public function testConsultarTurnos_ConTurnos_Exito()
    {
        $nombreTurno = 'Turno_Test_' . rand(1000, 9999);
        $this->turno->setNombreTurno($nombreTurno);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno;

        $resultado = $this->turno->Consultar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);

        $encontrado = false;
        foreach ($resultado['mensaje'] as $turno) {
            if ($turno['tur_nombre'] === $nombreTurno) {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testChequearSolapamiento_ConSolapamiento_RetornaTrue()
    {
        $nombreTurno1 = 'Turno_Test_' . rand(1000, 4999);
        $this->turno->setNombreTurno($nombreTurno1);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno1;

        $turno2 = new Turno();
        $turno2->setNombreTurno('Turno_Nuevo');
        $turno2->setHoraInicio('10:00:00');
        $turno2->setHoraFin('14:00:00');

        $resultado = $turno2->chequearSolapamiento();

        $this->assertTrue($resultado['solapamiento']);
        $this->assertEquals($nombreTurno1, $resultado['turno_choca']);
    }

    public function testChequearSolapamiento_SinSolapamiento_RetornaFalse()
    {
        $nombreTurno1 = 'Turno_Test_' . rand(1000, 4999);
        $this->turno->setNombreTurno($nombreTurno1);
        $this->turno->setHoraInicio('08:00:00');
        $this->turno->setHoraFin('12:00:00');
        $this->turno->Registrar();
        $this->datosCreados['turnos'][] = $nombreTurno1;

        $turno2 = new Turno();
        $turno2->setNombreTurno('Turno_Nuevo');
        $turno2->setHoraInicio('13:00:00');
        $turno2->setHoraFin('17:00:00');

        $resultado = $turno2->chequearSolapamiento();

        $this->assertFalse($resultado['solapamiento']);
    }

    public function testExisteEspacio_EspacioRegistrado_RetornaExiste()
    {
        $numero = rand(100, 999);
        $this->espacio->setNumero($numero);
        $this->espacio->setEdificio('Principal');
        $this->espacio->setTipo('Aula');
        $this->espacio->Registrar();
        $this->datosCreados['espacios'][] = ['numero' => $numero, 'edificio' => 'Principal', 'tipo' => 'Aula'];

        $resultado = $this->espacio->Existe($numero, 'Principal', 'Aula');

        $this->assertEquals('existe', $resultado['resultado']);
    }

    public function testExisteEspacio_EspacioNoRegistrado_RetornaNoExiste()
    {
        $numero = rand(100, 999);

        $resultado = $this->espacio->Existe($numero, 'Inexistente', 'Aula');

        $this->assertEquals('no_existe', $resultado['resultado']);
    }
}
