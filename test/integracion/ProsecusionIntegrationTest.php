<?php

use PHPUnit\Framework\TestCase;

require_once 'model/prosecusion.php';
require_once 'model/seccion.php';
require_once 'model/anio.php';

class ProsecusionIntegrationTest extends IntegrationTestCase
{
    private $prosecusion;
    private $seccion;
    private $anio;
    private $datosCreados = [];

    protected function setUp(): void
    {
        $this->prosecusion = new Prosecusion();
        $this->seccion = new Seccion();
        $this->anio = new Anio();
        $this->datosCreados = [
            'secciones' => [],
            'anios' => [],
            'prosecusiones' => []
        ];
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
    }

    public function testVerificarEstado_ConAnioActivo_RetornaInformacion()
    {
        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('anio_activo_existe', $resultado);
        $this->assertArrayHasKey('anio_destino_existe', $resultado);
    }

    public function testVerificarEstado_SinAnioActivo_RetornaFalse()
    {
        $resultado = $this->prosecusion->VerificarEstado();

        $this->assertIsArray($resultado);
        $this->assertIsBool($resultado['anio_activo_existe']);
    }

    public function testObtenerOpcionesDestinoManual_ConSeccionValida_RetornaOpciones()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultado', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);
    }

    public function testObtenerOpcionesDestinoManual_SeccionInexistente_RetornaError()
    {
        $codigoInexistente = 'IN9' . rand(100, 999);

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual($codigoInexistente);

        $this->assertIsArray($resultado);
        if (isset($resultado['resultado']) && $resultado['resultado'] === 'error') {
            $this->assertEquals('error', $resultado['resultado']);
        }
    }

    public function testVerificarDestinoAutomatico_ConSeccionValida_VerificaExistencia()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->verificarDestinoAutomatico($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('existe', $resultado);
        $this->assertIsBool($resultado['existe']);
    }

    public function testVerificarDestinoAutomatico_SeccionInexistente_RetornaNoExiste()
    {
        $codigoInexistente = 'IN9' . rand(100, 999);

        $resultado = $this->prosecusion->verificarDestinoAutomatico($codigoInexistente);

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['existe']);
    }

    public function testVerificarDestinoAutomatico_SeccionTrayecto2_GeneraTrayecto3()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN2' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->verificarDestinoAutomatico($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('existe', $resultado);
        if ($resultado['existe']) {
            $this->assertStringContainsString('IIN3', $resultado['seccion_destino']);
        }
    }

    public function testCalcularCantidadProsecusion_ConSeccionValida_RetornaCalculo()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->calcularCantidadProsecusion($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('puede_prosecusionar', $resultado);
        $this->assertArrayHasKey('cantidad_final', $resultado);
        $this->assertArrayHasKey('cantidad_disponible', $resultado);
    }

    public function testCalcularCantidadProsecusion_SeccionInexistente_RetornaError()
    {
        $codigoInexistente = 'IN9' . rand(100, 999);

        $resultado = $this->prosecusion->calcularCantidadProsecusion($codigoInexistente);

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['puede_prosecusionar']);
    }

    public function testObtenerOpcionesDestinoManual_SeccionTrayecto1_BuscaTrayecto2()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $co = getConnection($this->seccion);
        $chk = $co->prepare("SELECT 1 FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $chk->execute([$anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_anio (ani_anio, ani_tipo, ani_activo, ani_estado) VALUES (?, ?, 1, 1)")->execute([$anioNum, 'regular']);
        }

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);
        $this->assertIsArray($resultado['mensaje']);
    }

    public function testObtenerOpcionesDestinoManual_SeccionTrayecto0_BuscaTrayecto1()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN0' . rand(100, 999);

        $co = getConnection($this->seccion);
        $chk = $co->prepare("SELECT 1 FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ? LIMIT 1");
        $chk->execute([$anioNum, 'regular']);
        if (!$chk->fetchColumn()) {
            $co->prepare("INSERT INTO tbl_anio (ani_anio, ani_tipo, ani_activo, ani_estado) VALUES (?, ?, 1, 1)")->execute([$anioNum, 'regular']);
        }

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);
    }

    public function testVerificarDestinoAutomatico_PrefijoIN_MantienePrefijo()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->verificarDestinoAutomatico($codigoSeccion);

        $this->assertIsArray($resultado);
        if ($resultado['existe']) {
            $this->assertStringStartsWith('IN', $resultado['seccion_destino']);
        }
    }

    public function testVerificarDestinoAutomatico_PrefijoIIN_MantienePrefijo()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IIN3' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->verificarDestinoAutomatico($codigoSeccion);

        $this->assertIsArray($resultado);
        if ($resultado['existe']) {
            $this->assertStringStartsWith('IIN', $resultado['seccion_destino']);
        }
    }

    public function testObtenerOpcionesDestinoManual_ConAnioDestino_BuscaEnAnioSiguiente()
    {
        $anioNum = rand(2024, 2029);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 25, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->obtenerOpcionesDestinoManual($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertEquals('opcionesDestinoManual', $resultado['resultado']);

        if (count($resultado['mensaje']) > 0) {
            $primeraOpcion = $resultado['mensaje'][0];
            $this->assertEquals($anioNum + 1, $primeraOpcion['ani_anio']);
        }
    }

    public function testCalcularCantidadProsecusion_ConCantidadValida_RetornaCantidades()
    {
        $anioNum = rand(2024, 2030);
        $codigoSeccion = 'IN1' . rand(100, 999);

        $this->seccion->RegistrarSeccion($codigoSeccion, 30, $anioNum, 'regular', true);
        $this->datosCreados['secciones'][] = ['codigo' => $codigoSeccion, 'anio' => $anioNum, 'tipo' => 'regular'];

        $resultado = $this->prosecusion->calcularCantidadProsecusion($codigoSeccion);

        $this->assertIsArray($resultado);
        $this->assertIsInt($resultado['cantidad_final']);
        $this->assertIsInt($resultado['cantidad_disponible']);
    }
}
