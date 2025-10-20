<?php

use PHPUnit\Framework\TestCase;

/**
 * --- PREPARACIÓN DEL ENTORNO DE MOCK ---
 * (Este bloque es necesario para que las pruebas se ejecuten)
 */
if (!class_exists('Connection')) {
    class Connection
    {
        public function __construct()
        {
        }
        protected function Con()
        {
        }
    }
}

// Incluimos la clase que vamos a probar
require_once 'model/seccion.php';

/**
 * --- SUITE DE PRUEBAS PARA LA CLASE Seccion ---
 */
class SeccionTest extends TestCase
{
    /** @var Seccion|\PHPUnit\Framework\MockObject\MockObject */
    private $seccion;

    /** @var PDO|\PHPUnit\Framework\MockObject\MockObject */
    private $pdoMock;

    /** @var PDOStatement|\PHPUnit\Framework\MockObject\MockObject */
    private $stmtMock;

    /**
     * Configura el entorno antes de CADA prueba.
     */
    protected function setUp(): void
    {
        // 1. Crear mocks para PDO y PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // 2. Crear un "Mock Parcial" de la clase Seccion.
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor() 
            ->onlyMethods(['Con']) // Mockear solo la conexión
            ->getMock();

        // 3. Configurar el método Con() mockeado para que SIEMPRE devuelva nuestro mock de PDO.
        $this->seccion->method('Con')->willReturn($this->pdoMock);

        // 4. Configuración común: simular que prepare() y query() devuelven un statement
        $this->pdoMock->method('setAttribute');
        
        // CORRECCIÓN: Usar any() para evitar fallos por número de llamadas.
        // Las pruebas específicas pueden sobreescribir esto.
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->pdoMock->method('query')->willReturn($this->stmtMock);
        
        // Limpiar la variable de sesión que usa el módulo
        unset($_SESSION['promocion_f2_ejecutada_session']);
    }

    /**
     * Limpia el entorno después de CADA prueba.
     */
    protected function tearDown(): void
    {
        unset($_SESSION['promocion_f2_ejecutada_session']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: obtenerTodosLosHorarios()
    //--------------------------------------------------------------------------

    public function testObtenerTodosLosHorarios_Exito_CorrigeFormatoHora()
    {
        $datosSimulados = [
            ['hora_inicio' => '08:00', 'hora_fin' => '09:00'],
            ['hora_inicio' => '10:00:00', 'hora_fin' => '11:00:00']
        ];
        $datosEsperados = [
            ['hora_inicio' => '08:00:00', 'hora_fin' => '09:00:00'],
            ['hora_inicio' => '10:00:00', 'hora_fin' => '11:00:00']
        ];
        
        $this->stmtMock->method('fetchAll')->willReturn($datosSimulados);

        $resultado = $this->seccion->obtenerTodosLosHorarios();
        $this->assertEquals($datosEsperados, $resultado);
    }
    
    public function testObtenerTodosLosHorarios_Falla_DBException()
    {
        $this->pdoMock->method('query')->willThrowException(new PDOException('Error de BD'));
        $resultado = $this->seccion->obtenerTodosLosHorarios();
        $this->assertEquals([], $resultado);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: determinarFaseActual() (Privado, pero testeable)
    //--------------------------------------------------------------------------

    public function testDeterminarFaseActual_EncuentraFase1()
    {
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFases = $this->createMock(PDOStatement::class);

        // CORRECCIÓN: Usar un mapa flexible en lugar de 'withConsecutive'
        $prepareMap = [
            [$this->stringContains('SELECT ani_anio, ani_tipo FROM tbl_anio'), $stmtAnio],
            [$this->stringContains('SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase'), $stmtFases]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtAnio->method('fetch')->willReturn(['ani_anio' => '2025', 'ani_tipo' => '1']);
        
        $hoy = new DateTime();
        $ayer = (clone $hoy)->modify('-1 day')->format('Y-m-d H:i:s');
        $manana = (clone $hoy)->modify('+1 day')->format('Y-m-d H:i:s');

        $fasesSimuladas = [
            ['fase_numero' => 1, 'fase_apertura' => $ayer, 'fase_cierre' => $manana]
        ];
        $stmtFases->method('fetchAll')->willReturn($fasesSimuladas);

        $reflection = new ReflectionClass(Seccion::class);
        $method = $reflection->getMethod('determinarFaseActual');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->seccion);
        // Esta prueba AHORA DEBERÍA PASAR si aplicaste el setTime(23, 59, 59) en seccion.php
        $this->assertEquals('fase1', $resultado);
    }

    public function testDeterminarFaseActual_NoEncuentraFaseActiva()
    {
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFases = $this->createMock(PDOStatement::class);

        // CORRECCIÓN: Usar un mapa flexible
        $prepareMap = [
            [$this->stringContains('SELECT ani_anio, ani_tipo FROM tbl_anio'), $stmtAnio],
            [$this->stringContains('SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase'), $stmtFases]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        // CORRECCIÓN: Asegurarse de que el año activo se devuelva
        $stmtAnio->method('fetch')->willReturn(['ani_anio' => '2025', 'ani_tipo' => '1']);
        
        $fasesSimuladas = [
            ['fase_numero' => 1, 'fase_apertura' => '2025-01-01 00:00:00', 'fase_cierre' => '2025-01-31 23:59:59']
        ];
        $stmtFases->method('fetchAll')->willReturn($fasesSimuladas); // Ninguna fecha coincide con hoy

        $reflection = new ReflectionClass(Seccion::class);
        $method = $reflection->getMethod('determinarFaseActual');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->seccion);
        $this->assertEquals('ninguna', $resultado);
    }
    
    public function testDeterminarFaseActual_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error de BD'));
        
        $reflection = new ReflectionClass(Seccion::class);
        $method = $reflection->getMethod('determinarFaseActual');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($this->seccion);
        $this->assertEquals('ninguna', $resultado);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: RegistrarSeccion()
    //--------------------------------------------------------------------------

    public function testRegistrarSeccion_Falla_FormatoCodigoInvalido()
    {
        $resultado = $this->seccion->RegistrarSeccion("12345", 30, "2025", "1");
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Formato de código inválido', $resultado['mensaje']);
    }
    
    public function testRegistrarSeccion_Falla_CantidadInvalida()
    {
        $resultado = $this->seccion->RegistrarSeccion("IN1101", 100, "2025", "1");
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('entero entre 0 y 99', $resultado['mensaje']);
    }

    public function testRegistrarSeccion_Falla_SeccionYaExisteActiva()
    {
        $this->stmtMock->method('fetch')->willReturn(['sec_estado' => 1]);
        $this->pdoMock->expects($this->once())->method('rollBack');

        $resultado = $this->seccion->RegistrarSeccion("IN1101", 30, "2025", "1");
        
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }
    
    public function testRegistrarSeccion_Exito_Nueva()
    {
        $this->stmtMock->method('fetch')->willReturn(false); // No existe
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');
        
        $this->pdoMock->method('prepare')
             ->with($this->logicalOr(
                $this->stringContains('SELECT sec_estado FROM tbl_seccion'),
                $this->stringContains('INSERT INTO tbl_seccion')
             ))
             ->willReturn($this->stmtMock);

        $resultado = $this->seccion->RegistrarSeccion("IN1101", 30, "2025", "1");
        
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
        $this->assertEquals('IN1101', $resultado['nuevo_codigo']);
    }
    
    public function testRegistrarSeccion_Exito_Reactivando()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'EliminarDependenciasDeSeccion'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('EliminarDependenciasDeSeccion')->willReturn(true);

        $this->stmtMock->method('fetch')->willReturn(['sec_estado' => 0]);
        
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');

        $this->pdoMock->method('prepare')
             ->with($this->logicalOr(
                $this->stringContains('SELECT sec_estado FROM tbl_seccion'),
                $this->stringContains('UPDATE tbl_seccion SET sec_cantidad')
             ))
             ->willReturn($this->stmtMock);

        $resultado = $this->seccion->RegistrarSeccion("IN1101", 30, "2025", "1");
        
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
    }
    
    public function testRegistrarSeccion_Falla_DBException()
    {
        $this->stmtMock->method('fetch')->willReturn(false); // No existe
        
        // Simular que el 'execute' del INSERT falla
        $this->stmtMock->method('execute')->willThrowException(new PDOException('Error de BD'));
        
        // El test espera que se llame a rollBack.
        // Esta prueba AHORA DEBERÍA PASAR si aplicaste el rollBack() en seccion.php
        $this->pdoMock->expects($this->once())->method('rollBack');

        $resultado = $this->seccion->RegistrarSeccion("IN1101", 30, "2025", "1");
        $this->assertEquals('error', $resultado['resultado']);
    }
    
    //--------------------------------------------------------------------------
    // Pruebas para: ValidarClaseEnVivo()
    //--------------------------------------------------------------------------

    public function testValidarClaseEnVivo_Exito_SinConflictos()
    {
        $this->stmtMock->method('fetchColumn')->willReturn(false); 
        $this->stmtMock->method('fetchAll')->willReturn([]); 
        $this->stmtMock->method('fetch')->willReturn(false); 

        // CORRECCIÓN: Pasar un array de espacio completo
        $espacioCompleto = ['numero' => '101', 'tipo' => 'Aula', 'edificio' => 'A'];
        $resultado = $this->seccion->ValidarClaseEnVivo('V123', 'UC1', $espacioCompleto, 'Lunes', '08:00', '09:00', 'SEC-01');
        
        // Esta prueba AHORA DEBERÍA PASAR si aplicaste el (?? null) en seccion.php
        $this->assertEquals(false, $resultado['conflicto']);
    }

    public function testValidarClaseEnVivo_Falla_ConflictoDocente()
    {
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtDocente = $this->createMock(PDOStatement::class);
        
        // CORRECCIÓN: Usar mapa flexible
        $prepareMap = [
            [$this->stringContains('SELECT grupo_union_id'), $stmtGrupo],
            [$this->stringContains('SELECT uh.sec_codigo, d.doc_nombre'), $stmtDocente]
            // No esperamos consulta de espacio
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtGrupo->method('fetchColumn')->willReturn(false); // No hay grupo
        $conflictoSimulado = [['sec_codigo' => 'SEC-OTRA', 'doc_nombre' => 'John', 'doc_apellido' => 'Doe']];
        $stmtDocente->method('fetchAll')->willReturn($conflictoSimulado);

        $resultado = $this->seccion->ValidarClaseEnVivo('V123', 'UC1', null, 'Lunes', '08:00', '09:00', 'SEC-01');
        
        $this->assertEquals(true, $resultado['conflicto']);
        $this->assertEquals('docente', $resultado['mensajes'][0]['tipo']);
    }
    
    public function testValidarClaseEnVivo_Exito_IgnoraConflictoEnGrupoUnido()
    {
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtHermanas = $this->createMock(PDOStatement::class);
        $stmtDocente = $this->createMock(PDOStatement::class);
        $stmtEspacio = $this->createMock(PDOStatement::class);
        
        // CORRECCIÓN: Mapa flexible para las 4 posibles consultas
        $prepareMap = [
            [$this->stringContains('SELECT grupo_union_id'), $stmtGrupo],
            [$this->stringContains('SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id'), $stmtHermanas],
            [$this->stringContains('WHERE s.sec_estado = 1 AND uh.doc_cedula = ?'), $stmtDocente],
            [$this->stringContains('WHERE s.sec_estado = 1 AND uh.esp_numero = ?'), $stmtEspacio]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtGrupo->method('fetchColumn')->willReturn('grupo-123'); // SÍ hay grupo
        // CORRECCIÓN: fetchAll(PDO::FETCH_COLUMN) devuelve un array simple
        $stmtHermanas->method('fetchAll')->with(PDO::FETCH_COLUMN)->willReturn(['SEC-01', 'SEC-02']); // Secciones hermanas
        $stmtDocente->method('fetchAll')->willReturn([]); // No hay conflictos *fuera* del grupo
        $stmtEspacio->method('fetch')->willReturn(false); // No hay conflictos de espacio

        $resultado = $this->seccion->ValidarClaseEnVivo('V123', 'UC1', ['numero' => '101', 'tipo' => 'Aula', 'edificio' => 'A'], 'Lunes', '08:00', '09:00', 'SEC-01');
        
        $this->assertEquals(false, $resultado['conflicto']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: Modificar()
    //--------------------------------------------------------------------------

    public function testModificar_Falla_ConflictoUCDuplicada_SinForzar()
    {
        $horarioJson = json_encode([
            ['uc_codigo' => 'UC-01', 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00'],
            ['uc_codigo' => 'UC-01', 'dia' => 'Martes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00']
        ]);
        
        $this->stmtMock->method('fetchColumn')->willReturn('Matemática'); // Nombre de la UC

        $resultado = $this->seccion->Modificar('SEC-01', '2025', $horarioJson, 30, false);
        
        $this->assertEquals('confirmar_conflicto', $resultado['resultado']);
        $this->assertStringContainsString('Matemática', $resultado['mensaje']);
    }
    
    public function testModificar_Falla_ExcesoDeHorasDocente_SinForzar()
    {
        $horarioJson = json_encode([
            ['uc_codigo' => 'UC-01', 'doc_cedula' => 'V123', 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00']
        ]);

        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ValidarClaseEnVivo'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('ValidarClaseEnVivo')->willReturn(['conflicto' => false]);
        
        // Simular la secuencia de BD
        $stmtMalla = $this->createMock(PDOStatement::class);
        $stmtHorasUC = $this->createMock(PDOStatement::class);
        $stmtMaxHoras = $this->createMock(PDOStatement::class);
        $stmtHorasActuales = $this->createMock(PDOStatement::class); 
        $stmtDocInfo = $this->createMock(PDOStatement::class);

        // CORRECCIÓN: Añadir el mock para query()
        $this->pdoMock->method('query')->willReturn($stmtMalla);
        $stmtMalla->method('fetchColumn')->willReturn('MALLA-01'); // Malla activa
        
        // CORRECCIÓN: Usar mapa flexible
        $prepareMap = [
            [$this->stringContains('SELECT mal_hora_academica'), $stmtHorasUC],
            [$this->stringContains('SELECT act_academicas'), $stmtMaxHoras],
            [$this->stringContains('SELECT SUM(um.mal_hora_academica)'), $stmtHorasActuales],
            [$this->stringContains('SELECT doc_nombre, doc_apellido'), $stmtDocInfo]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtHorasUC->method('fetchColumn')->willReturn(10); // UC vale 10 horas
        $stmtMaxHoras->method('fetchColumn')->willReturn(20); // Docente max 20 horas
        $stmtHorasActuales->method('fetchColumn')->willReturn(15); // Docente ya tiene 15 horas (15 + 10 > 20)
        $stmtDocInfo->method('fetch')->willReturn(['doc_nombre' => 'John', 'doc_apellido' => 'Doe']);

        $resultado = $this->seccion->Modificar('SEC-01', '2025', $horarioJson, 30, false);
        
        $this->assertEquals('confirmar_conflicto', $resultado['resultado']);
        $this->assertStringContainsString('excedería sus horas académicas', $resultado['mensaje']);
    }

    public function testModificar_Exito_ConConflicto_ConForzar()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'EliminarDependenciasDeSeccion'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('EliminarDependenciasDeSeccion')->willReturn(true);

        $horarioJson = json_encode([
            ['uc_codigo' => 'UC-01', 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00'],
            ['uc_codigo' => 'UC-01', 'dia' => 'Martes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00']
        ]);
        
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $resultado = $this->seccion->Modificar('SEC-01', '2025', $horarioJson, 30, true);
        
        $this->assertEquals('modificar_ok', $resultado['resultado']);
    }

    public function testModificar_Exito_SinConflictos()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ValidarClaseEnVivo', 'EliminarDependenciasDeSeccion'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('ValidarClaseEnVivo')->willReturn(['conflicto' => false]);
        $this->seccion->method('EliminarDependenciasDeSeccion')->willReturn(true);

        $horarioJson = json_encode([
            ['uc_codigo' => 'UC-01', 'doc_cedula' => 'V123', 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:00']
        ]);
        
        $this->pdoMock->method('query')->willReturn($this->stmtMock); 
        $this->stmtMock->method('fetchColumn')
             ->willReturnOnConsecutiveCalls(
                'MALLA-01', // Malla activa
                10,         // Horas UC
                30,         // Max Horas Docente
                15          // Horas Actuales Docente (15 + 10 <= 30)
             );

        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');

        $resultado = $this->seccion->Modificar('SEC-01', '2025', $horarioJson, 30, false);
        
        $this->assertEquals('modificar_ok', $resultado['resultado']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: duplicarSeccionesAnioAnterior()
    //--------------------------------------------------------------------------
    
    public function testDuplicarSeccionesAnioAnterior_Falla_SinAnioActivo()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerAnioActivo'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('obtenerAnioActivo')->willReturn(null);

        $resultado = $this->seccion->duplicarSeccionesAnioAnterior();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('No se encontró un año académico activo', $resultado['mensaje']);
    }
    
    public function testDuplicarSeccionesAnioAnterior_Info_SinSeccionesAnteriores()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerAnioActivo'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('obtenerAnioActivo')->willReturn('2025');

        $this->stmtMock->method('fetchAll')->willReturn([]); // No hay secciones en 2024
        $this->pdoMock->expects($this->once())->method('rollBack');

        $resultado = $this->seccion->duplicarSeccionesAnioAnterior();
        $this->assertEquals('info_ok', $resultado['resultado']);
    }
    
    public function testDuplicarSeccionesAnioAnterior_Exito()
    {
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerAnioActivo'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('obtenerAnioActivo')->willReturn('2025');

        $seccionesViejas = [['sec_codigo' => 'SEC-01', 'ani_tipo' => '1']];
        $horariosViejos = [['sec_codigo' => 'SEC-01', 'uc_codigo' => 'UC-01', 'hor_dia' => 'Lunes', 'hor_horainicio' => '08:00:00', 'hor_horafin' => '09:00:00', 'subgrupo' => null]];

        $stmtViejas = $this->createMock(PDOStatement::class);
        // CORRECCIÓN: Devolver los datos simulados
        $stmtViejas->method('fetchAll')->willReturn($seccionesViejas);

        $stmtHorarios = $this->createMock(PDOStatement::class);
        // CORRECCIÓN: Devolver los datos simulados
        $stmtHorarios->method('fetchAll')->willReturn($horariosViejos);

        $this->pdoMock->method('prepare')
             ->willReturnCallback(function($sql) use ($stmtViejas, $stmtHorarios) {
                if (strpos($sql, 'SELECT * FROM tbl_seccion WHERE ani_anio = ?') !== false) {
                    return $stmtViejas;
                }
                if (strpos($sql, 'SELECT DISTINCT uh.sec_codigo, uh.uc_codigo') !== false) {
                    return $stmtHorarios;
                }
                return $this->stmtMock; // Para DELETEs e INSERTs
             });

        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        
        $resultado = $this->seccion->duplicarSeccionesAnioAnterior();
        
        $this->assertEquals('duplicar_anio_anterior_ok', $resultado['resultado']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: obtenerDatosCompletosHorarioParaReporte()
    //--------------------------------------------------------------------------
    
    public function testObtenerDatosCompletosHorarioParaReporte_Exito_SeccionUnica()
    {
        // CORRECCIÓN: Mockear obtenerTurnos() también
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerTurnos'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('obtenerTurnos')->willReturn([['tur_id' => 1, 'tur_horainicio' => '08:00:00', 'tur_horafin' => '08:40:00']]); // Simular turnos


        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtHorario = $this->createMock(PDOStatement::class);
        
        $prepareMap = [
            [$this->stringContains('SELECT grupo_union_id'), $stmtGrupo],
            [$this->stringContains('FROM uc_horario uh'), $stmtHorario]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtGrupo->method('fetchColumn')->willReturn(null); // No hay grupo
        
        // CORRECCIÓN: Devolver datos simulados
        $datosHorarioSimulados = [
            ['sec_codigo' => 'SEC-01', 'hor_horainicio' => '08:00', 'hor_horafin' => '09:00']
        ];
        $stmtHorario->method('fetchAll')->willReturn($datosHorarioSimulados);

        $resultado = $this->seccion->obtenerDatosCompletosHorarioParaReporte('SEC-01', '2025');

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals(['SEC-01'], $resultado['secciones']);
        $this->assertEquals('08:00:00', $resultado['horario'][0]['hor_horainicio']); 
    }
    
    public function testObtenerDatosCompletosHorarioParaReporte_Exito_SeccionAgrupada()
    {
        // CORRECCIÓN: Mockear obtenerTurnos() también
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'obtenerTurnos'])
            ->getMock();
        $this->seccion->method('Con')->willReturn($this->pdoMock);
        $this->seccion->method('obtenerTurnos')->willReturn([['tur_id' => 1, 'tur_horainicio' => '08:00:00', 'tur_horafin' => '08:40:00']]);


        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtSeccionesGrupo = $this->createMock(PDOStatement::class);
        $stmtHorario = $this->createMock(PDOStatement::class);
        
        $prepareMap = [
            [$this->stringContains('SELECT grupo_union_id'), $stmtGrupo],
            [$this->stringContains('SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id'), $stmtSeccionesGrupo],
            [$this->stringContains('FROM uc_horario uh'), $stmtHorario]
        ];
        $this->pdoMock->method('prepare')->will($this->returnValueMap($prepareMap));

        $stmtGrupo->method('fetchColumn')->willReturn('grupo-123');
        // CORRECCIÓN: fetchAll(PDO::FETCH_COLUMN) devuelve un array simple
        $stmtSeccionesGrupo->method('fetchAll')->with(PDO::FETCH_COLUMN)->willReturn(['SEC-01', 'SEC-02']);
        
        // CORRECCIÓN: Devolver datos simulados
        $datosHorarioSimulados = [
             ['sec_codigo' => 'SEC-01', 'hor_horainicio' => '08:00', 'hor_horafin' => '09:00'],
             ['sec_codigo' => 'SEC-02', 'hor_horainicio' => '08:00', 'hor_horafin' => '09:00']
        ];
        $stmtHorario->method('fetchAll')->willReturn($datosHorarioSimulados);

        $resultado = $this->seccion->obtenerDatosCompletosHorarioParaReporte('SEC-01', '2025');

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals(['SEC-01', 'SEC-02'], $resultado['secciones']);
    }
}