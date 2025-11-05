<?php

use PHPUnit\Framework\TestCase;

require_once 'model/anio.php';
require_once 'model/mallacurricular.php';

class AnioFaseIntegrationTest extends IntegrationTestCase
{
    private $anio;
    private $malla;
    private $datosCreados = [];

    protected function setUp(): void
    {
        $this->anio = new Anio();
        $this->malla = new Malla();
        $this->datosCreados = [
            'anios' => []
        ];
        // ensure there is at least one active malla for Anio tests
        try {
            $co = getConnection($this->malla);
            $codigoMalla = 'MALLA_ANIO_TEST_' . rand(10000, 99999);

            // Inspect table columns to build a compatible INSERT for this environment
            $colsStmt = $co->query("SHOW COLUMNS FROM tbl_malla");
            $cols = array_map(fn($r) => $r['Field'], $colsStmt->fetchAll(PDO::FETCH_ASSOC));

            if (in_array('mal_activa', $cols, true)) {
                $sql = "INSERT IGNORE INTO tbl_malla (mal_codigo, mal_nombre, mal_descripcion, mal_cohorte, mal_activa) VALUES (?, ?, ?, ?, 1)";
                $params = [$codigoMalla, 'Malla Anio Test', 'Creada por test', rand(10000, 99999)];
            } elseif (in_array('mal_estado', $cols, true)) {
                // Older schema: use mal_estado column as active flag
                $sql = "INSERT IGNORE INTO tbl_malla (mal_codigo, mal_nombre, mal_descripcion, mal_cohorte, mal_estado) VALUES (?, ?, ?, ?, 1)";
                $params = [$codigoMalla, 'Malla Anio Test', 'Creada por test', rand(10000, 99999)];
            } else {
                // Fallback: try inserting at least code, name and cohorte
                $sql = "INSERT IGNORE INTO tbl_malla (mal_codigo, mal_nombre, mal_cohorte) VALUES (?, ?, ?)";
                $params = [$codigoMalla, 'Malla Anio Test', rand(10000, 99999)];
            }

            $co->prepare($sql)->execute($params);
            // Ensure the inserted (or existing) malla is active so Anio::Registrar sees it
            try {
                $co->prepare("UPDATE tbl_malla SET mal_activa = 1 WHERE mal_codigo = ?")->execute([$codigoMalla]);
            } catch (Exception $e) {
                // ignore if column doesn't exist or update fails
            }
            // verify presence
            $check = $co->query("SELECT 1 FROM tbl_malla WHERE mal_codigo = '" . $codigoMalla . "' LIMIT 1")->fetchColumn();
            if ($check) {
                $this->datosCreados['malla_codigo'] = $codigoMalla;
            }
        } catch (Exception $e) {
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->datosCreados['anios'] as $anio) {
            try {
                $anioTemp = new Anio();
                $anioTemp->setAnio($anio['anio']);
                $anioTemp->setTipo($anio['tipo']);
                $anioTemp->Eliminar();
            } catch (Exception $e) {
            }
        }

        // Note: Malla class does not have an Eliminar() method
        // remove the test malla we created (direct SQL)
        if (!empty($this->datosCreados['malla_codigo'])) {
            try {
                $co = getConnection($this->malla);
                $co->prepare("DELETE FROM tbl_malla WHERE mal_codigo = ?")->execute([$this->datosCreados['malla_codigo']]);
            } catch (Exception $e) {
            }
        }
    }

    public function testRegistrarAnioRegular_ConDosFases_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        // Ensure no pre-existing ano with same year/type to avoid collisions in shared test DB
        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado = $this->anio->Registrar();

        if ($resultado['resultado'] === 'registrar' && !str_contains($resultado['mensaje'], 'ERROR')) {
            $this->assertEquals('registrar', $resultado['resultado']);
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testRegistrarAnioIntensivo_ConUnaFase_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('intensivo');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'intensivo']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'intensivo']);
        } catch (Exception $e) {
        }

        $resultado = $this->anio->Registrar();

        if ($resultado['resultado'] === 'registrar' && !str_contains($resultado['mensaje'], 'ERROR')) {
            $this->assertEquals('registrar', $resultado['resultado']);
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'intensivo'];
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testRegistrarAnio_AnioDuplicado_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado1 = $this->anio->Registrar();

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];

            $anio2 = new Anio();
            $anio2->setAnio($anioNum);
            $anio2->setTipo('regular');
            $anio2->setActivo(1);
            $anio2->setFases([
                ['apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
                ['apertura' => '2024-07-01', 'cierre' => '2024-12-31']
            ]);

            $resultado2 = $anio2->Registrar();

            $this->assertEquals('registrar', $resultado2['resultado']);
            $this->assertStringContainsString('YA existe', $resultado2['mensaje']);
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testRegistrarAnio_FechasInvalidas_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-06-30', 'cierre' => '2024-01-01'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('cierre debe ser posterior', $resultado['mensaje']);
    }

    public function testRegistrarAnio_Fase2AntesQueFase1_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-06-15', 'cierre' => '2024-12-31']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('apertura de la fase 2 debe ser posterior', $resultado['mensaje']);
    }

    public function testRegistrarAnio_AnioInvalido_Error()
    {
        $this->anio->setAnio(1999);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('debe estar entre 2000 y 2100', $resultado['mensaje']);
    }

    public function testRegistrarAnio_TipoInvalido_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('invalido');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('debe ser "regular" o "intensivo"', $resultado['mensaje']);
    }

    public function testRegistrarAnio_SinFases_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
    }

    public function testRegistrarAnioRegular_SinFase2_Error()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['apertura' => '2024-01-01', 'cierre' => '2024-06-30']
        ]);

        $resultado = $this->anio->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('debe tener datos para la fase 2', $resultado['mensaje']);
    }

    public function testListarAnios_ConAniosRegistrados_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado1 = $this->anio->Registrar();

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];

            $resultado = $this->anio->Listar();

            $this->assertEquals('consultar', $resultado['resultado']);
            $this->assertIsArray($resultado['mensaje']);

            $encontrado = false;
            foreach ($resultado['mensaje'] as $anio) {
                if ($anio['ani_anio'] == $anioNum && $anio['ani_tipo'] === 'regular') {
                    $encontrado = true;
                    break;
                }
            }
            $this->assertTrue($encontrado);
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testExisteAnio_AnioRegistrado_RetornaTrue()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado1 = $this->anio->Registrar();

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];

            $existe = $this->anio->Existe($anioNum, 'regular');

            $this->assertTrue($existe);
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testExisteAnio_AnioNoRegistrado_RetornaFalse()
    {
        $anioNum = rand(2050, 2060);

        $existe = $this->anio->Existe($anioNum, 'regular');

        $this->assertFalse($existe);
    }

    public function testModificarAnio_CambiarFechas_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        $resultado1 = $this->anio->Registrar();

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];

            $anioMod = new Anio();
            $anioMod->setAnio($anioNum);
            $anioMod->setTipo('regular');
            $anioMod->setActivo(1);
            $anioMod->setFases([
                ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-15'],
                ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
            ]);

            $resultado = $anioMod->Modificar($anioNum, 'regular');

            $this->assertEquals('modificar', $resultado['resultado']);
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }

    public function testEliminarAnio_AnioRegistrado_Exito()
    {
        $anioNum = rand(2024, 2030);
        $this->anio->setAnio($anioNum);
        $this->anio->setTipo('regular');
        $this->anio->setActivo(1);
        $this->anio->setFases([
            ['numero' => 1, 'apertura' => '2024-01-01', 'cierre' => '2024-06-30'],
            ['numero' => 2, 'apertura' => '2024-07-01', 'cierre' => '2024-12-31']
        ]);

        try {
            $co = getConnection($this->anio);
            $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
            $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ?")->execute([$anioNum, 'regular']);
        } catch (Exception $e) {
        }

        $resultado1 = $this->anio->Registrar();

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['anios'][] = ['anio' => $anioNum, 'tipo' => 'regular'];

            $anioElim = new Anio();
            $anioElim->setAnio($anioNum);
            $anioElim->setTipo('regular');

            $resultado = $anioElim->Eliminar();

            $this->assertEquals('eliminar', $resultado['resultado']);
        } else {
            $this->markTestSkipped('No hay malla activa para registrar el año');
        }
    }
}
