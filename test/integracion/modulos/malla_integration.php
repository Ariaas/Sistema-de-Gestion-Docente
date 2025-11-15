<?php

require_once 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Model\Malla;
use App\Model\UC;
use App\Model\Area;
use App\Model\Eje;
use App\Model\Bitacora;
use PDO;
use Exception;
use PDOException;

abstract class IntegrationTestCase extends TestCase
{
    protected static $pdo = null;

    public static function setUpBeforeClass(): void
    {
        $dsn = 'mysql:host=localhost;dbname=tu_db_de_pruebas';
        $username = 'tu_usuario';
        $password = 'tu_contraseña';
        try {
            self::$pdo = new PDO($dsn, $username, $password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("No se pudo conectar a la base de datos de pruebas: " . $e->getMessage());
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo = null;
    }

    public function getConnection($modelInstance)
    {
        return self::$pdo;
    }
}

class MallaCurricularControllerTest extends IntegrationTestCase
{
    private $datosCreados = [];

    protected function setUp(): void
    {
        global $integrationTestPdo;
        $integrationTestPdo = self::$pdo;
        
        $this->datosCreados = [
            'mallas' => [],
            'ucs' => [],
            'areas' => [],
            'ejes' => []
        ];

        $_POST = [];
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->datosCreados['mallas'] as $codigo) {
             try {
                 self::$pdo->prepare("DELETE FROM uc_malla WHERE mal_codigo = ?")->execute([$codigo]);
                 self::$pdo->prepare("DELETE FROM tbl_malla WHERE mal_codigo = ?")->execute([$codigo]);
             } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['ucs'] as $codigo) {
             try {
                 self::$pdo->prepare("DELETE FROM tbl_uc WHERE uc_codigo = ?")->execute([$codigo]);
             } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['areas'] as $nombre) {
             try {
                 self::$pdo->prepare("DELETE FROM tbl_area WHERE are_nombre = ?")->execute([$nombre]);
             } catch (Exception $e) {}
         }
        foreach ($this->datosCreados['ejes'] as $nombre) {
             try {
                 self::$pdo->prepare("DELETE FROM tbl_eje WHERE eje_nombre = ?")->execute([$nombre]);
             } catch (Exception $e) {}
         }
         
        global $integrationTestPdo;
        $integrationTestPdo = null;
    }

    private function simularPeticionAjax($postData, $sessionData = [])
    {
        $_SESSION = $sessionData;
        $_POST = $postData;

        ob_start();
        
        try {
            include 'mallacurricular.php'; 
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $output = ob_get_clean();
        return json_decode($output, true);
    }
    
    private function obtenerCohorteLibre($min = 10, $max = 120)
    {
        $intentos = 0;
        do {
            $intentos++;
            $c = rand($min, $max);
            $stmt = self::$pdo->prepare("SELECT 1 FROM tbl_malla WHERE mal_cohorte = ? LIMIT 1");
            $stmt->execute([$c]);
            $existe = (bool)$stmt->fetchColumn();
            if (!$existe) return $c;
        } while ($intentos < 200);
        return rand($min, $max);
    }

    private function crearUCsParaMalla()
    {
        $ucsCreadas = [];
        $trayectos = [0, 1, 2, 3, 4];
        
        $nombreArea = 'Area_Ctrl_' . rand(1000, 9999);
        self::$pdo->prepare("INSERT INTO tbl_area (are_nombre, are_descripcion) VALUES (?, ?)")
                  ->execute([$nombreArea, 'Área para controller']);
        $this->datosCreados['areas'][] = $nombreArea;

        $nombreEje = 'Eje_Ctrl_' . rand(1000, 9999);
        self::$pdo->prepare("INSERT INTO tbl_eje (eje_nombre, eje_descripcion) VALUES (?, ?)")
                  ->execute([$nombreEje, 'Eje para controller']);
        $this->datosCreados['ejes'][] = $nombreEje;

        $stmt = self::$pdo->prepare(
            "INSERT INTO tbl_uc (uc_codigo, uc_nombre, uc_creditos, uc_trayecto, uc_periodo, eje_nombre, are_nombre, uc_estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );

        foreach ($trayectos as $trayecto) {
            $codigoUC = 'UCC' . $trayecto . rand(100, 999);
            $stmt->execute([
                $codigoUC, 'UC Ctrl ' . $trayecto, 4, $trayecto, 'Fase I', $nombreEje, $nombreArea
            ]);
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

    public function testRegistrar_ViaController_ConDatosValidos_Exito()
    {
        $ucs = $this->crearUCsParaMalla();
        $codigoMalla = 'MALLA-CTRL-' . rand(1000, 9999);
        $cohorte = $this->obtenerCohorteLibre();
        $this->datosCreados['mallas'][] = $codigoMalla;

        $postData = [
            'accion' => 'registrar',
            'mal_codigo' => $codigoMalla,
            'mal_nombre' => 'Malla desde Controller',
            'mal_cohorte' => $cohorte,
            'mal_descripcion' => 'Descripción de prueba controller',
            'unidades' => json_encode($ucs)
        ];

        $sessionData = [
            'usu_id' => 1
        ];

        $response = $this->simularPeticionAjax($postData, $sessionData);
        
        $this->assertNotNull($response, "La respuesta del controlador fue nula o no es JSON válido.");
        $this->assertEquals('registrar', $response['resultado']);
        $this->assertStringContainsString('Registro Incluido', $response['mensaje']);
    }

    public function testRegistrar_ViaController_SinSession_Error()
    {
        $postData = [
            'accion' => 'registrar',
            'mal_codigo' => 'MALLA-FAIL',
            'mal_nombre' => 'Malla sin sesión',
            'mal_cohorte' => 999,
            'mal_descripcion' => 'Debe fallar',
            'unidades' => '[]'
        ];

        $sessionData = []; 

        $response = $this->simularPeticionAjax($postData, $sessionData);
        
        $this->assertNotNull($response);
        $this->assertEquals('error', $response['resultado']);
        $this->assertEquals('Usuario no autenticado.', $response['mensaje']);
    }

    public function testRegistrar_ViaController_SinUnidades_ErrorModelo()
    {
        $postData = [
            'accion' => 'registrar',
            'mal_codigo' => 'MALLA-NO-UC',
            'mal_nombre' => 'Malla sin unidades',
            'mal_cohorte' => $this->obtenerCohorteLibre(),
            'mal_descripcion' => 'Prueba de error del modelo',
            'unidades' => '[]'
        ];

        $sessionData = ['usu_id' => 1];

        $response = $this->simularPeticionAjax($postData, $sessionData);
        
        $this->assertNotNull($response);
        $this->assertEquals('error', $response['resultado']);
        $this->assertStringContainsString('No se han proporcionado unidades', $response['mensaje']);
    }
    
    public function testConsultar_ViaController_DevuelveJSON()
    {
        $postData = ['accion' => 'consultar'];
        $response = $this->simularPeticionAjax($postData);

        $this->assertNotNull($response);
        $this->assertEquals('consultar', $response['resultado']);
        $this->assertIsArray($response['mensaje']);
    }

    public function testVerificarCondiciones_ViaController_DevuelveJSON()
    {
        $this->crearUCsParaMalla();
        
        $postData = ['accion' => 'verificar_condiciones'];
        
        $response = $this->simularPeticionAjax($postData);

        $this->assertNotNull($response);
        $this->assertTrue($response['puede_registrar']);
        $this->assertEquals('', $response['mensaje']);
    }
}