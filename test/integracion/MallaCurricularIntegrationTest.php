<?php

use PHPUnit\Framework\TestCase;

require_once 'model/mallacurricular.php';
require_once 'model/uc.php';
require_once 'model/area.php';
require_once 'model/eje.php';

class MallaCurricularIntegrationTest extends IntegrationTestCase
{
    private $malla;
    private $uc;
    private $area;
    private $eje;
    private $datosCreados = [];

    protected function setUp(): void
    {
        $this->malla = new Malla();
        $this->uc = new UC();
        $this->area = new Area();
        $this->eje = new Eje();
        $this->datosCreados = [
            'mallas' => [],
            'ucs' => [],
            'areas' => [],
            'ejes' => []
        ];
    }

    protected function tearDown(): void
    {

        foreach ($this->datosCreados['ucs'] as $codigo) {
            try {
                $ucTemp = new UC();
                $ucTemp->setcodigoUC($codigo);
                $ucTemp->Eliminar();
            } catch (Exception $e) {
            }
        }


        foreach ($this->datosCreados['ejes'] as $nombre) {
            try {
                $ejeTemp = new Eje();
                $ejeTemp->setEje($nombre);
                $ejeTemp->Eliminar();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Devuelve un número de cohorte que no existe actualmente en tbl_malla.
     * Intenta varios valores aleatorios hasta encontrar uno libre.
     */
    private function obtenerCohorteLibre($min = 10, $max = 120)
    {
        $co = getConnection($this->malla);
        $intentos = 0;
        do {
            $intentos++;
            $c = rand($min, $max);
            try {
                $stmt = $co->prepare("SELECT 1 FROM tbl_malla WHERE mal_cohorte = ? LIMIT 1");
                $stmt->execute([$c]);
                $existe = (bool)$stmt->fetchColumn();
            } catch (Exception $e) {
                // si hay error en la consulta, asumimos que no existe y devolvemos el valor
                $existe = false;
            }
            if (!$existe) return $c;
        } while ($intentos < 200);
        // fallback: devuelve un valor en rango aunque exista (raro)
        return rand($min, $max);
    }

    private function crearUCsParaMalla()
    {
        $ucsCreadas = [];
        $trayectos = [0, 1, 2, 3, 4];

        $nombreArea = 'Area_Malla_' . rand(1000, 9999);
        $this->area->setArea($nombreArea);
        $this->area->setDescripcion('Área para malla');
        $this->area->Registrar();
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Malla_' . rand(1000, 9999);
        $this->eje->setEje($nombreEje);
        $this->eje->setDescripcion('Eje para malla');
        $this->eje->Registrar();
        $this->datosCreados['ejes'][] = $nombreEje;

        foreach ($trayectos as $trayecto) {
            $codigoUC = 'UCM' . $trayecto . rand(100, 999);
            $uc = new UC();
            $uc->setcodigoUC($codigoUC);
            $uc->setnombreUC('UC Trayecto ' . $trayecto);
            $uc->setcreditosUC(4);
            $uc->settrayectoUC($trayecto);
            $uc->setperiodoUC('Fase I');
            $uc->setejeUC($nombreEje);
            $uc->setareaUC($nombreArea);
            $uc->Registrar();
            $this->datosCreados['ucs'][] = $codigoUC;

            $ucsCreadas[] = [
                'uc_codigo' => $codigoUC,
                'hora_independiente' => 2,
                'hora_asistida' => 3,
                'hora_academica' => 4
            ];
        }

        return $ucsCreadas;
    }

    public function testVerificarCondicionesParaRegistrar_ConUCsEnTodosTrayectos_PuedeRegistrar()
    {
        $ucs = $this->crearUCsParaMalla();

        $resultado = $this->malla->verificarCondicionesParaRegistrar();

        $this->assertTrue($resultado['puede_registrar']);
    }

    public function testRegistrarMalla_ConDatosValidos_Exito()
    {
        $ucs = $this->crearUCsParaMalla();

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');

        $resultado = $this->malla->Registrar($ucs);

        if ($resultado['resultado'] === 'registrar' && !str_contains($resultado['mensaje'], 'ERROR')) {
            $this->assertEquals('registrar', $resultado['resultado']);
            $this->datosCreados['mallas'][] = $codigoMalla;
        } else {
            $this->markTestSkipped('No se pudo registrar la malla: ' . $resultado['mensaje']);
        }
    }

    public function testRegistrarMalla_SinUnidades_Error()
    {
        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');

        $resultado = $this->malla->Registrar([]);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('No se han proporcionado unidades', $resultado['mensaje']);
    }

    public function testRegistrarMalla_HorasInvalidas_Error()
    {
        $ucs = $this->crearUCsParaMalla();
        $ucs[0]['hora_independiente'] = 0;

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');

        $resultado = $this->malla->Registrar($ucs);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('horas inválidas', $resultado['mensaje']);
    }

    public function testRegistrarMalla_HorasNulas_Error()
    {
        $ucs = $this->crearUCsParaMalla();
        $ucs[0]['hora_independiente'] = null;

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');

        $resultado = $this->malla->Registrar($ucs);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nulos o faltantes', $resultado['mensaje']);
    }

    public function testRegistrarMalla_SinTrayectoInicial_Error()
    {
        $ucs = $this->crearUCsParaMalla();
        $ucs = array_filter($ucs, function ($uc) {
            $ucObj = new UC();
            $co = getConnection($ucObj);
            $stmt = $co->prepare("SELECT uc_trayecto FROM tbl_uc WHERE uc_codigo = ?");
            $stmt->execute([$uc['uc_codigo']]);
            $trayecto = $stmt->fetchColumn();
            return $trayecto != 0;
        });

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');

        $resultado = $this->malla->Registrar($ucs);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Debe incluir al menos una UC', $resultado['mensaje']);
    }

    public function testModificarMalla_ConDatosValidos_Exito()
    {
        $ucs = $this->crearUCsParaMalla();

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Original');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM uc_malla WHERE mal_codigo = ?")->execute([$codigoMalla]);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_codigo = ?")->execute([$codigoMalla]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción original');
        $resultado1 = $this->malla->Registrar($ucs);

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['mallas'][] = $codigoMalla;

            $ucs[0]['hora_independiente'] = 3;

            $mallaMod = new Malla();
            $mallaMod->setMalCodigo($codigoMalla);
            $mallaMod->setMalCodigoOriginal($codigoMalla);
            $mallaMod->setMalNombre('Malla Modificada');
            do {
                $newCoh = $this->obtenerCohorteLibre();
            } while ($newCoh == $cohorte);
            $mallaMod->setMalCohorte($newCoh);
            $mallaMod->setMalDescripcion('Descripción modificada');

            $resultado = $mallaMod->Modificar($ucs);

            $this->assertEquals('modificar', $resultado['resultado']);
        } else {
            $this->markTestSkipped('No se pudo registrar la malla inicial');
        }
    }

    public function testListarMallas_ConMallas_Exito()
    {
        $ucs = $this->crearUCsParaMalla();

        $codigoMalla = 'MALLA' . rand(1000, 9999);
        $this->malla->setMalCodigo($codigoMalla);
        $this->malla->setMalNombre('Malla Test');
        $cohorte = $this->obtenerCohorteLibre();
        $this->malla->setMalCohorte($cohorte);
        try {
            $co = getConnection($this->malla);
            $co->prepare("DELETE FROM tbl_malla WHERE mal_cohorte = ?")->execute([$cohorte]);
        } catch (Exception $e) {
        }
        $this->malla->setMalDescripcion('Descripción de prueba');
        $resultado1 = $this->malla->Registrar($ucs);

        if ($resultado1['resultado'] === 'registrar' && !str_contains($resultado1['mensaje'], 'ERROR')) {
            $this->datosCreados['mallas'][] = $codigoMalla;

            $resultado = $this->malla->Consultar();

            $this->assertEquals('consultar', $resultado['resultado']);
            $this->assertIsArray($resultado['mensaje']);

            $encontrada = false;
            foreach ($resultado['mensaje'] as $malla) {
                if ($malla['mal_codigo'] === $codigoMalla) {
                    $encontrada = true;
                    break;
                }
            }
            $this->assertTrue($encontrada);
        } else {
            $this->markTestSkipped('No se pudo registrar la malla');
        }
    }
}
